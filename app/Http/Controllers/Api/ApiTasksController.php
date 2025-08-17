<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contributor;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Models\Task;

class ApiTasksController extends Controller
{
    public function ping()
    {
        return response()->json(['is_ok' => true, 'message' => 'pong']);
    }

    // List tasks with pagination
    public function index(Request $request)
    {
        $projectId = $request->input('project_id');
        $userId = $request->user()->id;

        if (!$projectId || !$userId) {
            return response()->json(['is_ok' => false, 'message' => 'project_id and author_id are required'], 400);
        }

        $project = Project::find($projectId);
        if (!$project) {
            return response()->json(['is_ok' => false, 'message' => 'Project not found'], 404);
        }

        // Only project author or any contributor can list tasks
        $isContributor = Contributor::where('project_id', $projectId)
            ->where('contributor_id', $userId)
            ->exists();

        if ($project->author_id != $userId && !$isContributor) {
            return response()->json(['is_ok' => false, 'message' => 'Only the project author or a contributor can view tasks.'], 403);
        }

        $limit = $request->input('limit', 10);
        $tasks = Task::where('project_id', $projectId)
            ->with(['author', 'project'])
            ->paginate($limit);

        return response()->json([
            'is_ok' => true,
            'message' => 'Tasks fetched successfully',
            'payload' => $tasks
        ]);
    }

    // View single task
    public function show($id)
    {
        $userId = request('author_id');
        $task = Task::with(['author', 'project', 'comments.author'])->find($id);


        if (!$task) {
            return response()->json(['is_ok' => false, 'message' => 'Task not found'], 404);
        }

        $project = Project::find($task->project_id);
        if (!$project) {
            return response()->json(['is_ok' => false, 'message' => 'Project not found'], 404);
        }

        // Only project author or any contributor can view task
        $isContributor = Contributor::where('project_id', $project->id)
            ->where('contributor_id', $userId)
            ->exists();

        if ($project->author_id != $userId && !$isContributor) {
            return response()->json(['is_ok' => false, 'message' => 'Only the project author or a contributor can view this task.'], 403);
        }

        return response()->json($task);
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
            return response()->json(['is_ok' => false, 'message' => 'Project not found'], 404);
        }

        $userId = $request->user()->id;
        if ($project->author_id != $userId) {
            $isEditor = Contributor::where('project_id', $project->id)
                ->where('contributor_id', $userId)
                ->where('is_editor', true)
                ->exists();
            if (!$isEditor) {
                return response()->json(['is_ok' => false, 'message' => 'Only the project author or an editor contributor can create tasks.'], 403);
            }
        }

        $validated['author_id'] = $userId;
        $task = Task::create($validated);

        return response()->json([
            'is_ok' => true,
            'message' => 'Task created successfully',
            'payload' => $task
        ], 201);
    }

    // Edit task
    public function update(Request $request, $id)
    {
        $task = Task::find($id);
        if (!$task) {
            return response()->json(['is_ok' => false, 'message' => 'Task not found'], 404);
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
            return response()->json(['is_ok' => false, 'message' => 'Project not found'], 404);
        }

        $userId = $request->user()->id;

        // Only project author or contributor with is_editor can edit tasks
        if ($project->author_id != $userId) {
            $isEditor = Contributor::where('project_id', $project->id)
                ->where('contributor_id', $userId)
                ->where('is_editor', true)
                ->exists();
            if (!$isEditor) {
                return response()->json(['is_ok' => false, 'message' => 'Only the project author or an editor contributor can edit tasks.'], 403);
            }
        }

        $task->update($validated);

        return response()->json([
            'is_ok' => true,
            'message' => 'Task updated successfully',
            'payload' => $task
        ]);
    }

    // Delete task
    public function destroy($id)
    {
        $userId = request('author_id');
        $task = Task::find($id);

        if (!$task) {
            return response()->json(['is_ok' => false, 'message' => 'Task not found'], 404);
        }

        $project = Project::find($task->project_id);
        if (!$project) {
            return response()->json(['is_ok' => false, 'message' => 'Project not found'], 404);
        }

        // Only project author or contributor with is_editor can delete tasks
        if ($project->author_id != $userId) {
            $isEditor = Contributor::where('project_id', $project->id)
                ->where('contributor_id', $userId)
                ->where('is_editor', true)
                ->exists();
            if (!$isEditor) {
                return response()->json(['is_ok' => false, 'message' => 'Only the project author or an editor contributor can delete tasks.'], 403);
            }
        }

        $task->delete();

        return response()->json(['is_ok' => true, 'message' => 'Task deleted successfully']);
    }
}
