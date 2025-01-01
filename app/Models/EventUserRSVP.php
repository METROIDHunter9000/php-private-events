<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Parental\HasParent;

class EventUserRSVP extends EventUserRelation
{
    use HasParent;
    public function impersonate($user) {
        // What is this for? TODO
    }
}
