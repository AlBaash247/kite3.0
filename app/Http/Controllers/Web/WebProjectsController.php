<?php

namespace App\Http\Controllers\Web;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Contributor;
use App\Models\User;
use Inertia\Inertia;

class WebProjectsController extends Controller
{
    // List projects with pagination
    public function index(Request $request)
    {
        $userId = Auth::id();
        if (!$userId) {
            return redirect()->route('login');
        }
        
        // Only show projects where user is author or contributor
        $contributedProjectIds = Contributor::where('contributor_id', $userId)->pluck('project_id')->toArray();
        $projects = Project::where(function($query) use ($userId, $contributedProjectIds) {
            $query->where('author_id', $userId)
                  ->orWhereIn('id', $contributedProjectIds);
        })->with(['author', 'contributors.user'])->paginate($request->input('limit', 10));
        
        return Inertia::render('Projects/Index', [
            'projects' => $projects,
            'userId' => $userId
        ]);
    }

    // Show create form
    public function create()
    {
        return Inertia::render('Projects/Create');
    }

    // View single project
    public function show($id)
    {
        $userId = Auth::id();
        if (!$userId) {
            return redirect()->route('login');
        }
        
        $project = Project::with(['author', 'contributors.user', 'tasks'])->find($id);
        if (!$project) {
            abort(404, 'Project not found');
        }
        
        $isContributor = Contributor::where('project_id', $project->id)
            ->where('contributor_id', $userId)
            ->exists();
            
        if ($project->author_id != $userId && !$isContributor) {
            abort(403, 'Only the project author or a contributor can view this project.');
        }
        
        return Inertia::render('Projects/Show', [
            'project' => $project,
            'userId' => $userId,
            'isAuthor' => $project->author_id == $userId,
            'isContributor' => $isContributor
        ]);
    }

    // Show edit form
    public function edit($id)
    {
        $userId = Auth::id();
        if (!$userId) {
            return redirect()->route('login');
        }
        
        $project = Project::find($id);
        if (!$project) {
            abort(404, 'Project not found');
        }
        
        if ($project->author_id != $userId) {
            abort(403, 'Only the project author can edit this project.');
        }
        
        return Inertia::render('Projects/Edit', [
            'project' => $project
        ]);
    }

    // Create project
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        
        $validated['author_id'] = Auth::id();
        $project = Project::create($validated);
        
        return redirect()->route('projects.show', $project->id)
            ->with('success', 'Project created successfully');
    }

    // Edit project
    public function update(Request $request, $id)
    {
        $userId = Auth::id();
        if (!$userId) {
            return redirect()->route('login');
        }
        
        $project = Project::find($id);
        if (!$project) {
            abort(404, 'Project not found');
        }
        
        if ($project->author_id != $userId) {
            abort(403, 'Only the project author can update this project.');
        }
        
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
        ]);
        
        $project->update($validated);
        
        return redirect()->route('projects.show', $project->id)
            ->with('success', 'Project updated successfully');
    }

    // Delete project
    public function destroy($id)
    {
        $userId = Auth::id();
        if (!$userId) {
            return redirect()->route('login');
        }
        
        $project = Project::find($id);
        if (!$project) {
            abort(404, 'Project not found');
        }
        
        if ($project->author_id != $userId) {
            abort(403, 'Only the project author can delete this project.');
        }
        
        $project->delete();
        
        return redirect()->route('projects.index')
            ->with('success', 'Project deleted successfully');
    }

    // Show contributors page
    public function contributors($project_id)
    {
        $userId = Auth::id();
        if (!$userId) {
            return redirect()->route('login');
        }
        
        $project = Project::with(['author', 'contributors.user'])->find($project_id);
        if (!$project) {
            abort(404, 'Project not found');
        }
        
        if ($project->author_id != $userId) {
            abort(403, 'Only the project author can manage contributors.');
        }
        
        return Inertia::render('Projects/Contributors', [
            'project' => $project,
            'contributors' => $project->contributors
        ]);
    }

    // Add contributor by email
    public function addContributor(Request $request, $project_id)
    {
        $userId = Auth::id();
        if (!$userId) {
            return redirect()->route('login');
        }
        
        $project = Project::find($project_id);
        if (!$project) {
            abort(404, 'Project not found');
        }
        
        if ($project->author_id != $userId) {
            abort(403, 'Only the project author can add contributors.');
        }
        
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
            'is_editor' => 'boolean',
        ]);
        
        $contributorUser = User::where('email', $validated['email'])->first();
        if (!$contributorUser) {
            return back()->withErrors(['email' => 'User not found']);
        }
        
        // Check if trying to add self as contributor
        if ($contributorUser->id === $userId) {
            return back()->withErrors(['email' => 'You cannot add yourself as a contributor']);
        }
        
        // Check if already a contributor
        $existingContributor = Contributor::where('project_id', $project_id)
            ->where('contributor_id', $contributorUser->id)
            ->first();
            
        if ($existingContributor) {
            return back()->withErrors(['email' => 'User is already a contributor']);
        }
        
        Contributor::create([
            'project_id' => $project_id,
            'contributor_id' => $contributorUser->id,
            'is_editor' => $validated['is_editor'] ?? false,
        ]);
        
        return redirect()->route('projects.contributors', $project_id)
            ->with('success', 'Contributor added successfully');
    }

    // List contributors
    public function listContributors($project_id)
    {
        $userId = Auth::id();
        if (!$userId) {
            return redirect()->route('login');
        }
        
        $project = Project::find($project_id);
        if (!$project) {
            abort(404, 'Project not found');
        }
        
        $isContributor = Contributor::where('project_id', $project_id)
            ->where('contributor_id', $userId)
            ->exists();
            
        if ($project->author_id != $userId && !$isContributor) {
            abort(403, 'Only the project author or a contributor can view contributors.');
        }
        
        $contributors = Contributor::where('project_id', $project_id)
            ->with('user')
            ->get();
            
        return Inertia::render('Projects/Contributors', [
            'project' => $project,
            'contributors' => $contributors
        ]);
    }

    // Update contributor permission
    public function updateContributor(Request $request, $project_id, $contributor_id)
    {
        $userId = Auth::id();
        if (!$userId) {
            return redirect()->route('login');
        }
        
        $project = Project::find($project_id);
        if (!$project) {
            abort(404, 'Project not found');
        }
        
        if ($project->author_id != $userId) {
            abort(403, 'Only the project author can update contributors.');
        }
        
        $validated = $request->validate([
            'is_editor' => 'required|boolean',
        ]);
        
        $contributor = Contributor::where('project_id', $project_id)
            ->where('contributor_id', $contributor_id)
            ->first();
            
        if (!$contributor) {
            abort(404, 'Contributor not found');
        }
        
        $contributor->update($validated);
        
        return redirect()->route('projects.contributors', $project_id)
            ->with('success', 'Contributor updated successfully');
    }

    // Remove contributor
    public function removeContributor($project_id, $contributor_id)
    {

        $userId = Auth::id();
        if (!$userId) {
            return redirect()->route('login');
        }
        
        $project = Project::find($project_id);
        if (!$project) {
            abort(404, 'Project not found');
        }
        
        if ($project->author_id != $userId) {
            abort(403, 'Only the project author can remove contributors.');
        }
        
        $contributor = Contributor::where('project_id', $project_id)
            ->where('contributor_id', $contributor_id)
            ->first();
            
        if (!$contributor) {
            abort(404, 'Contributor not found');
        }
        
        $contributor->delete();
        
        return redirect()->route('projects.contributors', $project_id)
            ->with('success', 'Contributor removed successfully');
    }
} 