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
    public function index(Request $request, Project $project)
    {
        $userId = $request->user()->id;


        if (!$project || !$userId) {
            return response()->json(['is_ok' => false, 'message' => 'project_id and author_id are required'], 400);
        }


        if (!$project) {
            return response()->json(['is_ok' => false, 'message' => 'Project not found'], 404);
        }

        // Only project author or any contributor can list tasks
        $isContributor = Contributor::where('project_id', $project->id)
            ->where('contributor_id', $userId)
            ->exists();

        if ($project->author_id != $userId && !$isContributor) {
            return response()->json(['is_ok' => false, 'message' => 'Only the project author or a contributor can view tasks.'], 403);
        }

        $limit = $request->input('limit', 10);
        $tasks = Task::where('project_id', $project->id)
            ->with(['author', 'project'])
            ->paginate($limit);

        return response()->json([
            'is_ok' => true,
            'message' => 'Tasks fetched successfully',
            'payload' => $tasks
        ]);
    }

    // View single task
    public function show(Task $task)
    {
        $userId = request()->user()->id;

        $task = $task::with(['author', 'project', 'comments.author']);

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
    public function update(Request $request)
    {
        $task = Task::find($request->task_id);
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
    public function destroy(Request $request, Task $task)
    {
        $userId = $request->user()->id;


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

    // Search tasks
    public function search(Request $request)
    {
        // user can search by name AND+OR range of dates AND+OR status AND+OR importance AND+OR Assignee

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'status' => 'nullable|string',
            'importance' => 'nullable|string',
            'date_start' => 'nullable|date',
            'date_end' => 'nullable|date|after_or_equal:date_start',
            'assignee_id' => 'nullable|integer|exists:users,id',
            'project_id' => 'nullable|integer|exists:projects,id',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = Task::query();

        // Search by task name (partial match)
        if (!empty($validated['name'])) {
            $query->where('name', 'like', '%' . $validated['name'] . '%');
        }

        // Search by status
        if (!empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        // Search by importance
        if (!empty($validated['importance'])) {
            $query->where('importance', $validated['importance']);
        }

        // Search by date range
        if (!empty($validated['date_start'])) {
            $query->where('due_date', '>=', $validated['date_start']);
        }
        if (!empty($validated['date_end'])) {
            $query->where('due_date', '<=', $validated['date_end']);
        }

        // Search by assignee
        if (!empty($validated['assignee_id'])) {
            $query->whereHas('assignments', function ($subQuery) use ($validated) {
                $subQuery->where('user_id', $validated['assignee_id']);
            });
        }

        // Filter by project if specified
        if (!empty($validated['project_id'])) {
            $query->where('project_id', $validated['project_id']);
        }

        // Get the current user
        $userId = $request->user()->id;

        // Filter tasks based on user permissions
        $query->where(function ($subQuery) use ($userId) {
            // User can see tasks from projects they authored
            $subQuery->whereHas('project', function ($projectQuery) use ($userId) {
                $projectQuery->where('author_id', $userId);
            })
                // OR tasks from projects where they are contributors
                ->orWhereHas('project.contributors', function ($contributorQuery) use ($userId) {
                    $contributorQuery->where('contributor_id', $userId);
                })
                // OR tasks assigned to them
                ->orWhereHas('assignments', function ($assignmentQuery) use ($userId) {
                    $assignmentQuery->where('user_id', $userId);
                });
        });

        // Include relationships for better response data
        $query->with(['author', 'project', 'assignments.user']);

        // Order by creation date (newest first)
        $query->orderBy('created_at', 'desc');

        // Paginate results
        $perPage = $validated['per_page'] ?? 15;
        $tasks = $query->paginate($perPage);

        return response()->json([
            'is_ok' => true,
            'message' => 'Search completed successfully',
            'payload' => $tasks
        ]);
    }
}
