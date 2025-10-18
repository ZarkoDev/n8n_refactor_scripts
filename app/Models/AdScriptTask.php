<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdScriptTask extends Model
{
    use HasFactory;

    protected $table = 'ad_script_tasks';

    protected $fillable = [
        'reference_script',
        'outcome_description',
        'new_script',
        'analysis',
        'status',
        'error',
    ];

    protected $casts = [
        'reference_script' => 'string',
        'outcome_description' => 'string',
        'new_script' => 'string',
        'analysis' => 'string',
        'status' => 'string',
        'error' => 'string',
    ];
}


