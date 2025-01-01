<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'body',
        'date',
        'is_private',
    ];

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, "organizer_id");
    }

    public function event_user_relations(): HasMany
    {
        return $this->hasMany(EventUserRelation::class);
    }
}
