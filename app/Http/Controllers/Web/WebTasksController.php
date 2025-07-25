<?php

namespace App\Http\Controllers\Web;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\Contributor;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Models\Task;
use Inertia\Inertia;

class WebTasksController extends Controller
{
    // List tasks with pagination
    public function index(Request $request)
    {
        $projectId = $request->input('project_id');
        $userId = Auth::id();
        
        if (!$projectId || !$userId) {
            return redirect()->route('projects.index');
        }
        
        $project = Project::find($projectId);
        if (!$project) {
            abort(404, 'Project not found');
        }
        
        // Only project author or any contributor can list tasks
        $isContributor = Contributor::where('project_id', $projectId)
            ->where('contributor_id', $userId)
            ->exists();
            
        if ($project->author_id != $userId && !$isContributor) {
            abort(403, 'Only the project author or a contributor can view tasks.');
        }
        
        $limit = $request->input('limit', 10);
        $tasks = Task::where('project_id', $projectId)
            ->with(['author', 'project'])
            ->paginate($limit);
            
        return Inertia::render('Tasks/Index', [
            'tasks' => $tasks,
            'project' => $project,
            'userId' => $userId,
            'isAuthor' => $project->author_id == $userId,
            'isContributor' => $isContributor
        ]);
    }

    // Show create form
    public function create(Request $request)
    {
        $projectId = $request->input('project_id');
        $userId = Auth::id();
        
        if (!$projectId || !$userId) {
            return redirect()->route('projects.index');
        }
        
        $project = Project::find($projectId);
        if (!$project) {
            abort(404, 'Project not found');
        }
        
        // Only project author or contributor with is_editor can create tasks
        if ($project->author_id != $userId) {
            $isEditor = Contributor::where('project_id', $projectId)
                ->where('contributor_id', $userId)
                ->where('is_editor', true)
                ->exists();
            if (!$isEditor) {
                abort(403, 'Only the project author or an editor contributor can create tasks.');
            }
        }
        
        return Inertia::render('Tasks/Create', [
            'project' => $project
        ]);
    }

    // View single task
    public function show($id)
    {
        $userId = Auth::id();
        $task = Task::with(['author', 'project', 'comments.author'])->find($id);
        
        if (!$task) {
            abort(404, 'Task not found');
        }
        
        $project = Project::find($task->project_id);
        if (!$project) {
            abort(404, 'Project not found');
        }
        
        // Only project author or any contributor can view task
        $isContributor = Contributor::where('project_id', $project->id)
            ->where('contributor_id', $userId)
            ->exists();
            
        if ($project->author_id != $userId && !$isContributor) {
            abort(403, 'Only the project author or a contributor can view this task.');
        }
        
        return Inertia::render('Tasks/Show', [
            'task' => $task,
            'project' => $project,
            'userId' => $userId,
            'isAuthor' => $project->author_id == $userId,
            'isContributor' => $isContributor
        ]);
    }

    // Show edit form
    public function edit($id)
    {
        $userId = auth()->user()->id;
        $task = Task::with('project')->find($id);
        
        if (!$task) {
            abort(404, 'Task not found');
        }
        
        $project = Project::find($task->project_id);
        if (!$project) {
            abort(404, 'Project not found');
        }
        
        // Only project author or contributor with is_editor can edit tasks
        if ($project->author_id != $userId) {
            $isEditor = Contributor::where('project_id', $project->id)
                ->where('contributor_id', $userId)
                ->where('is_editor', true)
                ->exists();
            if (!$isEditor) {
                abort(403, 'Only the project author or an editor contributor can edit tasks.');
            }
        }
        
        return Inertia::render('Tasks/Edit', [
            'task' => $task,
            'project' => $project
        ]);
    }

    // Create task
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'status' => 'required|string',
            'importance' => 'required|string',
            'due_date' => 'nullable|date',
            'description' => 'nullable|string',
        ]);

        // Authorization: Only project author or contributor with is_editor can create
        $project = Project::find($validated['project_id']);
        if (!$project) {
            abort(404, 'Project not found');
        }
        
        $userId = auth()->user()->id;
        if ($project->author_id != $userId) {
            $isEditor = Contributor::where('project_id', $project->id)
                ->where('contributor_id', $userId)
                ->where('is_editor', true)
                ->exists();
            if (!$isEditor) {
                abort(403, 'Only the project author or an editor contributor can create tasks.');
            }
        }

        $validated['author_id'] = $userId;
        $task = Task::create($validated);
        
        return redirect()->route('tasks.show', $task->id)
            ->with('success', 'Task created successfully');
    }

    // Edit task
    public function update(Request $request, $id)
    {
        $task = Task::find($id);
        if (!$task) {
            abort(404, 'Task not found');
        }
        
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'status' => 'sometimes|string',
            'importance' => 'sometimes|string',
            'due_date' => 'nullable|date',
            'description' => 'nullable|string',
        ]);

        $project = Project::find($task->project_id);
        if (!$project) {
            abort(404, 'Project not found');
        }
        
        $userId = auth()->user()->id;
        
        // Only project author or contributor with is_editor can edit tasks
        if ($project->author_id != $userId) {
            $isEditor = Contributor::where('project_id', $project->id)
                ->where('contributor_id', $userId)
                ->where('is_editor', true)
                ->exists();
            if (!$isEditor) {
                abort(403, 'Only the project author or an editor contributor can edit tasks.');
            }
        }

        $task->update($validated);
        
        return redirect()->route('tasks.show', $task->id)
            ->with('success', 'Task updated successfully');
    }

    // Delete task
    public function destroy($id)
    {
        $userId = auth()->user()->id;
        $task = Task::find($id);
        
        if (!$task) {
            abort(404, 'Task not found');
        }
        
        $project = Project::find($task->project_id);
        if (!$project) {
            abort(404, 'Project not found');
        }
        
        // Only project author or contributor with is_editor can delete tasks
        if ($project->author_id != $userId) {
            $isEditor = Contributor::where('project_id', $project->id)
                ->where('contributor_id', $userId)
                ->where('is_editor', true)
                ->exists();
            if (!$isEditor) {
                abort(403, 'Only the project author or an editor contributor can delete tasks.');
            }
        }
        
        $projectId = $task->project_id;
        $task->delete();
        
        return redirect()->route('tasks.index', ['project_id' => $projectId])
            ->with('success', 'Task deleted successfully');
    }
} 