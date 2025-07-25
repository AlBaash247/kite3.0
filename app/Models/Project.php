<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'name',
        'author_id',
        'description',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function contributors()
    {
        return $this->hasMany(Contributor::class);
    }
}
