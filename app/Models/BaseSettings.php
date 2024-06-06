<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BaseSettings extends Model
{
    use HasFactory;

    public $table = 'base_settings';

    public function  sub_achievers() {
        return $this->hasMany(SubAchieverList::class, 'achiever_id', 'id')->select('id','achiever_id','value')->where('is_deleted',0);
    }

    public function  templates() {
        return $this->hasOne(Template::class, 'achiever_id', 'id')
        ->select('achiever_id','image','image_position','colour_for_name','colour_for_date','created_at')
        ->whereNull('sub_achiever_id')->where('is_deleted',0);
    }
}
