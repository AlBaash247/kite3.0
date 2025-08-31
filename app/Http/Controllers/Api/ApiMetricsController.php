<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Contributor;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskAssignment;
use Illuminate\Support\Facades\Auth;

class ApiMetricsController extends Controller
{
    public function ping()
    {
        return response()->json(['is_ok' => true, 'message' => 'pong']);
    }

    public function index()
    {
        $currentUser = Auth::user();
        // total projects
        $totalProjects = Project::count();

        //total contributors for current user projects
        $totalContributors = Contributor::where('user_id', $currentUser->id)->count();


        //total comments for current user projects
        $totalComments = Comment::where('author_id', $currentUser->id)->count();

        //total task assignments for current user projects
        $totalTaskAssignments = TaskAssignment::where('user_id', $currentUser->id)->count();

        // total tasks with no assignee
        $totalTasksNoAssignee = Task::whereDoesntHave('assignments')->count();



        //project with most tasks
        $projectWithMostTasks = Project::withCount('tasks')->orderBy('tasks_count', 'desc')->first();

        // project with most contributors
        $projectWithMostContributors = Project::withCount('contributors')->orderBy('contributors_count', 'desc')->first();

        // number of projects where all tasks are completed
        $projectsWithAllTasksCompleted = Project::whereDoesntHave('tasks', function ($query) {
            $query->where('status', '!=', 'completed');
        })->whereHas('tasks')->count();


        // Get metrics from helper methods
        $taskMetrics = $this->getTasksMetrics($currentUser);
        $taskStatusMetrics = $this->getTaskStatusMetrics($currentUser);
        $taskPriorityMetrics = $this->getTaskPrioritiesMetrics($currentUser);


        $payload = [
            'total_projects' => $totalProjects,
            'total_contributors' => $totalContributors,
            'total_comments' => $totalComments,
            'total_task_assignments' => $totalTaskAssignments,
            'total_tasks_no_assignee' => $totalTasksNoAssignee,
            'project_with_most_tasks' => $projectWithMostTasks,
            'project_with_most_contributors' => $projectWithMostContributors,
            'projects_with_all_tasks_completed' => $projectsWithAllTasksCompleted
        ];

        $payload +=  $taskMetrics + $taskStatusMetrics + $taskPriorityMetrics;


        return response()->json([
            'is_ok' => true,
            'message' => 'Metrics fetched successfully',
            'payload' => $payload
        ]);
    }

    public function getTasksMetrics($currentUser)
    {
        //total tasks for current user projects
        $totalTasks = Task::where('author_id', $currentUser->id)->count();

        //number of tasks due today for current user
        $tasksDueToday = Task::where('author_id', $currentUser->id)->where('due_date', now()->toDateString())->count();


        // number of tasks due today for current user as assignee
        $tasksDueTodayAsAssignee = Task::whereHas('assignments', function ($query) use ($currentUser) {
            $query->where('user_id', $currentUser->id);
        })->where('due_date', now()->toDateString())->count();

        $tasksDueToday += $tasksDueTodayAsAssignee;


        //number of tasks due in the next 7 days for current user
        $tasksDueIn7Days = Task::where('author_id', $currentUser->id)->where('due_date', '>=', now()->toDateString())->where('due_date', '<=', now()->addDays(7)->toDateString())->count();


        // number of tasks due in the next 7 days for current user as assignee
        $tasksDueIn7DaysAsAssignee = Task::whereHas('assignments', function ($query) use ($currentUser) {
            $query->where('user_id', $currentUser->id);
        })->where('due_date', '>=', now()->toDateString())->where('due_date', '<=', now()->addDays(7)->toDateString())->count();

        $tasksDueIn7Days += $tasksDueIn7DaysAsAssignee;




        //number of tasks past due for current user
        $tasksPastDue = Task::where('author_id', $currentUser->id)
            ->where('due_date', '<', now()->toDateString())
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->count();

        // number of tasks past due for current user as assignee
        $tasksPastDueAsAssignee = Task::whereHas('assignments', function ($query) use ($currentUser) {
            $query->where('user_id', $currentUser->id);
        })->where('due_date', '<', now()->toDateString())
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->count();

        $tasksPastDue += $tasksPastDueAsAssignee;

        return [
            'total_tasks' => $totalTasks,
            'tasks_due_today' => $tasksDueToday,
            'tasks_due_in_7_days' => $tasksDueIn7Days,
            'tasks_past_due' => $tasksPastDue,
        ];
    }

    public function getTaskStatusMetrics($currentUser)
    {

        //number of pending tasks for current user
        $tasksPending = Task::where('author_id', $currentUser->id)->where('status', 'pending')->count();


        // number of pending tasks for current user as assignee
        $tasksPendingAsAssignee = Task::whereHas('assignments', function ($query) use ($currentUser) {
            $query->where('user_id', $currentUser->id);
        })->where('status', 'pending')->count();

        $tasksPending += $tasksPendingAsAssignee;



        //number of tasks in progress for current user
        $tasksInProgress = Task::where('author_id', $currentUser->id)->where('status', 'in_progress')->count();


        // number of tasks in progress for current user as assignee
        $tasksInProgressAsAssignee = Task::whereHas('assignments', function ($query) use ($currentUser) {
            $query->where('user_id', $currentUser->id);
        })->where('status', 'in_progress')->count();

        $tasksInProgress += $tasksInProgressAsAssignee;

        //number of tasks in review for current user
        $tasksInReview = Task::where('author_id', $currentUser->id)->where('status', 'in_review')->count();


        // number of tasks in review for current user as assignee
        $tasksInReviewAsAssignee = Task::whereHas('assignments', function ($query) use ($currentUser) {
            $query->where('user_id', $currentUser->id);
        })->where('status', 'in_review')->count();

        $tasksInReview += $tasksInReviewAsAssignee;


        //number of tasks completed for current user
        $tasksCompleted = Task::where('author_id', $currentUser->id)->where('status', 'completed')->count();


        // number of tasks completed for current user as assignee

        $tasksCompletedAsAssignee = Task::whereHas('assignments', function ($query) use ($currentUser) {
            $query->where('user_id', $currentUser->id);
        })->where('status', 'completed')->count();

        $tasksCompleted += $tasksCompletedAsAssignee;

        //number of cancelled tasks for current user
        $tasksCancelled = Task::where('author_id', $currentUser->id)->where('status', 'cancelled')->count();


        // number of cancelled tasks for current user as assignee

        $tasksCancelledAsAssignee = Task::whereHas('assignments', function ($query) use ($currentUser) {
            $query->where('user_id', $currentUser->id);
        })->where('status', 'cancelled')->count();

        $tasksCancelled += $tasksCancelledAsAssignee;

        return [
            'tasks_pending' => $tasksPending,
            'tasks_in_progress' => $tasksInProgress,
            'tasks_completed' => $tasksCompleted,
            'tasks_in_review' => $tasksInReview,
            'tasks_cancelled' => $tasksCancelled,
        ];
    }

    public function getTaskPrioritiesMetrics($currentUser)
    {

        //number of tasks with low priority for current user
        $tasksLowPriority = Task::where('author_id', $currentUser->id)->where('priority', 'low')->count();


        // number of tasks with low priority for current user as assignee
        $tasksLowPriorityAsAssignee = Task::whereHas('assignments', function ($query) use ($currentUser) {
            $query->where('user_id', $currentUser->id);
        })->where('priority', 'low')->count();

        $tasksLowPriority += $tasksLowPriorityAsAssignee;



        //number of tasks with medium priority for current user
        $tasksMediumPriority = Task::where('author_id', $currentUser->id)->where('priority', 'medium')->count();


        //number of tasks with medium priority for current user as assignee
        $tasksMediumPriorityAsAssignee = Task::whereHas('assignments', function ($query) use ($currentUser) {
            $query->where('user_id', $currentUser->id);
        })->where('priority', 'medium')->count();

        $tasksMediumPriority += $tasksMediumPriorityAsAssignee;


        //number of tasks with high priority for current user
        $tasksHighPriority = Task::where('author_id', $currentUser->id)->where('priority', 'high')->count();


        $tasksHighPriorityAsAssignee = Task::whereHas('assignments', function ($query) use ($currentUser) {
            $query->where('user_id', $currentUser->id);
        })->where('priority', 'high')->count();

        $tasksHighPriority += $tasksHighPriorityAsAssignee;



        //number of tasks with critical priority for current user
        $tasksCriticalPriority = Task::where('author_id', $currentUser->id)->where('priority', 'critical')->count();


        $tasksCriticalPriorityAsAssignee = Task::whereHas('assignments', function ($query) use ($currentUser) {
            $query->where('user_id', $currentUser->id);
        })->where('priority', 'critical')->count();

        $tasksCriticalPriority += $tasksCriticalPriorityAsAssignee;

        return [
            'tasks_low_priority' => $tasksLowPriority,
            'tasks_medium_priority' => $tasksMediumPriority,
            'tasks_high_priority' => $tasksHighPriority,
            'tasks_critical_priority' => $tasksCriticalPriority,
        ];
    }

    public function taskDueTodayList()
    {
        $currentUser = Auth::user();

        //tasks due today for current user which are not completed or cancelled
        $tasksDueToday = Task::with('project')->where('author_id', $currentUser->id)
            ->where('due_date', now()->toDateString())
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->get();


        //tasks due today for current user as assignee which are not completed or cancelled
        $tasksDueTodayAsAssignee = Task::with('project')->whereHas('assignments', function ($query) use ($currentUser) {
            $query->where('user_id', $currentUser->id);
        })->where('due_date', now()->toDateString())
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->get();

        $tasksDueToday = $tasksDueToday->merge($tasksDueTodayAsAssignee);

        return response()->json([
            'is_ok' => true,
            'message' => 'Tasks due today fetched successfully',
            'payload' => $tasksDueToday
        ]);
    }

    public function taskDueIn7DaysList()
    {
        $currentUser = Auth::user();

        //tasks due in 7 days for current user which are not completed or cancelled
        $tasksDueIn7Days = Task::with('project')->where('author_id', $currentUser->id)
            ->where('due_date', '>=', now()->toDateString())->where('due_date', '<=', now()->addDays(7)->toDateString())
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->get();

        //tasks due in 7 days for current user as assignee which are not completed or cancelled
        $tasksDueIn7DaysAsAssignee = Task::with('project')->whereHas('assignments', function ($query) use ($currentUser) {
            $query->where('user_id', $currentUser->id);
        })->where('due_date', '>=', now()->toDateString())->where('due_date', '<=', now()->addDays(7)->toDateString())
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->get();

        $tasksDueIn7Days = $tasksDueIn7Days->merge($tasksDueIn7DaysAsAssignee);

        return response()->json([
            'is_ok' => true,
            'message' => 'Tasks due in 7 days fetched successfully',
            'payload' => $tasksDueIn7Days
        ]);
    }

    public function taskPastDueList()
    {
        $currentUser = Auth::user();

        //tasks past due for current user which are not completed or cancelled
        $tasksPastDue = Task::with('project')->where('author_id', $currentUser->id)
            ->where('due_date', '<', now()->toDateString())
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->get();

        //tasks past due for current user as assignee which are not completed or cancelled
        $tasksPastDueAsAssignee = Task::with('project')->whereHas('assignments', function ($query) use ($currentUser) {
            $query->where('user_id', $currentUser->id);
        })->where('due_date', '<', now()->toDateString())
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->get();

        $tasksPastDue = $tasksPastDue->merge($tasksPastDueAsAssignee);

        return response()->json([
            'is_ok' => true,
            'message' => 'Tasks past due fetched successfully',
            'payload' => $tasksPastDue
        ]);
    }
}
