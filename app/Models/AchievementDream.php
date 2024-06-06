<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AchievementDream extends Model
{
    use HasFactory;

    public $table = 'achievement_dreams';

    public function achievementTitle()
    {
        return $this->belongsTo(BaseSettings::class, 'achievement_id', 'id')->select('id','value');
    }
}
