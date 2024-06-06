<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TeamMembersController extends Controller
{
    public function getIntoducers(Request $request)
    {
        try {

            $name = $request->input('name');

            if ($request->token || $request->id) {

                if ($request->token) {
                    $user = User::select('id', 'first_name', 'last_name')->where('app_name', 'shospace_2')
                        ->where('token', $request->token)->first();
                } else if ($request->id) {
                    $user = User::select('id', 'first_name', 'last_name')->where('app_name', 'shospace_2')
                        ->where('id', $request->id)->first();
                }


                $response = [];

                if ($user) {
                    $userMembers = [
                        'id' => $user->id,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name ?? '',
                    ];

                    $response[] = $userMembers;


                    $teamMembers = $this->fetchTeamMembers($user->id, $name);

                    foreach ($teamMembers as $member) {
                        $response[] = $member;
                    }

                    if ($teamMembers) {
                        return $this->simpleReturn('success', $response);
                    } else if ($request->token || $request->id) {
                        if ($request->token) {
                            $user = User::select('id', 'first_name', 'last_name')->where('app_name', 'shospace_2')
                                ->where('token', $request->token)->get();
                        } else if ($request->id) {
                            $user = User::select('id', 'first_name', 'last_name')->where('app_name', 'shospace_2')
                                ->where('id', $request->id)->get();
                        }

                        return $this->simpleReturn('success', $user);
                    }
                }
            } else {
                $users = User::select('id', 'first_name', 'last_name')
                    ->where('app_name', 'shospace_2')
                    ->where('first_name', 'like',  $name . '%')
                    //->where('is_registered', 1)
                    ->where('app_name', 'shospace_2')
                    ->get();

                return $this->simpleReturn('success', $users);
            }

            return $this->simpleReturn('error', 'No Records found', 404);
        } catch (\Throwable $th) {

            return $this->simpleReturn('error', $th->getMessage(), 404);
        }
    }

    public function fetchTeamMembers($introducerId, $name)
    {
        $team_members = TeamMember::select('users.id as id', 'users.first_name', 'users.last_name')
            ->where('team_members.introducer_id', $introducerId)
            ->rightjoin('users', 'team_members.user_id', '=', 'users.id')
            ->get();

        $members = [];

        foreach ($team_members as $team_member) {
            $members[] = [
                'id' => $team_member->id,
                'first_name' => $team_member->first_name,
                'last_name' => $team_member->last_name ?? '',
            ];

            $members = array_merge($members, $this->fetchTeamMembers($team_member->id, $name));
        }

        return $members;
    }

    public function addTeammember(Request $request)
    {
        $rules = array(
            'introducer_id' => 'required',
            'first_name' => 'required',
            'title' => 'required',
            'place' => 'required',
            'phone' => 'required'
        );

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->simpleReturn('error', $validator->errors(), 400);
        }


        $exist = TeamMember::join('users', 'team_members.user_id', '=', 'users.id')
            ->where('users.phone', $request->phone)
            ->first();


        if ($exist) {
            return $this->simpleReturn('error', 'Phone number already exist', 404);
        } else {

            $userExist = User::where('phone', $request->phone)->where('app_name', 'shospace_2')->first();
            if ($userExist == null) {
                $introducer_phone = User::select('phone')->where('id', $request->introducer_id)->where('app_name', 'shospace_2')->first();

                $userreg = new User();
                $userreg->first_name = $request->first_name;
                $userreg->last_name = $request->last_name;
                $userreg->introducer_id  = $request->introducer_id;
                $userreg->introducer_phone  = ($introducer_phone->phone) ? $introducer_phone->phone : null;
                $userreg->place = $request->place;
                $userreg->phone = $request->phone;
                $userreg->app_name = 'shospace_2';
                $userreg->save();
                $newUserId = $userreg->id;
            } else {
                $newUserId = $userExist->id;
            }

            $team_member = new TeamMember();
            $team_member->title  = $request->title;
            $team_member->user_id = $newUserId;
            $team_member->introducer_id  = $request->introducer_id;

            if ($team_member->save()) {
                return $this->simpleReturn('success', 'Team Member added successfully.', 200);
            }
        }

        return $this->simpleReturn('error', 'Something went wrong', 400);
    }

    public function listTeammembers(Request $request)
    {
        try {

            $introducer_id = null;
            if ($request->token) {
                $user = User::where('token', $request->token)->select('id')->first();
                $introducer_id = $user->id;
            } else  if ($request->id) {
                $introducer_id = $request->id;
            }

            $teamMembers = TeamMember::with(['user' => function ($query) {
                $query->select('id', 'first_name', 'last_name', 'phone', 'place', 'is_registered', 'avatar');
            }])
                ->select(
                    'team_members.id',
                    'team_members.user_id',
                    'team_members.introducer_id',
                    'team_members.title'
                )
                ->where('team_members.introducer_id', $introducer_id)
                ->get();

            if ($teamMembers->count()) {
                $teamMembers->transform(function ($item, $key) {
                    if (isset($item->user->avatar)) {
                        $item->user->url = asset('http://tathastudd.com/public/storage/avatar/' . $item->user->avatar);
                    }
                    return $item;
                });
            }

            $result = [];

            foreach ($teamMembers as $teamMember) {
                $userData = $teamMember->user; // Access the associated User object
                $result[] = [
                    'id' => $teamMember->id,
                    'user_id' => $teamMember->user_id,
                    'introducer_id' => $teamMember->introducer_id,
                    'first_name' => $userData->first_name,
                    'last_name' => $userData->last_name,
                    'phone' => $userData->phone,
                    'place' => $userData->place,
                    'is_registered' => $userData->is_registered,
                    'avatar' => $userData->avatar,
                    'url' => $userData->url,
                    'title' => $teamMember->title
                ];
            }

            if ($result) {
                return $this->simpleReturn('success', $result);
            }

            return $this->simpleReturn('error', 'No Records found', 404);
        } catch (\Throwable $th) {
            return $this->simpleReturn('error', $th->getMessage(), 404);
        }
    }


    public function getUserByPhoneNumber(Request $request)
    {
        try {
            $user = User::select('id', 'first_name', 'last_name', 'place', 'phone', 'introducer_id', 'introducer_phone')
                ->where('phone', $request->phone)->where('app_name', 'shospace_2')
                ->get();

            if ($user->isEmpty()) {
                return $this->simpleReturn('error', 'No Records found', 404);
            }

            return $this->simpleReturn('success', $user);
        } catch (\Throwable $th) {
            return $this->simpleReturn('error', $th->getMessage(), 404);
        }
    }

    public function getIntroducer(Request $request)
    {
        try {

            if ($request->phone) {
                $user = User::select('id', 'first_name', 'last_name')
                    ->where('phone', $request->phone)->where('app_name', 'shospace_2')
                    ->get();

                return $this->simpleReturn('success', $user);
            } else if ($request->id) {
                $user = User::select('phone', 'first_name', 'last_name')
                    ->where('id', $request->id)->where('app_name', 'shospace_2')
                    ->get();

                return $this->simpleReturn('success', $user);
            }

            return $this->simpleReturn('error', 'No Records found', 404);
        } catch (\Throwable $th) {
            return $this->simpleReturn('error', $th->getMessage(), 404);
        }
    }

    public function editTeamMember(Request $request)
    {

        $teamMembers = TeamMember::select('user_id', 'title')
            ->where('user_id', $request->id)
            ->with(['user' => function ($query) {
                $query->select('id', 'first_name', 'last_name', 'phone', 'place', 'introducer_id');
            }])
            ->get();

        // Modify each item in the collection to merge 'title' with 'user' fields
        $teamMembers->transform(function ($item) {
            $userData = $item->user->toArray(); // Convert user relationship to array
            $userData['title'] = $item->title; // Merge 'title' with 'user' fields
            unset($item->user); // Remove the 'user' relationship
            return $userData; // Return the merged array
        });

        return $this->simpleReturn('success', $teamMembers);
    }

    public function updateTeamMember(Request $request)
    {


        $data = User::where('id', $request->user_id)->first();

        $result = User::where('id', $request->user_id)
            ->update([
                'phone' => $request->phone,
                'first_name' => ($request->first_name) ? $request->first_name : $data->first_name,
                'last_name' => ($request->last_name) ? $request->last_name :  $data->last_name,
                'place' => ($request->place) ? $request->place : $data->place,
                'introducer_id' => ($request->introducer) ? ($request->introducer) : $data->introducer_id
            ]);

        $user = TeamMember::where('user_id', $request->user_id)
            ->update([
                'title' => $request->title,
                'introducer_id' => ($request->introducer) ? ($request->introducer) : $data->introducer_id

            ]);

        if ($request->introducer) {
            $introducer = User::where('id', $request->introducer)->first();

            User::where('id', $request->user_id)
                ->update([
                    'introducer_phone' => $introducer->phone
                ]);
        }

        if ($result) {
            // The update was successful
            return $this->simpleReturn('success', 'User details updated successfully.');
        } else {
            // Handle the case where the update failed
            return $this->simpleReturn('error', 'Failed to update user details.');
        }
    }

    public function deleteTeammember(Request $request)
    {

        $team_member = TeamMember::where('user_id', $request->id)->first();

        $teams = TeamMember::where('introducer_id',$request->id)->get();

        if($teams->count() > 0){
            return $this->simpleReturn('error', 'Team member cannot be deleted.',404);
        }
        

        if ($team_member) {

            User::where('id', $request->id)->update([
                'introducer_id' => null,
                'introducer_phone' => null
            ]);

            
               $team_member->delete();

            return $this->simpleReturn('success', 'Team member deleted successfully.');
        } else {
            return $this->simpleReturn('error', 'Team member not found.');
        }
    }
}
