<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AchievementList extends Model
{
    use HasFactory;

    public $table = 'achievement_lists';

    public function achievementTrackers()
    {
        return $this->hasMany(AchievementTracker::class, 'achievement_id', 'id');
    }

}
