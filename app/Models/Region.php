<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Region extends Model
{
    protected $fillable = ['name'];

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
