<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Contributor;
use App\Models\Task;
use App\Models\User;


class ApiProjectsController extends Controller
{
    public function ping()
    {
        return response()->json(['is_ok' => true, 'message' => 'pong']);
    }

    // List projects with pagination
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        // Only show projects where user is author or contributor
        $contributedProjectIds = Contributor::where('contributor_id', $userId)->pluck('project_id')->toArray();
        $projects = Project::where(function ($query) use ($userId, $contributedProjectIds) {
            $query->where('author_id', $userId)
                ->orWhereIn('id', $contributedProjectIds);
        })->with(['author', 'contributors.user'])->paginate($request->input('limit', 10));

        return response()->json([
            'is_ok' => true,
            'message' => 'Projects fetched successfully',
            'payload' => $projects
        ]);
    }

    // View single project
    public function show($id)
    {
        $userId = request()->user()->id;

        $project = Project::with(['author', 'contributors.user', 'tasks.author'])->find($id);
        if (!$project) {
            return response()->json(['is_ok' => false, 'message' => 'Project not found'], 404);
        }

        $isContributor = Contributor::where('project_id', $project->id)
            ->where('contributor_id', $userId)
            ->exists();

        if ($project->author_id != $userId && !$isContributor) {
            return response()->json(['is_ok' => false, 'message' => 'Only the project author or a contributor can view this project.'], 403);
        }

        return response()->json([
            'is_ok' => true,
            'message' => 'Project fetched successfully',
            'payload' => $project
        ]);
    }

    // Create project
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $validated['author_id'] = $request->user()->id;
        $project = Project::create($validated);

        return response()->json([
            'is_ok' => true,
            'message' => 'Project created successfully',
            'payload' => $project
        ], 201);
    }

    // Edit project
    public function update(Request $request)
    {

        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
        ]);


        $userId = $request->user()->id;

        $project = Project::find($request->project_id);
        if (!$project) {
            return response()->json(['is_ok' => false, 'message' => 'Project not found'], 404);
        }

        if ($project->author_id != $userId) {
            return response()->json(['is_ok' => false, 'message' => 'Only the project author can update this project.'], 403);
        }



        $project->update($validated);

        return response()->json([
            'is_ok' => true,
            'message' => 'Project updated successfully',
            'payload' => $project
        ]);
    }

    // Delete project
    public function destroy($id)
    {
        $userId = request()->user()->id;

        $project = Project::find($id);
        if (!$project) {
            return response()->json(['is_ok' => false, 'message' => 'Project not found'], 404);
        }

        if ($project->author_id != $userId) {
            return response()->json(['is_ok' => false, 'message' => 'Only the project author can delete this project.'], 403);
        }


        // delete all comments tasks and contributors related to the project
        Comment::whereIn('task_id', Task::where('project_id', $id)->pluck('id'))->delete();
        Task::where('project_id', $id)->delete();
        Contributor::where('project_id', $id)->delete();

        $project->delete();

        return response()->json(['is_ok' => true, 'message' => 'Project deleted successfully']);
    }

    // Add contributor by email
    public function addContributor(Request $request)
    {
        // dd($request->all());
        $validated = $request->validate([
            'project_id' => 'required|exists:contributors,project_id',
            'email' => 'required|email|exists:users,email',
            'is_editor' => 'nullable|in:true,false,0,1'
        ]);

        // dd($validated);

        $userId = $request->user()->id;

        $project = Project::find($validated['project_id']);
        if (!$project) {
            return response()->json(['is_ok' => false, 'message' => 'Project not found'], 404);
        }

        if ($project->author_id != $userId) {
            return response()->json(['is_ok' => false, 'message' => 'Only the project author can add contributors.'], 403);
        }


        $contributorUser = User::where('email', $validated['email'])->first();
        if (!$contributorUser) {
            return response()->json(['is_ok' => false, 'message' => 'User not found'], 404);
        }

        // Check if trying to add self as contributor
        if ($contributorUser->id === $userId) {
            return response()->json(['is_ok' => false, 'message' => 'You cannot add yourself as a contributor'], 400);
        }

        // Check if already a contributor
        $existingContributor = Contributor::where('project_id', $validated['project_id'])
            ->where('contributor_id', $contributorUser->id)
            ->first();

        if ($existingContributor) {
            return response()->json(['is_ok' => false, 'message' => 'User is already a contributor'], 400);
        }

        // Convert string boolean to actual boolean
        $isEditor = false;
        if (isset($validated['is_editor'])) {
            $isEditor = filter_var($validated['is_editor'], FILTER_VALIDATE_BOOLEAN);
        }

        $contributor = Contributor::create([
            'project_id' => $validated['project_id'],
            'contributor_id' => $contributorUser->id,
            'is_editor' => $isEditor,
        ]);

        return response()->json([
            'is_ok' => true,
            'message' => 'Contributor added successfully',
            'payload' => $contributor
        ], 201);
    }

    // List contributors
    public function listContributors($project_id)
    {
        $userId = request()->user()->id;

        $project = Project::find($project_id);
        if (!$project) {
            return response()->json(['is_ok' => false, 'message' => 'Project not found'], 404);
        }

        $isContributor = Contributor::where('project_id', $project_id)
            ->where('contributor_id', $userId)
            ->exists();

        if ($project->author_id != $userId && !$isContributor) {
            return response()->json(['is_ok' => false, 'message' => 'Only the project author or a contributor can view the contributors.'], 403);
        }

        $contributors = Contributor::where('project_id', $project_id)
            ->with('user')
            ->get();

        return response()->json([
            'is_ok' => true,
            'message' => 'Contributors fetched successfully',
            'payload' => $contributors
        ]);
    }

    // Update contributor permission
    public function updateContributor(Request $request)
    {

        // if contributor_id == user id exit with error
        if ($request->contributor_id == $request->user()->id) {
            return response()->json(['is_ok' => false, 'message' => 'Error: You provided your own id as the contributor id!'], 403);
        }

        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'contributor_id' => 'required|exists:contributors,contributor_id',
            'is_editor' => 'required|in:true,false,0,1',
        ]);

        if (!$validated) {
            return response()->json(['is_ok' => false, 'message' => 'Error: validation failed!'], 403);
        }

        // Convert string boolean to actual boolean
        $validated['is_editor'] = filter_var($validated['is_editor'], FILTER_VALIDATE_BOOLEAN);

        $userId = $request->user()->id;

        $project = Project::find($validated['project_id']);
        if (!$project) {
            return response()->json(['is_ok' => false, 'message' => 'Project not found'], 404);
        }

        if ($project->author_id != $userId) {
            return response()->json(['is_ok' => false, 'message' => 'Only the project author can update contributor permissions.'], 403);
        }

        $contributor = Contributor::where('project_id', $validated['project_id'])
            ->where('contributor_id', $validated['contributor_id'])
            ->first();

        if (!$contributor) {
            return response()->json(['is_ok' => false, 'message' => 'Contributor not found'], 404);
        }

        $contributor->update([
            'is_editor' => $validated['is_editor']
        ]);

        return response()->json([
            'is_ok' => true,
            'message' => 'Contributor updated successfully',
            'payload' => $contributor
        ]);
    }

    // Remove contributor
    public function removeContributor(Request $request)
    {

        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'contributor_id' => 'required|exists:contributors,contributor_id',
        ]);

        if (!$validated) {
            return response()->json(['is_ok' => false, 'message' => 'Error: validation failed!'], 403);
        }

        $userId = request()->user()->id;

        $project = Project::find($validated['project_id']);
        if (!$project) {
            return response()->json(['is_ok' => false, 'message' => 'Project not found'], 404);
        }

        if ($project->author_id != $userId) {
            return response()->json(['is_ok' => false, 'message' => 'Only the project author can remove contributors.'], 403);
        }

        $contributor = Contributor::where('project_id', $validated['project_id'])
            ->where('contributor_id', $validated['contributor_id'])
            ->first();

        if (!$contributor) {
            return response()->json(['is_ok' => false, 'message' => 'Contributor not found'], 404);
        }

        $contributor->delete();

        return response()->json(['is_ok' => true, 'message' => 'Contributor removed successfully']);
    }

    // Remove contributor
    public function removeContributorById($id)
    {

        $userId = request()->user()->id;
        $contributor = Contributor::find($id);

        if (!$contributor) {
            return response()->json(['is_ok' => false, 'message' => 'Contributor not found'], 404);
        }


        $project = Project::find($contributor->project_id);
        if (!$project) {
            return response()->json(['is_ok' => false, 'message' => 'Project not found'], 404);
        }

        // check if user is the author of the project
        if ($project->author_id != $userId) {
            return response()->json(['is_ok' => false, 'message' => 'Only the project author can remove contributors.'], 403);
        }



        $contributor->delete();

        return response()->json(['is_ok' => true, 'message' => 'Contributor removed successfully']);
    }
}
