<?php

namespace App\Http\Controllers\Web;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Task;
use App\Models\Project;
use App\Models\Contributor;
use Inertia\Inertia;

class WebCommentsController extends Controller
{
    // List comments (project author or any contributor)
    public function index(Request $request)
    {
        $taskId = $request->input('task_id');
        $userId = Auth::id();
        
        if (!$taskId || !$userId) {
            return redirect()->route('projects.index');
        }
        
        $task = Task::find($taskId);
        if (!$task) {
            abort(404, 'Task not found');
        }
        
        $project = Project::find($task->project_id);
        if (!$project) {
            abort(404, 'Project not found');
        }
        
        $isContributor = Contributor::where('project_id', $project->id)
            ->where('contributor_id', $userId)
            ->exists();
            
        if ($project->author_id != $userId && !$isContributor) {
            abort(403, 'Only the project author or a contributor can view comments.');
        }
        
        $limit = $request->input('limit', 10);
        $comments = Comment::where('task_id', $taskId)
            ->with('author')
            ->paginate($limit);
            
        return Inertia::render('Comments/Index', [
            'comments' => $comments,
            'task' => $task,
            'project' => $project,
            'userId' => $userId
        ]);
    }

    // Show create form
    public function create(Request $request)
    {
        $taskId = $request->input('task_id');
        $userId = Auth::id();
        
        if (!$taskId || !$userId) {
            return redirect()->route('projects.index');
        }
        
        $task = Task::with('project')->find($taskId);
        if (!$task) {
            abort(404, 'Task not found');
        }
        
        $project = Project::find($task->project_id);
        if (!$project) {
            abort(404, 'Project not found');
        }
        
        $isContributor = Contributor::where('project_id', $project->id)
            ->where('contributor_id', $userId)
            ->exists();
            
        if ($project->author_id != $userId && !$isContributor) {
            abort(403, 'Only the project author or a contributor can create comments.');
        }
        
        return Inertia::render('Comments/Create', [
            'task' => $task,
            'project' => $project
        ]);
    }

    // View single comment (project author or any contributor)
    public function show($id)
    {
        $userId = Auth::id();
        $comment = Comment::with(['author', 'task.project'])->find($id);
        
        if (!$comment) {
            abort(404, 'Comment not found');
        }
        
        $task = Task::find($comment->task_id);
        if (!$task) {
            abort(404, 'Task not found');
        }
        
        $project = Project::find($task->project_id);
        if (!$project) {
            abort(404, 'Project not found');
        }
        
        $isContributor = Contributor::where('project_id', $project->id)
            ->where('contributor_id', $userId)
            ->exists();
            
        if ($project->author_id != $userId && !$isContributor) {
            abort(403, 'Only the project author or a contributor can view this comment.');
        }
        
        return Inertia::render('Comments/Show', [
            'comment' => $comment,
            'task' => $task,
            'project' => $project,
            'userId' => $userId
        ]);
    }

    // Show edit form
    public function edit($id)
    {
        $userId = Auth::id();
        $comment = Comment::with(['task.project'])->find($id);
        
        if (!$comment) {
            abort(404, 'Comment not found');
        }
        
        $task = Task::find($comment->task_id);
        if (!$task) {
            abort(404, 'Task not found');
        }
        
        $project = Project::find($task->project_id);
        if (!$project) {
            abort(404, 'Project not found');
        }
        
        // Only project author or comment author can edit
        if ($project->author_id != $userId && $comment->author_id != $userId) {
            abort(403, 'Only the project author or comment author can edit this comment.');
        }
        
        return Inertia::render('Comments/Edit', [
            'comment' => $comment,
            'task' => $task,
            'project' => $project
        ]);
    }

    // Create comment (project author or any contributor)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'task_id' => 'required|exists:tasks,id',
        ]);
        
        $task = Task::find($validated['task_id']);
        if (!$task) {
            abort(404, 'Task not found');
        }
        
        $project = Project::find($task->project_id);
        if (!$project) {
            abort(404, 'Project not found');
        }
        
        $userId = Auth::id();
        $isContributor = Contributor::where('project_id', $project->id)
            ->where('contributor_id', $userId)
            ->exists();
            
        if ($project->author_id != $userId && !$isContributor) {
            abort(403, 'Only the project author or a contributor can create comments.');
        }
        
        $validated['author_id'] = $userId;
        $comment = Comment::create($validated);
        
        return redirect()->route('comments.show', $comment->id)
            ->with('success', 'Comment created successfully');
    }

    // Update comment (project author or comment author)
    public function update(Request $request, $id)
    {
        $comment = Comment::find($id);
        if (!$comment) {
            abort(404, 'Comment not found');
        }
        
        $validated = $request->validate([
            'name' => 'required|string',
        ]);
        
        $task = Task::find($comment->task_id);
        if (!$task) {
            abort(404, 'Task not found');
        }
        
        $project = Project::find($task->project_id);
        if (!$project) {
            abort(404, 'Project not found');
        }
        
        $userId = Auth::id();
        
        // Only project author or comment author can edit
        if ($project->author_id != $userId && $comment->author_id != $userId) {
            abort(403, 'Only the project author or comment author can edit this comment.');
        }
        
        $comment->update($validated);
        
        return redirect()->route('comments.show', $comment->id)
            ->with('success', 'Comment updated successfully');
    }

    // Delete comment (project author or comment author)
    public function destroy($id)
    {
        $userId = Auth::id();
        $comment = Comment::find($id);
        
        if (!$comment) {
            abort(404, 'Comment not found');
        }
        
        $task = Task::find($comment->task_id);
        if (!$task) {
            abort(404, 'Task not found');
        }
        
        $project = Project::find($task->project_id);
        if (!$project) {
            abort(404, 'Project not found');
        }
        
        // Only project author or comment author can delete
        if ($project->author_id != $userId && $comment->author_id != $userId) {
            abort(403, 'Only the project author or comment author can delete this comment.');
        }
        
        $taskId = $comment->task_id;
        $comment->delete();
        
        return redirect()->route('comments.index', ['task_id' => $taskId])
            ->with('success', 'Comment deleted successfully');
    }
} 