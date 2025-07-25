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
    // List comments (project author or any contributor)
    public function index(Request $request)
    {
        $taskId = $request->input('task_id');
        $userId = $request->input('author_id');
        if (!$taskId || !$userId) {
            return response()->json(['message' => 'task_id and author_id are required'], 400);
        }
        $task = Task::find($taskId);
        if (!$task) {
            return response()->json(['message' => 'Task not found'], 404);
        }
        $project = Project::find($task->project_id);
        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }
        $isContributor = Contributor::where('project_id', $project->id)
            ->where('contributor_id', $userId)
            ->exists();
        if ($project->author_id != $userId && !$isContributor) {
            return response()->json(['message' => 'Only the project author or a contributor can view comments.'], 403);
        }
        $limit = $request->input('limit', 10);
        $comments = Comment::where('task_id', $taskId)->paginate($limit);
        return response()->json($comments);
    }

    // View single comment (project author or any contributor)
    public function show($id)
    {
        $userId = request('author_id');
        $comment = Comment::find($id);
        if (!$comment) {
            return response()->json(['message' => 'Comment not found'], 404);
        }
        $task = Task::find($comment->task_id);
        if (!$task) {
            return response()->json(['message' => 'Task not found'], 404);
        }
        $project = Project::find($task->project_id);
        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }
        $isContributor = Contributor::where('project_id', $project->id)
            ->where('contributor_id', $userId)
            ->exists();
        if ($project->author_id != $userId && !$isContributor) {
            return response()->json(['message' => 'Only the project author or a contributor can view this comment.'], 403);
        }
        return response()->json($comment);
    }

    // Create comment (project author or any contributor)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'author_id' => 'required|exists:users,id',
            'task_id' => 'required|exists:tasks,id',
        ]);
        $task = Task::find($validated['task_id']);
        if (!$task) {
            return response()->json(['message' => 'Task not found'], 404);
        }
        $project = Project::find($task->project_id);
        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }
        $userId = $validated['author_id'];
        $isContributor = Contributor::where('project_id', $project->id)
            ->where('contributor_id', $userId)
            ->exists();
        if ($project->author_id != $userId && !$isContributor) {
            return response()->json(['message' => 'Only the project author or a contributor can create comments.'], 403);
        }
        $comment = Comment::create($validated);
        return response()->json($comment, 201);
    }

    // Update comment (project author or comment author)
    public function update(Request $request, $id)
    {
        $comment = Comment::find($id);
        if (!$comment) {
            return response()->json(['message' => 'Comment not found'], 404);
        }
        $userId = $request->input('author_id');
        if (!$userId) {
            return response()->json(['message' => 'author_id is required'], 400);
        }
        $task = Task::find($comment->task_id);
        if (!$task) {
            return response()->json(['message' => 'Task not found'], 404);
        }
        $project = Project::find($task->project_id);
        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }
        if ($project->author_id != $userId && $comment->author_id != $userId) {
            return response()->json(['message' => 'Only the project author or the comment author can update this comment.'], 403);
        }
        $validated = $request->validate([
            'name' => 'sometimes|string',
        ]);
        $comment->update($validated);
        return response()->json($comment);
    }

    // Delete comment (project author or comment author)
    public function destroy($id)
    {
        $comment = Comment::find($id);
        if (!$comment) {
            return response()->json(['message' => 'Comment not found'], 404);
        }
        $userId = request('author_id');
        if (!$userId) {
            return response()->json(['message' => 'author_id is required'], 400);
        }
        $task = Task::find($comment->task_id);
        if (!$task) {
            return response()->json(['message' => 'Task not found'], 404);
        }
        $project = Project::find($task->project_id);
        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }
        if ($project->author_id != $userId && $comment->author_id != $userId) {
            return response()->json(['message' => 'Only the project author or the comment author can delete this comment.'], 403);
        }
        $comment->delete();
        return response()->json(['message' => 'Comment deleted successfully']);
    }
} 