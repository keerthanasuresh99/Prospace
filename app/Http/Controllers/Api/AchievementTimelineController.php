<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AchievementDream;
use App\Models\BaseSettings;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AchievementTimelineController extends Controller
{
    public function getAchievementTitles()
    {
        $list = BaseSettings::select('id', 'value')->whereIn('type', ['dream', 'achiever'])->where('is_deleted', 0)
            ->where('is_dream', 1)->latest()->get();

        if ($list->count()) {
            return $this->simpleReturn('success', $list);
        }
        return $this->simpleReturn('error', 'No Records found', 404);
    }

    public function addAchievementDream(Request $request)
    {
        $rules = array(
            'achievement_date' => 'required',
            // Add other validation rules if needed
        );

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->simpleReturn('error', $validator->errors(), 400);
        }

        // Check if updating or adding
        if ($request->achievement_dream_id) {
            $add_dream = AchievementDream::find($request->achievement_dream_id);
            if (!$add_dream) {
                return $this->simpleReturn('error', 'Achievement dream not found', 404);
            }
        } else {
            $add_dream = new AchievementDream();
        }

        if ($request->featured_image == "1") {
            $count = AchievementDream::where('featured_image', 1)->count();

            if ($count == 2) {
                return $this->simpleReturn('error', 'Cannot add more than two featured images', 400);
            }
        }

        if ($request->achievement_id) {
            $achievement_id = $request->achievement_id;
        } else if ($request->achievement_name) {
            $add = new BaseSettings();
            $add->type = 'dream';
            $add->value = $request->achievement_name;
            $add->is_dream = 1;

            if ($add->save()) {
                $achievement_id  = $add->id;
            }
        }

        if ($request->image) {
            $extension = $request->file('image')->extension();
            $image = time() . mt_rand(100, 999) . '.' . $extension;
            Storage::disk('templates')->putFileAs('', $request->file('image'), $image);
            $dream_image = $image;
        } else {
            $templateImage = Template::where('achiever_id', $request->achievement_id)->where('is_deleted', 0)->first();
            $dream_image = $templateImage ? $templateImage->image : $add_dream->image;
        }

        $add_dream->achievement_id  = $achievement_id;
        $add_dream->achievement_date  =  Carbon::createFromFormat('Y-m-d', $request->achievement_date)->format('Y-m-d');
        $add_dream->image = $dream_image ?? null;
        $add_dream->featured_image = $request->featured_image;

        if ($add_dream->save()) {
            return $this->simpleReturn('success', $request->achievement_dream_id ? 'Dream updated successfully.' : 'Dream added successfully.', 200);
        }

        return $this->simpleReturn('error', 'Something went wrong', 400);
    }


    public function listAchievementDream()
    {
        $list = AchievementDream::select('id', 'achievement_id', 'image', 'achievement_date', 'created_at', 'achieved_date')
            ->with('achievementTitle')->latest()->get();

        if ($list) {

            if ($list->count()) {
                // Iterate through each dream and calculate the time differences
                foreach ($list as $dream) {


                    //$dream->set_date = Carbon::parse($dream->created_at)->toDateString();

                    $dream->url = asset('http://tathastudd.com/public/storage/templates/' . $dream->image);

                    $set_date = Carbon::parse($dream->created_at);

                    $now = Carbon::now();

                    // Parse the achievement date
                    $endDate = Carbon::parse($dream->achievement_date);

                    $totalDays = $endDate->diffInDays($set_date);
                    $difference = $set_date->diff($endDate);


                    // Extract the difference in years, months, days, hours, and minutes
                    $years = $difference->y;
                    $months = $difference->m;
                    $days = $difference->d;
                    $hours = $difference->h;
                    $minutes = $difference->i;
                    $seconds = $difference->s;

                    // Convert years and months to a floating-point year representation
                    $totalYears = $years + ($months / 12) + ($days / 365.25);

                    // Convert total days to hours
                    $totalHours = $totalDays * 24 + $hours;

                    // Format the remaining time
                    $formattedRemainingTime = sprintf("%.1fYr- %dM- %d Hrs", $totalYears, ($years * 12) + $months, $totalHours);


                    $differenceInMinutes = $set_date->diffInMinutes($now);
                    $hours = intdiv($differenceInMinutes, 60);
                    $minute = $differenceInMinutes % 60;

                    // Format the output
                    $elapsedTime = sprintf('%d : %d', $minutes, $seconds);


                    // Add the additional data to the dream object
                    $dream->remaining_time = $formattedRemainingTime;

                    $dream->countdown = $elapsedTime;

                    $status = null;

                    $endDate = $endDate->startOfDay();
                    $set_date = $set_date->startOfDay();


                    if ($endDate->isBefore($set_date)) {
                        // Late
                        $status = 'Days late to achieve';
                    } elseif ($dream->achieved_date !== null && Carbon::parse($dream->achieved_date)->lt($endDate)) {
                        // Achieved before deadline
                        $status = 'Days before achieved';
                    } elseif ($endDate->isSameDay($set_date) && $dream->achieved_date) {
                        // Achieved on the same day as the deadline
                        $status = 'Achieved';
                    } else {
                        // Days to Achieve
                        $status = 'More days to go';
                    }


                    $dream->status = $status;
                    // Calculate days remaining or days late
                    $isLate = false;
                    if ($dream->achieved_date !== null) {
                        $achievedDate = Carbon::parse($dream->achieved_date);
                        if ($achievedDate->gt($endDate)) {
                            // Days late
                            $daysRemaining = $achievedDate->diffInDays($endDate);
                            $isLate = true;
                        } else {
                            // Days remaining until achievement
                            $daysRemaining = $endDate->diffInDays($achievedDate);
                        }
                    } else {
                        // If not achieved, calculate days remaining from now
                        if ($endDate->isBefore($now)) {
                            $daysRemaining = $endDate->diffInDays($now);
                            $isLate = true;
                        } else {

                            $difference = $now->diff($endDate);
                            $daysRemaining = $difference->days;
                        }
                    }

                    $dream->days_remaining = $daysRemaining;
                    $dream->is_late = $isLate;
                }

                return $this->simpleReturn('success', $list);
            }
        }
        return $this->simpleReturn('error', 'No Records found', 404);
    }

    public function deleteAchievementDream(Request $request)
    {
        try {
            $list = AchievementDream::findOrFail($request->id);

            if ($list) {

                $fileName = $list->image;
                if (Storage::disk('templates')->exists($fileName)) {
                    // Delete the file
                    Storage::disk('templates')->delete($fileName);
                }

                $list->delete();

                return $this->simpleReturn('success', 'Deleted successfully', 200);
            } else {
                return $this->simpleReturn('error', 'No Records found', 404);
            }
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Return error response if record not found
            return $this->simpleReturn('error', 'No Records found', 404);
        } catch (\Throwable $th) {
            // Return error response for other exceptions
            return $this->simpleReturn('error', $th->getMessage(), 500);
        }
    }

    public function  listFeaturedDreams()
    {
        try {
            $featuredDream = AchievementDream::where('featured_image', 1)->with('achievementTitle')->get();

            if ($featuredDream->isEmpty()) {
                return $this->simpleReturn('error', 'No records found', 404);
            }

            return $this->simpleReturn('success', $featuredDream, 200);
        } catch (\Throwable $th) {
            return $this->simpleReturn('error', $th->getMessage(), 500);
        }
    }
}
