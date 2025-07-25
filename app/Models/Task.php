<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'title',
        'author_id',
        'project_id',
        'status',
        'importance',
        'due_date',
        'description',
    ];
}
