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

            $new_joinee_exist = User::where('phone', $request->new_joinee_phone)->first();

            if ($new_joinee_exist) {
                $existingRecord = AchievementTracker::where('achievement_id', $request->title_id)
                    ->where('user_id', $request->user_id)
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

            if ($request->new_joinee_phone) {

                if ($new_joinee_exist) {
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
                $user_current_month_count = $this->getAchievement($value->id, $userId, $currentMonthStart, $currentMonthEnd, $team_members);
                $user_previous_month_count = $this->getAchievement($value->id, $userId, $previousMonthStart, $previousMonthEnd, $team_members);
                $today_count = $this->getAchievement($value->id, $userId, $currentDate, $currentDate, $team_members);
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

    private function getAchievement($list_id, $userId, $startDate, $endDate, $teamMembers)
    {
        switch ($list_id) {
            case 1:  // FSP PLUS ACHIEVEMENT
                $totalCount = $this->getClubCount($teamMembers, $startDate, $endDate, 3);
                return floor($totalCount / 3);
                break;
            case 2: // TEAM FSP PLUS ACHIEVERS COUNT
                $team_count = collect($teamMembers)->sum(function ($member) use ($startDate, $endDate, $userId) {
                    $subTeamMembers = $this->getTeamMembers($member['user_id']);
                    $totalCount = $this->getTeamCount($subTeamMembers, $userId, $startDate, $endDate, 3);
                    return $totalCount;
                });
                return floor($team_count / 3);
                break;
            case 3:     // FAST START ACHIEVEMENT
                $totalCount = $this->getClubCount($teamMembers, $startDate, $endDate, 3);
                return $totalCount ?? 0;
                break;
            case 9:    //SPONSORING CHALLENGE
                $totalCount = $this->getSponsoringCount($teamMembers, $startDate, $endDate, $userId);
                return $totalCount[9] ?? 0;
                break;
            case 13:  // GROUP BUSINESS VOLUME
                return $this->getAchievementCountOrPV($list_id, $userId, $startDate, $endDate, 'count');
                break;
            case 11:  //LOYALTY CHALLENGE
                return $this->getAchievementCountOrPV($list_id, $userId, $startDate, $endDate, 'pv_sum');
                break;
            case 4:  //TEAM FSP ACHIEVERS COUNT
                $team_count = collect($teamMembers)->sum(function ($member) use ($startDate, $endDate, $userId) {
                    $subTeamMembers = $this->getTeamMembers($member['user_id']);
                    $totalCount = $this->getTeamCount($subTeamMembers, $userId, $startDate, $endDate, 3);
                    return $totalCount;
                });
                return $team_count;
                break;
            case 10:  //TEAM SPONSORING COUNT
                $team_count = collect($teamMembers)->sum(function ($member) use ($startDate, $endDate, $userId) {
                    $subTeamMembers = $this->getTeamMembers($member['user_id']);
                    $totalCount = $this->getTeamCount($subTeamMembers, $userId, $startDate, $endDate, 9);
                    return $totalCount;
                });
                return $team_count;

            case 12:  //TEAM PBV COUNT
                $totalCount = $this->getTeamAchievementCount($teamMembers, $startDate, $endDate, $userId);
                return $totalCount[11] ?? 0;
                break;
            case 5:   //SPONSORING CLUB LEADER
                $team_count = $this->getClubCount($teamMembers, $startDate, $endDate, 9);
                $club_count = floor($team_count / 3);
                return floor($club_count / 3);
                break;
            case 7:  // Sponsoring club

                $totalCount = $this->getClubCount($teamMembers, $startDate, $endDate, 9);
               
                return floor($totalCount / 3);
                break;
            case 6:  //TEAM SPONSORING CLUB LEADERS COUNT
                $team_count = collect($teamMembers)->sum(function ($member) use ($startDate, $endDate, $userId) {
                    $subTeamMembers = $this->getTeamMembers($member['user_id']);
                    $totalCount = $this->getTeamCount($subTeamMembers, $userId, $startDate, $endDate, 9);
                    return $totalCount;
                });
                $count =  floor($team_count / 3);
                return floor($count / 3);
                break;
            case 8:  //TEAM SPONSORING CLUB COUNT
                // $team_count = collect($teamMembers)->sum(function ($member) use ($startDate, $endDate, $userId) {
                //     $subTeamMembers = $this->getTeamMembers($member['user_id']);
                //     $totalCount = $this->getClubCount($subTeamMembers, $startDate, $endDate, 9);
                //     return $totalCount;
                // });
                $team_count = collect($teamMembers)->sum(function ($member) use ($startDate, $endDate, $userId) {
                    $subTeamMembers = $this->getTeamMembers($member['user_id']);
                    $totalCount = $this->getTeamCount($subTeamMembers, $userId, $startDate, $endDate, 9);
                    return $totalCount;
                });
                return floor($team_count / 3);
                break;
            default:
                return 0;
        }
    }

    private function getAchievementCountOrPV($list_id, $userId, $startDate, $endDate, $type)
    {
        $totalCount = AchievementTracker::with('achievement')
            ->select('user_id', 'achievement_id', DB::raw('COUNT(*) as count'), DB::raw('ROUND(SUM(pv), 2) as pv_sum'), DB::raw('ROUND(SUM(group_pv), 2) as gpv_sum'))
            ->where('user_id', $userId)
            ->where('achievement_id', $list_id)
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy('user_id', 'achievement_id')
            ->first();

        if ($list_id == 3 || $list_id == 9) {
            return $totalCount->count ?? 0;
        } elseif ($list_id == 11) {
            return $totalCount->pv_sum ?? 0;
        } elseif ($list_id == 13) {
            return $totalCount->gpv_sum ?? 0;
        }
    }

    private function getTarget($user_id, $achievement_id, $month)
    {
        $target = AchievementTarget::where('user_id', $user_id)
            ->where('achievement_id', $achievement_id)
            ->where('target_date', $month)
            ->value('target_value');

        return $target ?? 0;
    }

    public function getUserAchievementCount($teamMembers, $userId, $startDate, $endDate, $achievement_id)
    {
        $totalCount = AchievementTracker::with('achievement')
            ->select(DB::raw('COUNT(achievement_id) as achievement_count'))
            ->where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->where('achievement_id', $achievement_id)
            ->get()
            ->pluck('achievement_count')
            ->sum();

        return $totalCount;
    }

    public function getClubCount($teamMembers, $startDate, $endDate, $achievement_id)
    {
        $totalCount = AchievementTracker::with('achievement')
            ->select(DB::raw('COUNT( member_id) as user_count'))
            ->whereIn('member_id', $teamMembers->pluck('user_id'))
            ->where('achievement_id', $achievement_id)
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->pluck('user_count')
            ->sum();

        return $totalCount;
    }

    public function getTeamCount($teamMembers, $userId, $startDate, $endDate, $achievement_id)
    {
        $totalCount = AchievementTracker::with('achievement')
            ->select(DB::raw('COUNT(DISTINCT user_id) as user_count'))
            ->whereIn('user_id', $teamMembers->pluck('user_id'))
            ->whereBetween('date', [$startDate, $endDate])
            ->where('achievement_id', $achievement_id)
            ->first();

        return $totalCount->user_count;
    }

    public function getTeamAchievementCount($teamMembers, $startDate, $endDate, $userId)
    {
        $totalCount = AchievementTracker::with('achievement')
            ->select('achievement_id', DB::raw('COUNT(DISTINCT  member_id) as team_count'))
            ->whereIn('member_id', $teamMembers->pluck('user_id'))
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy('achievement_id')
            ->get()
            ->keyBy('achievement_id')
            ->map->team_count;

        return $totalCount;
    }

    public function getSponsoringCount($teamMembers, $startDate, $endDate, $userId)
    {
        $totalCount = AchievementTracker::with('achievement')
            ->select('achievement_id', DB::raw('COUNT(user_id) as team_count'))
            ->where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy('achievement_id')
            ->get()
            ->keyBy('achievement_id')
            ->map->team_count;

        return $totalCount;
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


            $teamMembers = TeamMember::with(['achievementTracker' => function ($query) use ($request) {
                $query->where('achievement_id', $request->achievement_id);
            }])
                ->with('user', 'achievementTitle', 'userPermissions')
                ->where('introducer_id', $request->user_id)
                ->whereHas('achievementTracker', function ($query) use ($request) {
                    $query->where('achievement_id', $request->achievement_id);
                    if ($request->date) {
                        $query->whereDate('date', $request->date);
                    }
                })
                ->select('user_id', 'title')
                ->get();

            // Check if request parameter 'pv' is set
            if ($request->pv) {
                // Filter team members based on the sum of target value
                $teamMembers = $teamMembers->filter(function ($member) use ($request) {
                    $sumTargetValue = $member->achievementTracker->sum('pv');
                    return $sumTargetValue == $request->pv;
                });
            }


            $data = [];

            foreach ($teamMembers as $key => $value) {

                $avatar = ($value->user->avatar) ? asset('http://tathastudd.com/public/storage/avatar/' . $value->user->avatar) : null;

                $title = ($value->achievementTitle) ? $value->achievementTitle->value : null;

                $team_members = $this->getTeamMembers($value['user_id']);

                $user = User::where('id', $value['user_id'])->first();

                $currentDate = Carbon::now()->toDateString();

                // Define achievement IDs mapping
                $achievementIdsMapping = [
                    1 => 2,
                    3 => 4,
                    5 => 6,
                    7 => 8,
                    9 => 10,
                    11 => 12,
                    13 => 13
                ];

                $team_count_current_month = $this->getAchievement($achievementIdsMapping[$request->achievement_id] ?? null, $value['user_id'], $currentMonthStart, $currentMonthEnd, $team_members);
                $team_count_prev_month = $this->getAchievement($achievementIdsMapping[$request->achievement_id] ?? null, $value['user_id'], $previousMonthStart, $previousMonthEnd, $team_members);
                $team_today_count = $this->getAchievement($achievementIdsMapping[$request->achievement_id] ?? null, $value['user_id'], $currentDate, $currentDate, $team_members);

                $current_month_bill_date = $this->getBilldate($value['user_id'], $request->achievement_id, $currentMonthStart, $currentMonthEnd);
                $previous_month_bill_date = $this->getBilldate($value['user_id'], $request->achievement_id, $previousMonthStart, $previousMonthEnd);
                $user_current_month_target = $this->getTarget($value['user_id'], $request->achievement_id, Carbon::now()->format('Y-m-d'));
                $team_current_month_target = $this->getTeamtarget($team_members, $value['user_id'], $request->achievement_id, Carbon::now()->format('Y-m-d'));

                $data[] = [
                    'user_id' => $value['user_id'],
                    'first_name' => $user->first_name,
                    'last_name' => ($user->last_name != null) ? ($user->last_name) : '',
                    'place' => $user->place,
                    'avatar' => $avatar,
                    'title' => $title,
                    'current_month_count' => $this->getAchievement($request->achievement_id, $value['user_id'], $currentMonthStart, $currentMonthEnd, $team_members),
                    'previous_month_count' => $this->getAchievement($request->achievement_id, $value['user_id'], $previousMonthStart, $previousMonthEnd, $team_members),
                    'team_count_current_month' => $team_count_current_month,
                    'team_count_previous_month' => $team_count_prev_month,
                    'today_count' => $this->getAchievement($request->achievement_id, $value['user_id'], $currentDate, $currentDate, $team_members),
                    'team_today_count' => $team_today_count,
                    'target' => $user_current_month_target,
                    'team_target' => $team_current_month_target,
                    'current_month_bill_date' => $current_month_bill_date,
                    'previous_month_bill_date' => $previous_month_bill_date,
                    'status' => ($value->userPermissions->status) ?? 0
                ];
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

    // public function getPreviousmonnthtracker(Request $request)
    // {
    //     try {
    //         if ($request->token) {
    //             $user = User::where('token', $request->token)->firstOrFail(['id']);

    //             $list = AchievementList::select('id', 'title', 'target')
    //                 ->get();

    //             $team_members = $this->getTeamMembers($user->id);

    //             $currentYear = Carbon::now()->year;
    //             $currentMonth = Carbon::now()->month;

    //             $data = [];
    //             foreach ($list as $value) {
    //                 $achievementData = [];

    //                 // Loop through each year
    //                 for ($year = $currentYear; $year >= $currentYear - 1; $year--) { // 2 years
    //                     // Loop through each month
    //                     for ($month = ($year == $currentYear ? $currentMonth - 2
    //                         : 12); $month > 0; $month--) {
    //                         $start = Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
    //                         $end = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();

    //                         $count = $this->getAchievement($value->id, $user->id, $start, $end, $team_members);
    //                         $bill_date = $this->getBilldate($user->id, $value->id, $start, $end);
    //                         $target = $this->getTarget($user->id, $value->id, Carbon::now());

    //                         $achievementData[] = [
    //                             'month_year' => Carbon::create($year, $month, 1)->format('M Y'),
    //                             'count' => $count,
    //                             'bill_date' => $bill_date,
    //                             'target' => $target
    //                         ];
    //                     }
    //                 }

    //                 $perPage = 3; // Number of months per page
    //                 $currentPage = $request->input('page', 1); // Get current page from request

    //                 $paginatedData = array_slice($achievementData, ($currentPage - 1) * $perPage, $perPage);

    //                 $data[] = [
    //                     'achievement_name' => $value->title,
    //                     'achievement_id' => $value->id,
    //                     'data' => $paginatedData
    //                 ];
    //             }

    //             $totalPages = ceil(count($achievementData) / $perPage);

    //             $paginationData = [
    //                 'current_page' => $currentPage,
    //                 'last_page' => $totalPages,
    //                 'current_page_url' => $request->fullUrlWithQuery(['page' => $currentPage]),
    //                 'last_page_url' => $request->fullUrlWithQuery(['page' => $totalPages]),
    //                 'data' => $data,
    //             ];

    //             if (!$list->isEmpty() || $paginationData) {
    //                 return $this->simpleReturn('success', $paginationData);
    //             }
    //         }

    //         return $this->simpleReturn('error', 'No Records found', 404);
    //     } catch (\Throwable $th) {
    //         return $this->simpleReturn('error', $th->getMessage(), 404);
    //     }
    // }

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
                [1, 10],
                [1, 15],
                [1, 20],
                [1, 25],
                [1, 31], // For the last period of current month
            ];



            foreach ($list as $value) {
                $period_data = [];
                foreach ($periods as $period) {
                    $start = $period[0];
                    $end = $period[1];

                    $current_month_start = Carbon::parse($currentMonthStart)->addDays($start - 1)->toDateString();
                    $current_month_end = Carbon::parse($current_month_start)->addDays($end - $start)->toDateString();

                    $prev_month_start = Carbon::parse($previousMonthStart)->addDays($start - 1)->toDateString();
                    $prev_month_end = Carbon::parse($prev_month_start)->addDays($end - $start)->toDateString();

                    if ($end > $currentMonthEnd->day) {
                        $current_month_end = $currentMonthEnd->toDateString();
                    }
                
                    if ($end > $previousMonthEnd->day) {
                        $prev_month_end = $previousMonthEnd->toDateString();
                    }

                    $user_current_month_count = $this->getAchievement($value->id, $user->id, $current_month_start, $current_month_end, $team_members);
                    $user_previous_month_count = $this->getAchievement($value->id, $user->id, $prev_month_start, $prev_month_end, $team_members);

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

        if ($forTeam) {
            $query = AchievementTarget::select(DB::raw('COUNT(achievement_id) as achievement_count'))
                ->where('achievement_id', $achievementId)
                ->where('target_date', $endDate)
                ->whereIn('user_id', $teamMembers)
                ->get()
                ->pluck('achievement_count');
            return $query->sum();
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
}
