<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Task;
use App\Models\Project;
use App\Models\Contributor;

class ApiCommentsController extends Controller
{
    public function ping()
    {
        return response()->json(['is_ok' => true, 'message' => 'pong']);
    }

    // List comments (project author or any contributor)
    public function index(Request $request, $task_id)
    {
        $userId = $request->user()->id;

        if (!$task_id || !$userId) {
            return response()->json(['is_ok' => false, 'message' => 'task_id and author_id are required'], 400);
        }

        $task = Task::find($task_id);
        if (!$task) {
            return response()->json(['is_ok' => false, 'message' => 'Task not found'], 404);
        }

        $project = Project::find($task->project_id);
        if (!$project) {
            return response()->json(['is_ok' => false, 'message' => 'Project not found'], 404);
        }

        $isContributor = Contributor::where('project_id', $project->id)
            ->where('contributor_id', $userId)
            ->exists();

        if ($project->author_id != $userId && !$isContributor) {
            return response()->json(['is_ok' => false, 'message' => 'Only the project author or a contributor can view comments.'], 403);
        }

        $limit = $request->input('limit', 10);
        $comments = Comment::where('task_id', $task_id)
            ->with('author')
            ->paginate($limit);

        return response()->json([
            'is_ok' => true,
            'message' => 'Comments fetched successfully',
            'payload' => $comments
        ]);
    }

    // View single comment (project author or any contributor)
    public function show($id)
    {
        $userId = request()->user()->id;;
        $comment = Comment::with(['author', 'task.project'])->find($id);

        if (!$comment) {
            return response()->json(['is_ok' => false, 'message' => 'Comment not found'], 404);
        }

        $task = Task::find($comment->task_id);
        if (!$task) {
            return response()->json(['is_ok' => false, 'message' => 'Task not found'], 404);
        }

        $project = Project::find($task->project_id);
        if (!$project) {
            return response()->json(['is_ok' => false, 'message' => 'Project not found'], 404);
        }

        $isContributor = Contributor::where('project_id', $project->id)
            ->where('contributor_id', $userId)
            ->exists();

        if ($project->author_id != $userId && !$isContributor) {
            return response()->json(['is_ok' => false, 'message' => 'Only the project author or a contributor can view this comment.'], 403);
        }

        return response()->json([
            'is_ok' => true,
            'message' => 'Comment fetched successfully',
            'payload' => $comment
        ]);
    }

    // Create comment (project author or any contributor)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'task_id' => 'required|exists:tasks,id',
        ]);



        if (!$validated) {
            return response()->json(['is_ok' => false, 'message' => 'Error: validation failed!'], 403);
        }

        $task = Task::find($validated['task_id']);
        if (!$task) {
            return response()->json(['is_ok' => false,
            'message' => "Task not found, task_id: " . $validated['task_id']], 404);
        }

        $project = Project::find($task->project_id);
        if (!$project) {
            return response()->json(['is_ok' => false, 'message' => 'Project not found'], 404);
        }

        $userId = $request->user()->id;
        $isContributor = Contributor::where('project_id', $project->id)
            ->where('contributor_id', $userId)
            ->exists();

        if ($project->author_id != $userId && !$isContributor) {
            return response()->json(['is_ok' => false, 'message' => 'Only the project author or a contributor can create comments.'], 403);
        }

        $validated['author_id'] = $userId;
        $comment = Comment::create($validated);

        return response()->json([
            'is_ok' => true,
            'message' => 'Comment created successfully',
            'payload' => $comment
        ], 201);
    }

    // Update comment (project author or comment author)
    public function update(Request $request, $id)
    {
        $comment = Comment::find($id);
        if (!$comment) {
            return response()->json(['is_ok' => false, 'message' => 'Comment not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string',
        ]);

        $task = Task::find($comment->task_id);
        if (!$task) {
            return response()->json(['is_ok' => false, 'message' => 'Task not found'], 404);
        }

        $project = Project::find($task->project_id);
        if (!$project) {
            return response()->json(['is_ok' => false, 'message' => 'Project not found'], 404);
        }

        $userId = $request->user()->id;

        // Only project author or comment author can edit
        if ($project->author_id != $userId && $comment->author_id != $userId) {
            return response()->json(['is_ok' => false, 'message' => 'Only the project author or comment author can edit this comment.'], 403);
        }

        $comment->update($validated);

        return response()->json([
            'is_ok' => true,
            'message' => 'Comment updated successfully',
            'payload' => $comment
        ]);
    }

    // Delete comment (project author or comment author)
    public function destroy($id)
    {
        $userId = request()->user()->id;;
        $comment = Comment::find($id);

        if (!$comment) {
            return response()->json(['is_ok' => false, 'message' => 'Comment not found'], 404);
        }

        $task = Task::find($comment->task_id);
        if (!$task) {
            return response()->json(['is_ok' => false, 'message' => 'Task not found'], 404);
        }

        $project = Project::find($task->project_id);
        if (!$project) {
            return response()->json(['is_ok' => false, 'message' => 'Project not found'], 404);
        }

        // Only project author or comment author can delete
        if ($project->author_id != $userId && $comment->author_id != $userId) {
            return response()->json(['is_ok' => false, 'message' => 'Only the project author or comment author can delete this comment.'], 403);
        }

        $comment->delete();

        return response()->json(['is_ok' => true, 'message' => 'Comment deleted successfully']);
    }
}
