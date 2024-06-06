<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AchievementTracker extends Model
{
    use HasFactory;

    public $table = 'achievement_tracker';

    public function achievement()
    {
        return $this->belongsTo(AchievementList::class, 'achievement_id', 'id')->select('id', 'title', 'target', 'month');
    }

    public function teamMember()
    {
        return $this->belongsTo(TeamMember::class, 'user_id', 'introducer_id');
    }

    public function user()
    {
        return $this->hasMany(User::class, 'id', 'member_id')->select('id', 'first_name', 'last_name', 'phone');
    }

    public function member()
    {
        return $this->hasMany(User::class, 'id', 'user_id')->select('id', 'first_name', 'last_name', 'phone');
    }
}
