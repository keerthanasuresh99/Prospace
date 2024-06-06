<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    use HasFactory;

    public $table = 'templates';

    public function  achievers() {
        return $this->belongsto(BaseSettings::class, 'achiever_id', 'id')->select('id', 'type', 'value')->where('is_deleted',0);
    }

    public function  subAchievers() {
        return $this->belongsto(SubAchieverList::class, 'sub_achiever_id', 'id')->select('id', 'value')->where('is_deleted',0);
    }
}
