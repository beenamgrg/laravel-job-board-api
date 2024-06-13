<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema()
 */

class JobApplication extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'resume',
        'cover_letter',
        'user_id',
        'job_id'
    ];
}
