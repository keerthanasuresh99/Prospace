<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamMember extends Model
{
    use HasFactory;

    public $table = 'team_members';

    protected $fillable = ['first_name','last_name','title'];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function achievementTracker()
    {
        return $this->hasMany(AchievementTracker::class, 'member_id', 'user_id');
    }

    public function achievementTitle()
    {
        return $this->belongsTo(BaseSettings::class, 'title','id');
    }

    public function userPermissions()
    {
        return $this->belongsTo(UserPermission::class, 'user_id','team_member_id');
    }

    public function tracker()
    {
        return $this->hasMany(AchievementTracker::class, 'user_id', 'user_id');
    }
}
