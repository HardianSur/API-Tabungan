<?php

namespace App\Models;

use App\Models\Target;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pay extends Model
{
    use HasFactory;


    protected $guarded=['id'];

    public function targets(): HasOne
    {
        return $this->hasOne(Target::class);
    }
}
