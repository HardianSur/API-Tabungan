<?php

namespace App\Models;

use App\Models\Target;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;


class Pay extends Model
{
    use HasFactory, HasUuids;


    protected $guarded=['id'];

    public function targets(): BelongsTo
    {
        return $this->belongsTo(Target::class);
    }
}
