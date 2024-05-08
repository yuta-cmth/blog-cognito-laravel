<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CognitoUser extends Model
{
    use HasFactory;
    protected $primaryKey = 'username';

    protected $fillable = [
        'username',
        'refresh_token',
    ];
}
