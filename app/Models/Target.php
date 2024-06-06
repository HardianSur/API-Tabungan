<?php

namespace App\Models;

use App\Models\Pay;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Target extends Model
{
    use HasFactory;

    protected $guarded= [
        'id'
    ];

    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn ($image) => url('/storage/targets/' . $image),
        );
    }

    public function pays(): HasMany
    {
        return $this->hasMany(Pay::class);
    }

}
