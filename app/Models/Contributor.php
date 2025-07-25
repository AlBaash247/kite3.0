<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contributor extends Model
{
    protected $fillable = [
        'project_id',
        'contributor_id',
        'is_editor',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'contributor_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }


}
