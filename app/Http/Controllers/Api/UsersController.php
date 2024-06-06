<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BaseSettings;
use App\Models\SubAchieverList;
use App\Models\TeamMember;
use App\Models\Template;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;



class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {


        if ($request->token) {
            $user = User::where('token', $request->token)->get();
            if ($user->count()) {
                $user->transform(function ($item, $key) {
                    if (isset($item->avatar)) {
                        $item->url = asset('http://tathastudd.com/public/storage/avatar/' . $item->avatar);
                    }
                    return $item;
                });
                return $this->simpleReturn('success', $user);
            }
            return $this->simpleReturn('error', 'No Records found', 404);
        } else {
            $rules = array(
                'first_name' => 'required',
                'last_name' => 'required',
                'image' => 'required',
                'place' => 'required',
            );

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return $this->simpleReturn('error', $validator->errors(), 400);
            }

            $userExist = User::where('first_name', $request->first_name)
                ->where('last_name', $request->last_name)
                ->where('place', $request->place)->whereNull('app_name')->first();


            if ($userExist) {
                $fileName = $userExist->avatar;
                if (Storage::disk('avatar')->exists($fileName)) {
                    // Delete the file
                    Storage::disk('avatar')->delete($fileName);
                }

                $userExist->delete();
            }


            if ($request->image) {
                $extension = $request->file('image')->extension();
                $image = time() . mt_rand(100, 999) . '.' . $extension;
                Storage::disk('avatar')->putFileAs('', $request->file('image'), $image);
            }

            $userreg = new User();
            $userreg->first_name = $request->first_name;
            $userreg->last_name = $request->last_name;
            $userreg->place = $request->place;
            $token = Str::uuid()->toString();
            $userreg->token = $token;
            $userreg->avatar = $image ?? null;

            if ($userreg->save()) {
                $sucess['response'] = 'User Added';
                $success['token'] = $token;
                return $this->simpleReturn('success', $success);
            }
        }

        return $this->simpleReturn('error', 'Something went wrong', 400);
    }



    public function getAchieversList()
    {
        $list = BaseSettings::select('id', 'type', 'value', 'created_at')->where('type', 'achiever')->where('is_deleted', 0)
            ->with('templates', 'sub_achievers')->withCount('sub_achievers')->latest()->get();

        if ($list->count()) {
            $list->transform(function ($item, $key) {
                if (isset($item->templates->image)) {
                    $item->templates->url = asset('http://tathastudd.com/public/storage/templates/' . $item->templates->image);
                }
                return $item;
            });
            return $this->simpleReturn('success', $list);
        }
        return $this->simpleReturn('error', 'No Records found', 404);
    }

    public function getSubachieversList(Request $request)
    {
        $list = SubAchieverList::where('achiever_id', $request->achiever_id)->where('is_deleted', 0)
            ->with('achievers', 'templates')->get();

        if ($list->count()) {
            $list->transform(function ($item, $key) {
                if (isset($item->templates->image)) {
                    $item->templates->url = asset('http://tathastudd.com/public/storage/templates/' . $item->templates->image);
                }
                return $item;
            });
            return $this->simpleReturn('success', $list);
        }
        return $this->simpleReturn('error', 'No Records found', 404);
    }

    public function getUser(Request $request)
    {
        $user = User::where('token', $request->token)->get();

        if ($user->count()) {
            return $this->simpleReturn('success', $user);
        }
        return $this->simpleReturn('error', 'No Records found', 404);
    }


    public function addUser(Request $request)
    {

        if ($request->token) {
            $user = User::where('token', $request->token)->where('app_name', 'shospace_2')->get();
            if ($user->count()) {
                $user->transform(function ($item, $key) {
                    if (isset($item->avatar)) {
                        $item->url = asset('http://tathastudd.com/public/storage/avatar/' . $item->avatar);
                    }
                    return $item;
                });
                return $this->simpleReturn('success', $user);
            }
            return $this->simpleReturn('error', 'No Records found', 404);
        } else {
            try {
                if ($request->app_name) {
                    $rules = array(
                        'first_name' => 'required',
                        'image' => 'required',
                        'place' => 'required',
                        'phone' => 'required',
                    );
                }

                $validator = Validator::make($request->all(), $rules);

                if ($validator->fails()) {
                    return $this->simpleReturn('error', $validator->errors(), 400);
                }


                $userExist = User::where('phone', $request->phone)->where('app_name', 'shospace_2')->first();
                $token = Str::uuid()->toString();

                if ($request->image) {
                    $extension = $request->file('image')->extension();
                    $image = time() . mt_rand(100, 999) . '.' . $extension;
                    Storage::disk('avatar')->putFileAs('', $request->file('image'), $image);
                }

                if ($userExist) {

                    if ($request->introducer_name) {
                        $newuser = new User();
                        $newuser->first_name = $request->introducer_name;
                        $newuser->last_name = '';
                        $newuser->phone = $request->introducer_phone;
                        $newuser->app_name = 'shospace_2';
                        $newuser->is_registered = 0;
                        $newuser->save();
                    }

                    $fileName = $userExist->avatar;

                    if ($fileName && Storage::disk('avatar')->exists($fileName)) {
                        // Delete the file
                        Storage::disk('avatar')->delete($fileName);
                    }



                    User::where('id', $userExist->id)->update([
                        'first_name' => ($request->first_name) ?  $request->first_name : null,
                        'last_name' => ($request->last_name) ?  $request->last_name : null,
                        'place' => $request->place,
                        'avatar' => $image,
                        'token' => ($userExist->token) ? $userExist->token : $token,
                        'introducer_id' => $request->introducer_id ? $request->introducer_id : ($request->introducer_name ? $newuser->id : null),
                        'introducer_phone' => ($request->introducer_phone) ? $request->introducer_phone : null,
                    ]);

                    $sucess['response'] = 'User Added';
                    $success['token'] = ($userExist->token) ? $userExist->token : $token;
                    return $this->simpleReturn('success', $success);
                } else {
                    //New user
                    if ($request->introducer_name) {
                        $newuser = new User();
                        $newuser->first_name = $request->introducer_name;
                        $newuser->last_name = '';
                        $newuser->phone = $request->introducer_phone;
                        $newuser->app_name = 'shospace_2';
                        $newuser->is_registered = 0;
                        $newuser->save();
                    }

                    $userreg = new User();
                    $userreg->first_name = $request->first_name;
                    $userreg->last_name = $request->last_name;
                    $userreg->place = $request->place;
                    $userreg->phone = $request->phone;
                    $userreg->introducer_id = $request->introducer_id ? $request->introducer_id : ($request->introducer_name ? $newuser->id : null);
                    $userreg->introducer_phone = ($request->introducer_phone) ? $request->introducer_phone : null;
                    $userreg->event_builder = 2;
                    $userreg->app_name = ($request->app_name) ? $request->app_name : null;
                    $userreg->is_registered = ($request->app_name) ? 1 : 0;
                    $userreg->token = $token;
                    $userreg->avatar = $image;

                    if ($userreg->save()) {

                        $team_member = new TeamMember();
                        $team_member->user_id = $userreg->id;
                        $team_member->introducer_id  = $request->introducer_id ? $request->introducer_id : ($request->introducer_name ? $newuser->id : null);
                        $team_member->save();

                        $sucess['response'] = 'User Added';
                        $success['token'] = $token;
                        return $this->simpleReturn('success', $success);
                    }
                }

                return $this->simpleReturn('error', 'Something went wrong', 400);
            } catch (\Throwable $th) {
                return $this->simpleReturn('error', $th->getMessage(), 500);
            }
        }
    }
}
