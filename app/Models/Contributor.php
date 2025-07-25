<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contributor extends Model
{
    protected $fillable = [
        'project_id',
        'contributor_id',
        'is_editor',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'contributor_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
