<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class UserPermission extends Model
{
    use HasFactory;

    public $table = 'user_permissions';

    protected $fillable = [
        'requester_id', 'team_member_id', 'permission_type', 'status',
    ];

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id')->select('id','first_name',\DB::raw('COALESCE(last_name, "") as last_name'));
    }
}
