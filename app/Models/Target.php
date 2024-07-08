<?php

namespace App\Models;

use App\Models\Pay;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Target extends Model
{
    use HasFactory, HasUuids;

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
        return $this->hasMany(Pay::class,'target_id','id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

}
