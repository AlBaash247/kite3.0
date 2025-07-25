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
}
