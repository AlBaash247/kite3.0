<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contributor;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Models\Task;

class ApiTasksController extends Controller
{
    // List tasks with pagination
    public function index(Request $request)
    {
        $projectId = $request->input('project_id');
        $userId = $request->input('author_id');
        if (!$projectId || !$userId) {
            return response()->json(['message' => 'project_id and author_id are required'], 400);
        }
        $project = Project::find($projectId);
        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }
        // Only project author or any contributor can list tasks
        $isContributor = Contributor::where('project_id', $projectId)
            ->where('contributor_id', $userId)
            ->exists();
        if ($project->author_id != $userId && !$isContributor) {
            return response()->json(['message' => 'Only the project author or a contributor can view tasks.'], 403);
        }
        $limit = $request->input('limit', 10);
        $tasks = Task::where('project_id', $projectId)->paginate($limit);
        return response()->json($tasks);
    }

    // View single task
    public function show($id)
    {
        $userId = request('author_id');
        $task = Task::find($id);
        if (!$task) {
            return response()->json(['message' => 'Task not found'], 404);
        }
        $project = Project::find($task->project_id);
        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }
        // Only project author or any contributor can view task
        $isContributor = Contributor::where('project_id', $project->id)
            ->where('contributor_id', $userId)
            ->exists();
        if ($project->author_id != $userId && !$isContributor) {
            return response()->json(['message' => 'Only the project author or a contributor can view this task.'], 403);
        }
        return response()->json($task);
    }

    // Create task
    public function store(Request $request)
    {
        $validated = $request->validate([
            'author_id' => 'required|exists:users,id',
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
            return response()->json(['message' => 'Project not found'], 404);
        }
        $userId = $validated['author_id'];
        if ($project->author_id != $userId) {
            $isEditor = Contributor::where('project_id', $project->id)
                ->where('contributor_id', $userId)
                ->where('is_editor', true)
                ->exists();
            if (!$isEditor) {
                return response()->json(['message' => 'Only the project author or an editor contributor can create tasks.'], 403);
            }
        }

        $task = Task::create($validated);
        return response()->json($task, 201);
    }

    // Edit task
    public function update(Request $request, $id)
    {
        $task = Task::find($id);
        if (!$task) {
            return response()->json(['message' => 'Task not found'], 404);
        }
        $validated = $request->validate([
            'author_id' => 'sometimes|exists:users,id',
            'project_id' => 'sometimes|integer',
            'name' => 'sometimes|string|max:255',
            'status' => 'sometimes|string',
            'importance' => 'sometimes|string',
            'due_date' => 'nullable|date',
            'description' => 'nullable|string',
        ]);

        // Authorization: Only project author or contributor with is_editor can edit
        $userId = $validated['author_id'] ?? $task->author_id;
        $projectId = $validated['project_id'] ?? $task->project_id;
        $project = Project::find($projectId);
        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }
        if ($project->author_id != $userId) {
            $isEditor = Contributor::where('project_id', $project->id)
                ->where('contributor_id', $userId)
                ->where('is_editor', true)
                ->exists();
            if (!$isEditor) {
                return response()->json(['message' => 'Only the project author or an editor contributor can edit tasks.'], 403);
            }
        }

        $task->update($validated);
        return response()->json($task);
    }

    // Delete task
    public function destroy($id)
    {
        $task = Task::find($id);
        if (!$task) {
            return response()->json(['message' => 'Task not found'], 404);
        }
        // Authorization: Only project author or contributor with is_editor can delete
        $userId = request('author_id', $task->author_id);
        $project = Project::find($task->project_id);
        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }
        if ($project->author_id != $userId) {
            $isEditor = Contributor::where('project_id', $project->id)
                ->where('contributor_id', $userId)
                ->where('is_editor', true)
                ->exists();
            if (!$isEditor) {
                return response()->json(['message' => 'Only the project author or an editor contributor can delete tasks.'], 403);
            }
        }
        $task->delete();
        return response()->json(['message' => 'Task deleted successfully']);
    }
} 