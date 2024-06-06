<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class EventsController extends Controller
{
    public function listEventbuilders()
    {
        $list = User::whereIn('event_builder',[1,2])
        ->where('app_name','shospace_2')->latest()->get();
       
        return view('admin.events.listEventBuilders', [
            'list' => $list,
        ]);  
    }

    public function approveEventbuilders($user)
    {


        User::where('id', $user)->update(['event_builder' => 1]);

        return redirect()->route('list-event-builders')->with('status', 'User approved successfully!');
    }

    public function rejectEventbuilders($user)
    {

        User::where('id', $user)->update(['event_builder' => 0]);

        return redirect()->route('list-event-builders')->with('status', 'User rejected successfully!');
    }
}
