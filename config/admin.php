<?php

return [

    'menu' => [
        [
            'route' => 'dashboard',
            'icon' => 'gauge',
            'text' => 'DASHBOARD',
            'sub' => false
        ],
        [
            'route' => 'franchise',
            'icon' => 'reorder-horizontal',
            'text' => 'FRANCHISE',
            'sub' => FALSE,
        ],
        [
            'route' => 'users',
            'icon' => 'account-multiple',
            'text' => 'USER MANAGMENT',
            'sub' => FALSE,
        ],
        [
            'route' => 'syllabus',
            'icon' => 'book',
            'text' => 'CURRICULUM',
            'sub' => FALSE,
        ],
        [
            'route' => 'children',
            'icon' => 'account-multiple',
            'text' => 'ACADEMIC ENROLLMENT',
            'sub' => true,
            'sub_menu' => [
                [
                    'route' => 'userEnrollment',
                    'text' => 'USER',
                ],
                [
                    'route' => 'children',
                    'text' => 'CHILDREN',
                ],
            ]
        ],
        [
            'route' => 'viewSyllabus',
            'icon' => 'book',
            'text' => 'ASIIGN SYLLABUS',
            'sub' => false,
        ],
        [
            'route' => 'calendar',
            'icon' => 'calendar',
            'text' => 'CALENDAR',
            'sub' => false,
        ],
        [
            'route' => 'tasks',
            'icon' => 'buffer',
            'text' => 'TASKS',
            'sub' => true,
            'sub_menu' => [
                [
                    'route' => 'tasks',
                    'text' => 'Tasks',
                ],
                [
                    'route' => 'tickets',
                    'text' => 'Tickets',
                ],

            ]
        ],
        [
            'route' => 'class-timtable',
            'icon' => 'clock',
            'text' => 'TIMETABLE',
            'sub' => true,
            'sub_menu' => [
                [
                    'route' => 'class-timtable',
                    'text' => 'Class TimeTable',
                ],
                [
                    'route' => 'exam-timtable',
                    'text' => 'Exam TimeTable',
                ],

            ]
        ],
        [
            'route' => '',
            'icon' => 'reorder-horizontal',
            'text' => 'REPORTS',
            'sub' => true,
            'sub_menu' => [
                [
                    'route' => 'academic-report',
                    'text' => 'Academic Report',
                ],

            ]
        ],
    ]
];
