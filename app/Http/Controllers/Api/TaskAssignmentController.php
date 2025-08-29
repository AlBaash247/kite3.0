<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class TaskAssignmentController extends Controller
{

    public function ping()
    {
        return response()->json(['is_ok' => true, 'message' => 'pong']);
    }



    /**
     * Assign a user to a task.
     */
    public function assign(Request $request, Task $task): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($request->user_id);
        $currentUser = Auth::user();

        // Check if user is already assigned to this task
        $existingAssignment = TaskAssignment::where('task_id', $task->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingAssignment) {
            throw ValidationException::withMessages([
                'user_id' => 'User is already assigned to this task.',
            ]);
        }

        // Check if the current user has permission to assign users to this task
        // This could be the task author, project author, or a contributor with editor rights
        $canAssign = $this->canAssignToTask($currentUser, $task);

        if (!$canAssign) {
            return response()->json([
                'is_ok' => false,
                'message' => 'You do not have permission to assign users to this task.'
            ], 403);
        }

        // Create the assignment
        $assignment = TaskAssignment::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'assigned_by' => $currentUser->id,
        ]);

        return response()->json([
            'is_ok' => true,
            'message' => 'User assigned to task successfully.',
            'payload' => $assignment->load(['user', 'assignedBy']),
        ], 201);
    }

    /**
     * Unassign a user from a task.
     */
    public function unassign(Request $request, Task $task): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($request->user_id);
        $currentUser = Auth::user();

        // Find the assignment
        $assignment = TaskAssignment::where('task_id', $task->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$assignment) {
            return response()->json([
                'is_ok' => false,
                'message' => 'User is not assigned to this task.'
            ], 404);
        }

        // Check if the current user has permission to unassign users from this task
        $canUnassign = $this->canUnassignFromTask($currentUser, $task, $assignment);

        if (!$canUnassign) {
            return response()->json([
                'is_ok' => false,
                'message' => 'You do not have permission to unassign users from this task.'
            ], 403);
        }

        // Delete the assignment
        $assignment->delete();

        return response()->json([
            'is_ok' => true,
            'message' => 'User unassigned from task successfully.',
            'payload' => $assignment,
        ]);
    }

    /**
     * Get all assignments for a task.
     */
    public function taskAssignments(Task $task): JsonResponse
    {
        $assignments = $task->assignments()->with(['user', 'assignedBy'])->get();

        return response()->json([
            'is_ok' => true,
            'message' => 'Task assignments fetched successfully',
            'payload' => $assignments,
        ]);
    }

    /**
     * Get all tasks assigned to the current user.
     */
    public function myAssignments(): JsonResponse
    {
        $user = Auth::user();

        $assignments = $user->taskAssignments()->with(['task', 'assignedBy', 'project'])->get();


        return response()->json([
            'is_ok' => true,
            'message' => 'My assignments fetched successfully',
            'payload' => $assignments,
        ]);


    }

    /**
     * Check if a user can assign users to a task.
     */
    private function canAssignToTask(User $user, Task $task): bool
    {
        // Task author can assign users
        if ($task->author_id === $user->id) {
            return true;
        }

        // Project author can assign users
        if ($task->project->author_id === $user->id) {
            return true;
        }

        // Contributors with editor rights can assign users
        $contribution = $task->project->contributors()
            ->where('contributor_id', $user->id)
            ->where('is_editor', true)
            ->first();

        return $contribution !== null;
    }

    /**
     * Check if a user can unassign users from a task.
     */
    private function canUnassignFromTask(User $user, Task $task, TaskAssignment $assignment): bool
    {
        // Task author can unassign users
        if ($task->author_id === $user->id) {
            return true;
        }

        // Project author can unassign users
        if ($task->project->author_id === $user->id) {
            return true;
        }

        // Contributors with editor rights can unassign users
        $contribution = $task->project->contributors()
            ->where('contributor_id', $user->id)
            ->where('is_editor', true)
            ->first();

        if ($contribution) {
            return true;
        }

        // Users can unassign themselves
        if ($assignment->user_id === $user->id) {
            return true;
        }

        return false;
    }
}
