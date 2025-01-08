<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Parental\HasChildren;

class EventUserRelation extends Model
{
    use HasChildren, HasFactory;

    protected $fillable = ['type', 'user_id', 'event_id'];

    protected $childTypes = [
        'attendance' => EventUserAttendance::class,
        'invitation' => EventUserInvitation::class,
        'request' => EventUserRequest::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
