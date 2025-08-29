<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Task extends Model
{
    protected $fillable = [
        'name',
        'author_id',
        'project_id',
        'status',
        'importance',
        'due_date',
        'description',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get the task assignments for this task.
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(TaskAssignment::class);
    }

    /**
     * Get the users assigned to this task.
     */
    public function assignedUsers(): HasManyThrough
    {
        return $this->hasManyThrough(User::class, TaskAssignment::class, 'task_id', 'id', 'id', 'user_id');
    }
}
