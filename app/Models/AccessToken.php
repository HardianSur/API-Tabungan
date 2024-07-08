<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;


class AccessToken extends Model
{
    use HasFactory,HasUuids ;
    public $incrementing = False;
    protected $table = 'access_tokens';
    protected $fillable = ['id','user_id', 'token', 'expired_at'];
}
