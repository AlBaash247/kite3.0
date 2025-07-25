<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Contributor;
use App\Models\User;

class ApiProjectsController extends Controller
{
    // List projects with pagination
    public function index(Request $request)
    {
        $userId = $request->input('author_id');
        if (!$userId) {
            return response()->json(['message' => 'author_id is required'], 400);
        }
        
        // Only show projects where user is author or contributor
        $contributedProjectIds = Contributor::where('contributor_id', $userId)->pluck('project_id')->toArray();
        $projects = Project::where(function($query) use ($userId, $contributedProjectIds) {
            $query->where('author_id', $userId)
                  ->orWhereIn('id', $contributedProjectIds);
        })->with(['author', 'contributors.user'])->paginate($request->input('limit', 10));
        
        return response()->json($projects);
    }

    // View single project
    public function show($id)
    {
        $userId = request('author_id');
        if (!$userId) {
            return response()->json(['message' => 'author_id is required'], 400);
        }
        
        $project = Project::with(['author', 'contributors.user', 'tasks'])->find($id);
        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }
        
        $isContributor = Contributor::where('project_id', $project->id)
            ->where('contributor_id', $userId)
            ->exists();
            
        if ($project->author_id != $userId && !$isContributor) {
            return response()->json(['message' => 'Only the project author or a contributor can view this project.'], 403);
        }
        
        return response()->json($project);
    }

    // Create project
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        
        $validated['author_id'] = $request->input('author_id');
        $project = Project::create($validated);
        
        return response()->json($project, 201);
    }

    // Edit project
    public function update(Request $request, $id)
    {
        $userId = $request->input('author_id');
        if (!$userId) {
            return response()->json(['message' => 'author_id is required'], 400);
        }
        
        $project = Project::find($id);
        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }
        
        if ($project->author_id != $userId) {
            return response()->json(['message' => 'Only the project author can update this project.'], 403);
        }
        
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
        ]);
        
        $project->update($validated);
        
        return response()->json($project);
    }

    // Delete project
    public function destroy($id)
    {
        $userId = request('author_id');
        if (!$userId) {
            return response()->json(['message' => 'author_id is required'], 400);
        }
        
        $project = Project::find($id);
        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }
        
        if ($project->author_id != $userId) {
            return response()->json(['message' => 'Only the project author can delete this project.'], 403);
        }
        
        $project->delete();
        
        return response()->json(['message' => 'Project deleted successfully']);
    }

    // Add contributor by email
    public function addContributor(Request $request, $project_id)
    {
        $userId = $request->input('author_id');
        if (!$userId) {
            return response()->json(['message' => 'author_id is required'], 400);
        }
        
        $project = Project::find($project_id);
        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }
        
        if ($project->author_id != $userId) {
            return response()->json(['message' => 'Only the project author can add contributors.'], 403);
        }
        
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
            'is_editor' => 'boolean',
        ]);
        
        $contributorUser = User::where('email', $validated['email'])->first();
        if (!$contributorUser) {
            return response()->json(['message' => 'User not found'], 404);
        }
        
        // Check if trying to add self as contributor
        if ($contributorUser->id === $userId) {
            return response()->json(['message' => 'You cannot add yourself as a contributor'], 400);
        }
        
        // Check if already a contributor
        $existingContributor = Contributor::where('project_id', $project_id)
            ->where('contributor_id', $contributorUser->id)
            ->first();
            
        if ($existingContributor) {
            return response()->json(['message' => 'User is already a contributor'], 400);
        }
        
        $contributor = Contributor::create([
            'project_id' => $project_id,
            'contributor_id' => $contributorUser->id,
            'is_editor' => $validated['is_editor'] ?? false,
        ]);
        
        return response()->json($contributor, 201);
    }

    // List contributors
    public function listContributors($project_id)
    {
        $userId = request('author_id');
        if (!$userId) {
            return response()->json(['message' => 'author_id is required'], 400);
        }
        
        $project = Project::find($project_id);
        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }
        
        $isContributor = Contributor::where('project_id', $project_id)
            ->where('contributor_id', $userId)
            ->exists();
            
        if ($project->author_id != $userId && !$isContributor) {
            return response()->json(['message' => 'Only the project author or a contributor can view the contributors.'], 403);
        }
        
        $contributors = Contributor::where('project_id', $project_id)
            ->with('user')
            ->get();
            
        return response()->json($contributors);
    }

    // Update contributor permission
    public function updateContributor(Request $request, $project_id, $contributor_id)
    {
        $userId = $request->input('author_id');
        if (!$userId) {
            return response()->json(['message' => 'author_id is required'], 400);
        }
        
        $project = Project::find($project_id);
        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }
        
        if ($project->author_id != $userId) {
            return response()->json(['message' => 'Only the project author can update contributor permissions.'], 403);
        }
        
        $validated = $request->validate([
            'is_editor' => 'required|boolean',
        ]);
        
        $contributor = Contributor::where('project_id', $project_id)
            ->where('contributor_id', $contributor_id)
            ->first();
            
        if (!$contributor) {
            return response()->json(['message' => 'Contributor not found'], 404);
        }
        
        $contributor->update($validated);
        
        return response()->json($contributor);
    }

    // Remove contributor
    public function removeContributor($project_id, $contributor_id)
    {
        $userId = request('author_id');
        if (!$userId) {
            return response()->json(['message' => 'author_id is required'], 400);
        }
        
        $project = Project::find($project_id);
        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }
        
        if ($project->author_id != $userId) {
            return response()->json(['message' => 'Only the project author can remove contributors.'], 403);
        }
        
        $contributor = Contributor::where('project_id', $project_id)
            ->where('contributor_id', $contributor_id)
            ->first();
            
        if (!$contributor) {
            return response()->json(['message' => 'Contributor not found'], 404);
        }
        
        $contributor->delete();
        
        return response()->json(['message' => 'Contributor removed successfully']);
    }
} 