<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskAssignment;
use App\Models\Contributor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class TaskSearchTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $project;
    protected $task1;
    protected $task2;
    protected $task3;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user
        $this->user = User::factory()->create();

        // Create a project
        $this->project = Project::create([
            'name' => 'Test Project',
            'description' => 'A test project',
            'author_id' => $this->user->id,
        ]);

        // Create tasks with different attributes
        $this->task1 = Task::create([
            'name' => 'High Priority Bug Fix',
            'author_id' => $this->user->id,
            'project_id' => $this->project->id,
            'status' => 'in_progress',
            'importance' => 'high',
            'due_date' => '2025-12-31',
            'description' => 'Fix critical bug in production',
        ]);

        $this->task2 = Task::create([
            'name' => 'Low Priority Documentation',
            'author_id' => $this->user->id,
            'project_id' => $this->project->id,
            'status' => 'pending',
            'importance' => 'low',
            'due_date' => '2025-11-30',
            'description' => 'Update user documentation',
        ]);

        $this->task3 = Task::create([
            'name' => 'Medium Priority Feature',
            'author_id' => $this->user->id,
            'project_id' => $this->project->id,
            'status' => 'completed',
            'importance' => 'medium',
            'due_date' => '2025-10-31',
            'description' => 'Implement new feature',
        ]);

        // Authenticate user
        Sanctum::actingAs($this->user);
    }

    public function test_search_by_name()
    {
        $response = $this->postJson('/api/tasks/search', [
            'name' => 'Bug'
        ]);

        $response->assertStatus(200)
            ->assertJson(['is_ok' => true])
            ->assertJsonCount(1, 'payload.data');

        $this->assertEquals('High Priority Bug Fix', $response->json('payload.data.0.name'));
    }

    public function test_search_by_status()
    {
        $response = $this->postJson('/api/tasks/search', [
            'status' => 'completed'
        ]);

        $response->assertStatus(200)
            ->assertJson(['is_ok' => true])
            ->assertJsonCount(1, 'payload.data');

        $this->assertEquals('completed', $response->json('payload.data.0.status'));
    }

    public function test_search_by_importance()
    {
        $response = $this->postJson('/api/tasks/search', [
            'importance' => 'high'
        ]);

        $response->assertStatus(200)
            ->assertJson(['is_ok' => true])
            ->assertJsonCount(1, 'payload.data');

        $this->assertEquals('high', $response->json('payload.data.0.importance'));
    }

    public function test_search_by_date_range()
    {
        $response = $this->postJson('/api/tasks/search', [
            'start_date' => '2025-11-01',
            'end_date' => '2025-12-31'
        ]);

        $response->assertStatus(200)
            ->assertJson(['is_ok' => true])
            ->assertJsonCount(2, 'payload.data');
    }

    public function test_search_by_project_id()
    {
        $response = $this->postJson('/api/tasks/search', [
            'project_id' => $this->project->id
        ]);

        $response->assertStatus(200)
            ->assertJson(['is_ok' => true])
            ->assertJsonCount(3, 'payload.data');
    }

    public function test_search_by_assignee()
    {
        // Create another user and assign them to a task
        $assignee = User::factory()->create();
        TaskAssignment::create([
            'task_id' => $this->task1->id,
            'user_id' => $assignee->id,
            'assigned_by' => $this->user->id,
        ]);

        $response = $this->postJson('/api/tasks/search', [
            'assignee_id' => $assignee->id
        ]);

        $response->assertStatus(200)
            ->assertJson(['is_ok' => true])
            ->assertJsonCount(1, 'payload.data');

        $this->assertEquals($this->task1->id, $response->json('payload.data.0.id'));
    }

    public function test_search_with_multiple_criteria()
    {
        $response = $this->postJson('/api/tasks/search', [
            'status' => 'in_progress',
            'importance' => 'high'
        ]);

        $response->assertStatus(200)
            ->assertJson(['is_ok' => true])
            ->assertJsonCount(1, 'payload.data');

        $this->assertEquals('High Priority Bug Fix', $response->json('payload.data.0.name'));
    }

    public function test_search_with_pagination()
    {
        $response = $this->postJson('/api/tasks/search', [
            'per_page' => 2
        ]);

        $response->assertStatus(200)
            ->assertJson(['is_ok' => true])
            ->assertJsonCount(2, 'payload.data');

        $this->assertEquals(2, $response->json('payload.per_page'));
    }

    public function test_search_returns_empty_when_no_matches()
    {
        $response = $this->postJson('/api/tasks/search', [
            'name' => 'NonExistentTask'
        ]);

        $response->assertStatus(200)
            ->assertJson(['is_ok' => true])
            ->assertJsonCount(0, 'payload.data');
    }

    public function test_search_validation_errors()
    {
        $response = $this->postJson('/api/tasks/search', [
            'end_date' => '2025-01-01',
            'start_date' => '2025-12-31'
        ]);

        $response->assertStatus(422);
    }
}
