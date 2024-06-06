<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubAchieverList extends Model
{
    use HasFactory;

    public $table = 'sub_achiever_lists';

    public function  achievers() {
        return $this->belongsto(BaseSettings::class, 'achiever_id', 'id')->select('id', 'type', 'value','created_at')->where('is_deleted',0);
    }

    public function  templates() {
        return $this->hasOne(Template::class, 'sub_achiever_id', 'id')
        ->select('sub_achiever_id','image','image_position','colour_for_name','colour_for_date','created_at')->where('is_deleted',0);
    }
}
