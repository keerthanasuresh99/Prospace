<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AchievementList;
use App\Models\AchievementTarget;
use App\Models\AchievementTracker;
use App\Models\BaseSettings;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AchievementTrackerController extends Controller
{
    public function achievementTitle()
    {
        try {

            $list = AchievementList::select('id', 'title')
                ->whereNotIn('title', ['GROUP BUSINESS VOLUME'])
                ->get();

            $tracker_list = AchievementList::select('id', 'title')
                ->where('is_tracker_list', 1)
                ->get();

            $data = [
                'list' => $list,
                'tracker_list' => $tracker_list
            ];

            if (!$list->isEmpty() || !$tracker_list->isEmpty()) {
                return $this->simpleReturn('success', $data);
            }


            return $this->simpleReturn('error', 'No Records found', 404);
        } catch (\Throwable $th) {
            return $this->simpleReturn('error', $th->getMessage(), 404);
        }
    }

    public function addAchievements(Request $request)
    {

        try {

            $rules = array(
                'title_id' => 'required',
                'user_id' => 'required',
                'date' => 'required'
            );

            $validator = Validator::make($request->all(), $rules);


            if ($validator->fails()) {
                return $this->simpleReturn('error', $validator->errors(), 400);
            }

            if ($request->edit == "true") {
                $achievementTracker = AchievementTracker::where('id', $request->id)->first();

                if ($achievementTracker) {

                    $achievementTracker->user_id = $request->user_id;
                    $achievementTracker->bill_amount = ($request->bill_amount) ? $request->bill_amount : null;
                    $achievementTracker->bv = ($request->bv) ? $request->bv : null;
                    $achievementTracker->pv = ($request->pv) ? $request->pv : null;
                    $achievementTracker->group_bv = ($request->group_bv) ? $request->group_bv : null;
                    $achievementTracker->group_pv = ($request->group_pv) ? $request->group_pv : null;
                    $achievementTracker->date = ($request->date) ? Carbon::createFromFormat('Y-m-d', $request->date)->format('Y-m-d') : null;
                    $achievementTracker->member_id  = ($request->member_id) ? $request->member_id  : ($request->new_joinee_phone ? $achievementTracker->member_id : null);;

                    $achievementTracker->save();

                    return $this->simpleReturn('success', 'Data updated successfully.', 200);
                }
            } else {


                if ($request->member_id) {
                    $new_joinee_exist = User::where('id', $request->member_id)->first();
                } else {
                    $new_joinee_exist = User::where('phone', $request->new_joinee_phone)->first();
                }

                if ($new_joinee_exist) {
                    $existingRecord = AchievementTracker::where('achievement_id', $request->title_id)
                        ->whereNotIn('achievement_id', [11, 13])->where('user_id', $request->user_id)
                        ->where('date', $request->date)
                        ->where(function ($query) use ($new_joinee_exist) {
                            $query->where('member_id', $new_joinee_exist->id)
                                ->orWhereNull('member_id');
                        })
                        ->first();

                    if ($existingRecord) {
                        return $this->simpleReturn('error', 'Data already added.', 400);
                    }
                }


                if ($request->new_joinee_phone && $request->title_id == 9) {

                    if ($new_joinee_exist && $request->title_id == 9) {

                        return $this->simpleReturn('error', 'User already exist.', 400);
                    }

                    $introducer_phone = User::where('id', $request->user_id)->first('phone');

                    $newuser = new User();
                    $newuser->first_name = $request->new_joinee_name;
                    $newuser->phone = $request->new_joinee_phone;
                    $newuser->introducer_id = $request->user_id;
                    $newuser->introducer_phone = ($introducer_phone->phone) ?? null;
                    $newuser->app_name = 'shospace_2';
                    $newuser->is_registered = 0;
                    $newuser->save();

                    $team_member = new TeamMember();
                    $team_member->user_id = $newuser->id;
                    $team_member->introducer_id  = $request->user_id;
                    $team_member->save();
                }

                $add_tracker = new AchievementTracker();
                $add_tracker->achievement_id  = $request->title_id;
                $add_tracker->user_id = $request->user_id;
                $add_tracker->member_id  = ($request->member_id) ? $request->member_id  : ($request->new_joinee_phone ? $newuser->id : null);
                $add_tracker->bill_amount = ($request->bill_amount) ? $request->bill_amount : null;
                $add_tracker->bv = ($request->bv) ? $request->bv : null;
                $add_tracker->pv = ($request->pv) ? $request->pv : null;
                $add_tracker->group_bv = ($request->group_bv) ? $request->group_bv : null;
                $add_tracker->group_pv = ($request->group_pv) ? $request->group_pv : null;
                $add_tracker->date = ($request->date) ? Carbon::createFromFormat('Y-m-d', $request->date)->format('Y-m-d') : null;

                if ($add_tracker->save()) {
                    return $this->simpleReturn('success', 'Data added sucessfully', 200);
                }
            }
        } catch (\Throwable $th) {
            return $this->simpleReturn('error', $th->getMessage(), 404);
        }
    }

    public function addachievementTarget(Request $request)
    {
        try {

            $rules = array(
                'achievement_id' => 'required|integer|exists:achievement_lists,id',
                'user_id' => 'required|integer|exists:users,id',
                'target_value' => 'required|integer'
            );

            $validator = Validator::make($request->all(), $rules);


            if ($validator->fails()) {
                return $this->simpleReturn('error', $validator->errors(), 400);
            }

            if ($request->date) {
                $currentMonth = $request->date;
            } else {
                $currentMonth = Carbon::now()->endOfMonth()->toDateString();
            }

            $achievementTarget = AchievementTarget::where('achievement_id', $request->input('achievement_id'))
                ->where('user_id', $request->input('user_id'))
                ->where('target_date', $currentMonth)
                ->first();


            if ($achievementTarget) {
                $achievementTarget->target_value = $request->input('target_value');
                $achievementTarget->save();
            } else {
                $newAchievementTarget = new AchievementTarget();
                $newAchievementTarget->achievement_id = $request->input('achievement_id');
                $newAchievementTarget->user_id = $request->input('user_id');
                $newAchievementTarget->target_date = $currentMonth;
                $newAchievementTarget->target_value = $request->input('target_value');
                $newAchievementTarget->save();
            }

            // if ($request->date) {

            //     $date = Carbon::createFromFormat('Y-m-d', $request->date);
            //     $year = $date->year;
            //     $month = $date->month;

            //     $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            //     $endDate = Carbon::create($year, $month, 1)->endOfMonth();

            //     $target = AchievementTarget::select(DB::raw('SUM(target_value) as total_target'))
            //         ->whereBetween('target_date', [$startDate, $endDate])
            //         ->where('achievement_id', $request->input('achievement_id'))
            //         ->where('user_id', $request->input('user_id'))
            //         ->first();

            //     $target_date = Carbon::now()->endOfMonth()->toDateString();

            //     AchievementTarget::whereYear('target_date', $year)
            //         ->where('target_date', $target_date)
            //         ->where('achievement_id', $request->input('achievement_id'))
            //         ->where('user_id', $request->input('user_id'))
            //         ->update(['target_value' => $target->total_target]);
            // }


            return $this->simpleReturn('success', 'Achievement target saved successfully', 200);
        } catch (\Throwable $th) {
            return $this->simpleReturn('error', $th->getMessage(), 404);
        }
    }

    public function getAchievementTracker(Request $request)
    {
        try {

            $userId = null;
            if ($request->token) {
                $user = User::where('token', $request->token)->firstOrFail(['id']);
                $userId = $user->id;
            } else if ($request->id) {
                $userId = $request->id;
            }

            $currentDate = Carbon::now()->toDateString();

            [$currentMonthStart, $currentMonthEnd, $previousMonthStart, $previousMonthEnd] = $this->getMonthDates();

            $list = AchievementList::select('id', 'title', 'target')
                ->get();

            $team_members = $this->getTeamMembers($userId);

            $achievement_list_data = [];

            foreach ($list as $value) {

                if ($value->id == 13) {
                    $user_current_month_count = $this->getAchievement($value->id, $userId, $currentMonthStart, $currentMonthEnd, $team_members, true);
                    $user_previous_month_count = $this->getAchievement($value->id, $userId, $previousMonthStart, $previousMonthEnd, $team_members, true);
                    $today_count = $this->getAchievement($value->id, $userId, $currentDate, $currentDate, $team_members, true);
                } else {
                    $user_current_month_count = $this->getAchievement($value->id, $userId, $currentMonthStart, $currentMonthEnd, $team_members);
                    $user_previous_month_count = $this->getAchievement($value->id, $userId, $previousMonthStart, $previousMonthEnd, $team_members);
                    $today_count = $this->getAchievement($value->id, $userId, $currentDate, $currentDate, $team_members);
                }

                $user_current_month_target = $this->getTarget($userId, $value->id,  Carbon::now()->endOfMonth()->toDateString());
                $team_current_month_target = $this->getTeamtarget($team_members, $userId, $value->id, Carbon::now()->endOfMonth()->toDateString());
                $current_month_bill_date = $this->getBilldate($userId, $value->id, $currentMonthStart, $currentMonthEnd);
                $previous_month_bill_date = $this->getBilldate($userId, $value->id, $previousMonthStart, $previousMonthEnd);


                $achievement_list_data[] = [
                    'achievement_name' => $value->title,
                    'achievement_id' => $value->id,
                    'target' => $user_current_month_target ?? 0,
                    'team_target' => $team_current_month_target ?? 0,
                    'achieved_today_count' => $today_count ?? 0,
                    'current_month_count' => $user_current_month_count ?? 0,
                    'previous_month_count' => $user_previous_month_count ?? 0,
                    'current_month_bill_date' => $current_month_bill_date,
                    'previous_month_bill_date' => $previous_month_bill_date
                ];
            }

            $tracker_list = AchievementList::select('id', 'title')
                ->where('is_tracker_list', 1)
                ->get();

            $data = [
                'list' => $achievement_list_data,
                'tracker_list' => $tracker_list
            ];

            if ($userId != null) {
                return $this->simpleReturn('success', $data);
            }

            return $this->simpleReturn('error', 'No Records found', 404);
        } catch (\Throwable $th) {
            return $this->simpleReturn('error', $th->getMessage(), 404);
        }
    }

    private function getMonthDates()
    {
        $currentMonthStart = Carbon::now()->startOfMonth();
        $currentMonthEnd = Carbon::now()->endOfMonth();
        $previousMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $previousMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        return [$currentMonthStart, $currentMonthEnd, $previousMonthStart, $previousMonthEnd];
    }

    private function getTeamMembers($userId)
    {
        return TeamMember::select('user_id')->where('introducer_id', $userId)->get();
    }

    private function getAchievement($list_id, $userId, $startDate, $endDate, $teamMembers, $tracker = false)
    {
        switch ($list_id) {
            case 1:  // FSP PLUS ACHIEVEMENT
                $totalCount = $this->getClubCount($teamMembers, $startDate, $endDate, 3, $userId);
                return floor($totalCount / 3);
                break;

            case 2: // TEAM FSP PLUS ACHIEVERS COUNT
                $subTeamMembers = $this->getTeamMembersRecursive($userId); // Fetch team members recursively
                $count = $this->getUniqueUserCount($startDate, $endDate, $userId, 3, $subTeamMembers, true);
                return $count;
                break;
            case 3:     // FAST START ACHIEVEMENT
                $totalCount = $this->getUserCount($startDate, $endDate, $userId, $list_id, $teamMembers);
                return $totalCount ?? 0;
                break;
            case 4:  //TEAM FSP ACHIEVERS COUNT
                $subTeamMembers = $this->getTeamMembersRecursive($userId); // Fetch team members recursively
                $tcount = $this->getUserCount($startDate, $endDate, $userId, 3, $subTeamMembers, true);
                return $tcount;
                break;
            case 5:   //SPONSORING CLUB LEADER
                $team_count = $this->getClubCount($teamMembers, $startDate, $endDate, 9, $userId);
                $club_count = floor($team_count / 3);
                return floor($club_count / 3);
                break;
            case 6:  //TEAM SPONSORING CLUB LEADERS COUNT
                $subTeamMembers = $this->getTeamMembersRecursive($userId); // Fetch team members recursively
                $team_count = $this->getUniqueUserCount($startDate, $endDate, $userId, 9, $subTeamMembers, true);
                $count =  floor($team_count / 3);
                return $count;
                break;
            case 7:  // Sponsoring club
                $totalCount = $this->getClubCount($teamMembers, $startDate, $endDate, 9, $userId);
                return floor($totalCount / 3);
                break;
            case 8:  //TEAM SPONSORING CLUB COUNT
                $subTeamMembers = $this->getTeamMembersRecursive($userId); // Fetch team members recursively
                $count = $this->getUniqueUserCount($startDate, $endDate, $userId, 9, $subTeamMembers, true);
                return $count;

                break;
            case 9:    //SPONSORING CHALLENGE
                $totalCount = $this->getUserCount($startDate, $endDate, $userId, $list_id, $teamMembers);
                return $totalCount ?? 0;
                break;
            case 10:  //TEAM SPONSORING COUNT
                $subTeamMembers = $this->getTeamMembersRecursive($userId); // Fetch team members recursively
                $tcount = $this->getUserCount($startDate, $endDate, $userId, 9, $subTeamMembers, true);
                return $tcount;
            case 11:  //LOYALTY CHALLENGE
                $subTeamMembers = $this->getTeamMembersRecursive($userId); // Fetch team members recursively
                return $this->getAchievementCountOrPV($list_id, $userId, $startDate, $endDate, $subTeamMembers);
                break;
            case 12:  //TEAM PBV COUNT
                $subTeamMembers = $this->getTeamMembersRecursive($userId);
                $totalCount = $this->getTeamAchievementCount($subTeamMembers, $startDate, $endDate, $userId);
                return $totalCount[11] ?? 0;
                break;
            case 13:  // GROUP BUSINESS VOLUME
                $subTeamMembers = $this->getTeamMembersRecursive($userId); // Fetch team members recursively
                if ($tracker) {
                    return $this->getAchievementPV($list_id, $userId, $startDate, $endDate, $subTeamMembers);
                } else {
                    return $this->getAchievementCountOrPV($list_id, $userId, $startDate, $endDate, $subTeamMembers);
                }
                break;
            case 14:  // GROUP BUSINESS VOLUME  - Team count
                $subTeamMembers = $this->getTeamMembersRecursive($userId);
                $totalCount = $this->getTeamAchievementCount($subTeamMembers, $startDate, $endDate, $userId);
                return $totalCount[13] ?? 0;
                break;
            default:
                return 0;
        }
    }

    private function getTeamMembersRecursive($userId)
    {
        $subTeamMembers = $this->getTeamMembers($userId)->toArray(); // Convert Collection to array
        $teamMembers = $subTeamMembers;

        foreach ($subTeamMembers as $subTeamMember) {
            $teamMembers = array_merge($teamMembers, $this->getTeamMembersRecursive($subTeamMember['user_id']));
        }


        return $teamMembers;
    }


    private function getAchievementPV($list_id, $userId, $startDate, $endDate, $teamMembers)
    {
        $totalCount = AchievementTracker::with('achievement')
            ->select('user_id', 'group_pv')
            ->where('user_id', $userId)
            ->where('achievement_id', $list_id)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc') //  order by ID to ensure the latest entry
            ->first();


        return  $totalCount == null ? 0 : $totalCount->group_pv;
    }



    private function getAchievementCountOrPV($list_id, $userId, $startDate, $endDate, $teamMembers)
    {
        $members = collect($teamMembers);
        $members->push($userId);

        $totalCount = AchievementTracker::with('achievement')
            ->select('user_id', DB::raw('COUNT(*) as count'), DB::raw('SUM(pv) as pv_sum'), DB::raw('SUM(group_pv) as gpv_sum'))
            ->where('user_id', $userId)
            ->where('achievement_id', $list_id)
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy('user_id')
            ->get();


        if ($list_id == 11) {
            return floor($totalCount->sum('pv_sum') * 10) / 10;
        } else if ($list_id == 13) {
            return floor($totalCount->sum('gpv_sum') * 10) / 10;
        }

        return 0;
    }

    private function getTarget($user_id, $achievement_id, $currentMonth)
    {
        $target = AchievementTarget::where('user_id', $user_id)
            ->where('achievement_id', $achievement_id)
            ->where('target_date', $currentMonth)
            ->value('target_value');

        return $target ?? 0;
    }



    public function getClubCount($teamMembers, $startDate, $endDate, $achievement_id, $user_id)
    {
        $totalCountQuery = AchievementTracker::with('achievement')
            ->select(DB::raw('COUNT(user_id) as user_count'))
            ->where('achievement_id', $achievement_id)
            ->whereBetween('date', [$startDate, $endDate]);

        if ($achievement_id == 3) {
            $totalCountQuery->where('member_id', $user_id);
        } else {
            $totalCountQuery->where('user_id', $user_id);
        }

        $totalCount = $totalCountQuery->get()->pluck('user_count')->sum();

        return $totalCount;
    }



    public function getTeamAchievementCount($teamMembers, $startDate, $endDate, $userId)
    {
        $totalCount = AchievementTracker::with('achievement')
            ->select('achievement_id', DB::raw('COUNT(  user_id) as team_count'))
            ->whereIn('user_id', $teamMembers)
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy('achievement_id')
            ->get()
            ->keyBy('achievement_id')
            ->map->team_count;

        return $totalCount;
    }

    public function getUserCount($startDate, $endDate, $userId, $list_id, $teamMembers, $isTeam = false)
    {

        $query = AchievementTracker::with('achievement')
            ->select('achievement_id', DB::raw('COUNT( user_id) as team_count'))
            ->where('achievement_id', $list_id)
            ->whereBetween('date', [$startDate, $endDate]);


        if ($isTeam) {
            if ($list_id == 3) {
                $query->whereIn('member_id', $teamMembers);
            } else {
                $query->whereIn('user_id', $teamMembers);
            }
        } else {
            if ($list_id == 3) {
                $query->where('member_id', $userId);
            } else {
                $query->where('user_id', $userId);
            }
        }

        $totalCount = $query->groupBy('achievement_id')->get();

        return $totalCount->isNotEmpty() ? $totalCount->first()->team_count : 0;
    }


    public function getUniqueUserCount($startDate, $endDate, $userId, $list_id, $teamMembers, $isTeam = false)
    {

        $query = AchievementTracker::with('achievement')
            ->select('achievement_id', DB::raw('COUNT(*) as team_count'));

        // Apply date and achievement_id filters
        $query->where('achievement_id', $list_id)
            ->whereBetween('date', [$startDate, $endDate]);

        // Apply conditional where clauses based on isTeam and list_id
        if ($isTeam) {
            if ($list_id == 3) {
                $query->whereIn('member_id', $teamMembers);
            } else {
                $query->whereIn('user_id', $teamMembers);
            }
        } else {
            if ($list_id == 3) {
                $query->where('member_id', $userId);
            } else {
                $query->where('user_id', $userId);
            }
        }

        // Apply conditional group by clause based on list_id
        if ($list_id == 3) {
            $query->groupBy('achievement_id', 'member_id');
        } else {
            $query->groupBy('achievement_id', 'user_id');
        }

        $totalCount = $query->get();

        $count = 0;
        foreach ($totalCount as $value) {
            $club_count = floor($value->team_count / 3);
            $club_count = min($club_count, 1);
            $count += $club_count;
        }

        return $count;
    }


    private function getTeamtarget($team_members, $userId, $achievement_id, $target_date)
    {

        $achievementMapping = [
            2 => 1,
            4 => 3,
            6 => 5,
            8 => 7,
            10 => 9,
            12 => 11
        ];

        $mappedAchievementId = $achievementMapping[$achievement_id] ?? $achievement_id;

        $target = AchievementTarget::select('achievement_id', DB::raw('SUM(target_value) as target_sum'))
            ->whereIn('user_id', $team_members->pluck('user_id'))
            ->where('achievement_id', $mappedAchievementId)
            ->where('target_date', $target_date)
            ->groupBy('achievement_id')
            ->first();


        return $target['target_sum'] ?? 0;
    }

    private function getBilldate($userId, $achievement_id, $startDate, $endDate)
    {
        $bill_date  = AchievementTracker::where('user_id', $userId)
            ->where('achievement_id', $achievement_id)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'asc')
            ->first('date');

        if ($bill_date) {
            $firstAchievementDate = $bill_date['date'];
        } else {
            $firstAchievementDate = null;
        }
        return $firstAchievementDate;
    }



    public function listTeamachievements(Request $request)
    {
        try {
            [$currentMonthStart, $currentMonthEnd, $previousMonthStart, $previousMonthEnd] = $this->getMonthDates();


            switch ($request->achievement_id) {
                case 10:
                case 8:
                case 7:
                case 5:
                case 6:
                    $achievement_id = 9;
                    break;
                case 12:
                    $achievement_id = 11;
                    break;
                case 1:
                case 2:
                case 4:
                    $achievement_id = 3;
                    break;
                default:
                    $achievement_id = $request->achievement_id;
                    break;
            }


            $members = $this->getTeamMembersRecursive($request->user_id);

            $teamMembers = AchievementTracker::where('achievement_id', $achievement_id);

            $members = collect($members);
            $members->push($request->user_id);

            if ($achievement_id == 3) {
                $teamMembers->whereIn('member_id', $members->toArray());
            } else {
                $teamMembers->whereIn('user_id', $members->toArray());
            }

            if ($request->from_date && $request->to_date) {

                $fromDate = Carbon::createFromFormat('Y-m-d', $request->from_date)->format('Y-m-d');
                $toDate = Carbon::createFromFormat('Y-m-d', $request->to_date)->format('Y-m-d');

                $teamMembers->whereBetween('date', [$fromDate, $toDate]);
            }

            $teamMembers = $teamMembers->get();


            if ($request->pv) {
                // Group team members by user_id and sum pv or group_pv
                if ($achievement_id == 3) {
                    $groupedMembers = $teamMembers->groupBy('member_id');
                } else {
                    $groupedMembers = $teamMembers->groupBy('user_id');
                }
                // Filter team members based on the sum of target value
                $teamMembers = $groupedMembers->filter(function ($members) use ($request) {
                    if ($request->achievement_id == 11) {
                        $sumTargetValue = $members->sum('pv');
                    } elseif ($request->achievement_id == 13) {
                        $sumTargetValue = $members->sum('group_pv');
                    } else {
                        return true; // No specific sum filtering for other achievement_ids
                    }

                    if ($request->pv == 60) {
                        return $sumTargetValue >= $request->pv && $sumTargetValue < 100;
                    } else {
                        return $sumTargetValue >= $request->pv;
                    }
                });

                // Flatten the collection back to a single level
                $teamMembers = $teamMembers->flatten();
            }

            if ($achievement_id == 3) {
                $distinctTeamMembers = $teamMembers->unique('member_id');
            } else {
                $distinctTeamMembers = $teamMembers->unique('user_id');
            }


            $achievement = AchievementList::where('id', $achievement_id)->first();

            $data = [];
            $club_count = 0;



            foreach ($distinctTeamMembers as $key => $value) {

                if ($achievement_id == 3) {
                    $user = User::where('id', $value['member_id'])->first();
                    $userId = $value['member_id'];
                } else {
                    $user = User::where('id', $value['user_id'])->first();
                    $userId = $value['user_id'];
                }

                $avatar = ($user->avatar) ? asset('http://tathastudd.com/public/storage/avatar/' . $user->avatar) : null;
                $team_members = $this->getTeamMembers($userId);
                $currentDate = Carbon::now()->toDateString();



                $achievementIdsMapping = [
                    1 => 2,
                    2 => 2,
                    3 => 4,
                    4 => 4,
                    5 => 6,
                    6 => 6,
                    7 => 8,
                    8 => 8,
                    9 => 10,
                    10 => 10,
                    11 => 12,
                    12 => 12,
                    13 => 14
                ];


                $team_count_current_month = $this->getAchievement($achievementIdsMapping[$request->achievement_id] ?? null, $userId, $currentMonthStart, $currentMonthEnd, $team_members);
                $team_count_prev_month = $this->getAchievement($achievementIdsMapping[$request->achievement_id] ?? null, $userId, $previousMonthStart, $previousMonthEnd, $team_members);
                $team_today_count = $this->getAchievement($achievementIdsMapping[$request->achievement_id] ?? null, $userId, $currentDate, $currentDate, $team_members);

                $current_month_bill_date = $this->getBilldate($userId, $achievement_id, $currentMonthStart, $currentMonthEnd);
                $previous_month_bill_date = $this->getBilldate($userId, $achievement_id, $previousMonthStart, $previousMonthEnd);
                $user_current_month_target = $this->getTarget($userId, $achievement_id, Carbon::now()->endOfMonth()->toDateString());
                $team_current_month_target = $this->getTeamtarget($team_members, $userId, $achievement_id, Carbon::now()->endOfMonth()->toDateString());


                // if (in_array($request->achievement_id, [13, 12, 11])) {

                //     $achievements = AchievementTracker::where('user_id', $userId)
                //         ->where('achievement_id', $achievement_id)
                //         ->with('member')
                //         ->whereBetween('date', [$currentMonthStart, $currentMonthEnd])
                //         ->get()
                //         ->each(function ($achievement) {
                //             $achievement->setRelation('user', $achievement->member);
                //         });
                // } else {

                //     $achievements = AchievementTracker::where('user_id', $userId)
                //         ->where('achievement_id', $achievement_id)
                //         ->whereBetween('date', [$currentMonthStart, $currentMonthEnd])
                //         ->with('user')->get();
                // }


                if (in_array($request->achievement_id, [13, 12, 11])) {
                    $achievements = AchievementTracker::where('user_id', $userId)
                        ->where('achievement_id', $achievement_id)
                        ->with('member');

                    if ($request->from_date && $request->to_date) {
                        $achievements = $achievements->whereBetween('date', [$request->from_date, $request->to_date]);
                    } else {
                        $achievements = $achievements->whereBetween('date', [$currentMonthStart, $currentMonthEnd]);
                    }

                    $achievements = $achievements->get()
                        ->each(function ($achievement) {
                            $achievement->setRelation('user', $achievement->member);
                        });
                } else {

                    if ($achievement_id == 3) {
                        $achievements = AchievementTracker::where('member_id', $userId)
                            ->where('achievement_id', $achievement_id);
                    } else {
                        $achievements = AchievementTracker::where('user_id', $userId)
                            ->where('achievement_id', $achievement_id);
                    }

                    if ($request->from_date && $request->to_date) {
                        $achievements = $achievements->whereBetween('date', [$request->from_date && $request->to_date]);
                    } else {
                        $achievements = $achievements->whereBetween('date', [$currentMonthStart, $currentMonthEnd]);
                    }

                    $achievements = $achievements->with('user')->get();
                }

                $current_month_count = $this->getAchievement($achievement_id, $userId, $currentMonthStart, $currentMonthEnd, $team_members);

                $club_count = ($request->achievement_id == 7 || $request->achievement_id == 8 || $request->achievement_id == 6 || $request->achievement_id == 5 || $request->achievement_id == 1 ||  $request->achievement_id == 2) ? intdiv($current_month_count, 3) : null;
                $club_leader_count = ($request->achievement_id == 5 || $request->achievement_id == 6) ? intdiv($club_count, 3) : null;


                // Condition to add the user's data to the array
                $shouldAdd = false;
                if ($request->achievement_id == 7 || $request->achievement_id == 8 || $request->achievement_id == 1 ||  $request->achievement_id == 2) {
                    $shouldAdd = $club_count > 0;
                } elseif ($request->achievement_id == 5 || $request->achievement_id == 6) {
                    $shouldAdd = $club_leader_count > 0;
                } else {
                    $shouldAdd = true;
                }


                if ($shouldAdd) {

                    $tracker_id_mapping = [
                        2 => 1,
                        4 => 3,
                        6 => 5,
                        8 => 7,
                        10 => 9,
                        12 => 11,
                    ];

                    $tracker_achievement_id = $request->achievement_id;

                    // Check if achievement_id exists in the mapping
                    if (array_key_exists($request->achievement_id, $tracker_id_mapping)) {
                        $tracker_achievement_id = $tracker_id_mapping[$request->achievement_id];
                    }


                    $data[] = [
                        'id' => $value['id'],
                        'achievement' => $achievement->title,
                        'user_id' => $userId,
                        'first_name' => $user->first_name,
                        'last_name' => ($user->last_name != null) ? ($user->last_name) : '',
                        'place' => $user->place,
                        'avatar' => $avatar ?? null,
                        'title' =>  $achievement->title,
                        // 'current_month_count' => $this->getAchievement($tracker_achievement_id, $userId, $currentMonthStart, $currentMonthEnd, $team_members),
                        'current_month_count' => $tracker_achievement_id == 13
                            ? $this->getAchievement($tracker_achievement_id, $userId, $currentMonthStart, $currentMonthEnd, $team_members, true)
                            : $this->getAchievement($tracker_achievement_id, $userId, $currentMonthStart, $currentMonthEnd, $team_members),

                        'previous_month_count' => $tracker_achievement_id == 13 ? $this->getAchievement($tracker_achievement_id, $userId, $previousMonthStart, $previousMonthEnd, $team_members) :
                            $this->getAchievement($tracker_achievement_id, $userId, $previousMonthStart, $previousMonthEnd, $team_members),

                        'team_count_current_month' => $team_count_current_month,
                        'team_count_previous_month' => $team_count_prev_month,
                        'today_count' => $tracker_achievement_id == 13 ? $this->getAchievement($tracker_achievement_id, $userId, $currentDate, $currentDate, $team_members) :
                            $this->getAchievement($tracker_achievement_id, $userId, $currentDate, $currentDate, $team_members),
                        'team_today_count' => $team_today_count,
                        'target' => $user_current_month_target,
                        'team_target' => $team_current_month_target,
                        'current_month_bill_date' => $current_month_bill_date,
                        'previous_month_bill_date' => $previous_month_bill_date,
                        'status' => ($user->userPermissions->status) ?? 0,
                        'achievements' => $achievements
                    ];
                }
            }

            if ($data) {
                return $this->simpleReturn('success', $data);
            }

            return $this->simpleReturn('error', 'No Records found', 404);
        } catch (\Throwable $th) {
            return $this->simpleReturn('error', $th->getMessage(), 404);
        }
    }



    public function getPreviousmonnthtracker(Request $request)
    {
        try {
            if ($request->token) {
                $user = User::where('token', $request->token)->firstOrFail(['id']);

                $list = AchievementList::select('id', 'title', 'target')
                    ->get();

                $team_members = $this->getTeamMembers($user->id);

                $data = [];
                $currentYear = Carbon::now()->year;
                $currentMonth = Carbon::now()->month;

                $perPage = 3; // Number of months per page
                $currentPage = $request->input('page', 1); // Get current page from request

                // Calculate total number of months
                $totalMonths = 0;
                for ($year = $currentYear; $year >= $currentYear - 1; $year--) {
                    $totalMonths += ($year == $currentYear ? $currentMonth - 2 : 12);
                }

                // Calculate total pages
                $totalPages = ceil($totalMonths / $perPage);

                // Calculate starting month for current page
                $startYear = $currentYear;
                $startMonth = $currentMonth - ($currentPage - 1) * $perPage;
                if ($startMonth <= 0) {
                    $startMonth += 12;
                    $startYear--;
                }

                // Loop through each year
                for ($year = $startYear; $year >= $startYear - 1; $year--) {
                    // Loop through each month
                    for ($month = ($year == $startYear ? $startMonth - 2 : 12); $month > 0; $month--) {
                        $start = Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
                        $end = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();

                        $achievementData = [];

                        foreach ($list as $value) {
                            $count = $this->getAchievement($value->id, $user->id, $start, $end, $team_members);
                            $bill_date = $this->getBilldate($user->id, $value->id, $start, $end);
                            $target = $this->getTarget($user->id, $value->id, Carbon::now());

                            $achievementData[] = [
                                'achievement_name' => $value->title,
                                'achievement_id' => $value->id,
                                'count' => $count,
                                'bill_date' => $bill_date,
                                'target' => $target
                            ];
                        }

                        $data[] = [
                            'month_year' => Carbon::create($year, $month, 1)->format('M Y'),
                            'achievements' => $achievementData
                        ];

                        // Break the loop if already processed 3 months for pagination
                        if (count($data) >= $perPage) {
                            break 2; // Break both inner and outer loop
                        }
                    }
                }

                $paginationData = [
                    'current_page' => $currentPage,
                    'last_page' => $totalPages,
                    'current_page_url' => $request->fullUrlWithQuery(['page' => $currentPage]),
                    'last_page_url' => $request->fullUrlWithQuery(['page' => $totalPages]),
                    'data' => $data,
                ];


                if (!$list->isEmpty() || $paginationData) {
                    return $this->simpleReturn('success', $paginationData);
                }
            }

            return $this->simpleReturn('error', 'No Records found', 404);
        } catch (\Throwable $th) {
            return $this->simpleReturn('error', $th->getMessage(), 404);
        }
    }


    public function getFiveInOneAchievementTracker(Request $request)
    {
        try {
            $user = User::where('token', $request->token)->firstOrFail(['id']);

            [$currentMonthStart, $currentMonthEnd, $previousMonthStart, $previousMonthEnd] = $this->getMonthDates();



            $list = AchievementList::select('id', 'title', 'target')
                ->whereIn('id', [1, 3, 5, 7, 9, 11, 13])
                ->get();

            $team_members = $this->getTeamMembers($user->id);

            $achievement_list_data = [];

            // Define the 5-day periods
            $periods = [
                [1, 5],
                [6, 10],
                [11, 15],
                [16, 20],
                [21, 25],
                [26, 31], // For the last period of current month
            ];

            foreach ($list as $value) {
                $period_data = [];
                foreach ($periods as $period) {
                    $start = 1;
                    $end = $period[1];

                    $fromDate = $period[0];

                    $cmonth_start = Carbon::parse($currentMonthStart)->addDays($fromDate - 1)->toDateString();
                    $cmonth_end = Carbon::parse($cmonth_start)->addDays($end - $fromDate)->toDateString();

                    $pmonth_start = Carbon::parse($previousMonthStart)->addDays($fromDate - 1)->toDateString();
                    $pmonth_end = Carbon::parse($pmonth_start)->addDays($end - $fromDate)->toDateString();

                    $currentAchievementCount = $this->getAchievement($value->id, $user->id, $cmonth_start, $cmonth_end, $team_members);
                    $previousAchievementCount = $this->getAchievement($value->id, $user->id, $pmonth_start, $pmonth_end, $team_members);


                    $current_month_start = Carbon::parse($currentMonthStart)->toDateString();
                    $current_month_end = Carbon::parse($current_month_start)->addDays($end - $start)->toDateString();


                    $prev_month_start = Carbon::parse($previousMonthStart)->toDateString();
                    $prev_month_end = Carbon::parse($prev_month_start)->addDays($end - $start)->toDateString();

                    if ($end > $currentMonthEnd->day) {
                        $current_month_end = $currentMonthEnd->toDateString();
                    }

                    if ($end > $previousMonthEnd->day) {
                        $prev_month_end = $previousMonthEnd->toDateString();
                    }

                    $user_current_month_count = ($currentAchievementCount > 0) ? $this->getAchievement($value->id, $user->id, $current_month_start, $current_month_end, $team_members) : 0;
                    $user_previous_month_count = ($previousAchievementCount > 0) ? $this->getAchievement($value->id, $user->id, $prev_month_start, $prev_month_end, $team_members) : 0;

                    $achievementIdsMapping = [
                        1 => 2,
                        3 => 4,
                        5 => 6,
                        7 => 8,
                        9 => 10,
                        11 => 12
                    ];


                    $team_current_month_count = $this->getAchievement($achievementIdsMapping[$value->id] ?? null, $user->id, $current_month_start, $current_month_end, $team_members);
                    $team_previous_month_count = $this->getAchievement($achievementIdsMapping[$value->id] ?? null, $user->id, $prev_month_start, $prev_month_end, $team_members);


                    $user_current_month_target = $this->getTargetForPeriod($value->id, $user->id, $currentMonthStart, $currentMonthEnd, $start, $end, $team_members);
                    $user_previous_month_target = $this->getTargetForPeriod($value->id, $user->id, $previousMonthStart, $previousMonthEnd, $start, $end, $team_members);

                    $team_current_month_target = $this->getTargetForPeriod($value->id, $user->id, $currentMonthStart, $currentMonthEnd, $start, $end, $team_members, true);
                    $team_previous_month_target = $this->getTargetForPeriod($value->id, $user->id, $previousMonthStart, $previousMonthEnd, $start, $end, $team_members, true);

                    $period_data[] = [
                        'start_day' => $start,
                        'end_day' => $end,
                        'current_month_start' => $current_month_start,
                        'current_month_end' => $current_month_end,
                        'prev_month_start' => $prev_month_start,
                        'prev_month_end' => $prev_month_end,
                        'user_current_month_count' => $user_current_month_count,
                        'user_previous_month_count' => $user_previous_month_count,
                        'team_current_month_count' => $team_current_month_count,
                        'team_previous_month_count' => $team_previous_month_count,
                        'user_current_month_target' => $user_current_month_target,
                        'user_previous_month_target' => $user_previous_month_target,
                        'team_current_month_target' => $team_current_month_target,
                        'team_previous_month_target' => $team_previous_month_target
                    ];
                }

                $achievement_list_data[] = [
                    'achievement_name' => $value->title,
                    'achievement_id' => $value->id,
                    'period_data' => $period_data
                ];
            }


            if ($achievement_list_data) {
                return $this->simpleReturn('success', $achievement_list_data);
            }

            return $this->simpleReturn('error', 'No Records found', 404);
        } catch (\Throwable $th) {
            return $this->simpleReturn('error', $th->getMessage(), 500);
        }
    }


    private function getTargetForPeriod($achievementId, $userId, $startDate, $endDate, $startDay, $endDay, $teamMembers, $forTeam = false)
    {
        $startDate = Carbon::parse($startDate)->addDays($startDay - 1)->toDateString();
        $endDate = Carbon::parse($startDate)->addDays($endDay - $startDay)->toDateString();

        $members = $this->getTeamMembersRecursive($userId);


        if ($forTeam) {
            $target = AchievementTarget::select(DB::raw('SUM(target_value) as total_target'))
                ->where('target_date',  $endDate)
                ->where('achievement_id', $achievementId)
                ->whereIn('user_id', $members)
                ->first();

            return intval($target->total_target);
        } else {
            $target = AchievementTarget::select(DB::raw('SUM(target_value) as total_target'))
                ->where('target_date',  $endDate)
                ->where('achievement_id', $achievementId)
                ->where('user_id', $userId)
                ->first();

            return intval($target->total_target);
        }
    }

    public function getAchieversList()
    {
        $list = BaseSettings::select('id', 'type', 'value', 'created_at')->where('type', 'achiever')->where('is_deleted', 0)
            ->get();

        if ($list->count()) {

            return $this->simpleReturn('success', $list);
        }
        return $this->simpleReturn('error', 'No Records found', 404);
    }

    public function editAchievements(Request $request)
    {
        $data = AchievementTracker::select('id', 'achievement_id', 'user_id as introducer_id', 'member_id', 'bill_amount', 'bv', 'pv', 'group_bv', 'group_pv', 'date')
            ->with('user', 'achievement')
            ->where('id', $request->id)->first();


        if ($data) {

            return $this->simpleReturn('success', $data);
        }
        return $this->simpleReturn('error', 'No Records found', 404);
    }



    public function deleteAchievements(Request $request)
    {
        $achievementTracker = AchievementTracker::where('id', $request->id)->first();

        if ($achievementTracker) {

            $member_id = $achievementTracker->member_id;
            $title_id = $achievementTracker->achievement_id;


            $achievementTracker->delete();

            if ($title_id  == 9) {   // Sponsoring chalenge - delete new joinee data from team member and user table
                $teamMemberExists = TeamMember::where('user_id', $member_id)->first();

                if ($teamMemberExists) {

                    $teamMemberExists->delete();

                    $userExists = User::where('id', $member_id)->first();

                    if ($userExists) {
                        $userExists->delete();
                    }
                }
            }

            return $this->simpleReturn('success', 'Data deleted successfully', 200);
        }
        return $this->simpleReturn('error', 'No Records found', 404);
    }
}
