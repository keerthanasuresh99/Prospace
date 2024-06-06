<?php

namespace Database\Seeders;

use App\Models\AchievementList;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AchievementTitleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $titles = [
            'FSP PLUS ACHIEVEMENT', 'TEAM FSP PLUS ACHIEVERS COUNT', 'FAST START ACHIEVEMENT', 'TEAM FSP ACHIEVERS COUNT',
            'SPONSORING CLUB LEADER', 'TEAM SPONSORING CLUB LEADERS COUNT', 'SPONSORING CLUB', 'TEAM SPONSORING CLUB COUNT', 'SPONSORING CHALLENGE',
            'TEAM SPONSORING COUNT', 'LOYALTY CHALLENGE', 'TEAM PBV COUNT', 'GROUP BUSINESS VOLUME'
        ];

        $trackerTitles = [
            'FAST START ACHIEVEMENT',
            'SPONSORING CHALLENGE',
            'LOYALTY CHALLENGE',
            'GROUP BUSINESS VOLUME',
        ];

        foreach ($titles as $title) {
            AchievementList::create([
                'title' => $title,
                'is_tracker_list' => in_array($title, $trackerTitles), // Set to true only for tracker list items
            ]);
        }
    }
}
