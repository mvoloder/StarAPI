<?php

return [
    /**
     * Fixed Star API configuration
     */
    'internalConfiguration' => [
        'roles' => [
            'admin' => 'Admin',
            'standard' => 'Standard',
            'accountant' => 'Accountant',
        ],
        'employees' => [
            'roles' => [
                'Apprentice' => [
                    'minimumEarnings' => 10000,
                    'coefficient' => 0.27,
                    'xpEntryPoint' => 200,
                ],
                'Junior' => [
                    'minimumEarnings' => 17500,
                    'coefficient' => 0.26,
                    'xpEntryPoint' => 400,
                ],
                'Standard' => [
                    'minimumEarnings' => 30000,
                    'coefficient' => 0.26,
                    'xpEntryPoint' => 600,
                ],
                'Senior' => [
                    'minimumEarnings' => 45000,
                    'coefficient' => 0.25,
                    'xpEntryPoint' => 800,
                ],
                'Leader' => [
                    'minimumEarnings' => 60000,
                    'coefficient' => 0.25,
                    'xpEntryPoint' => 1000,
                ],
            ]
        ],
        'projects' => [
            'reservation' => [
                'maxReservationTime' => env('PROJECT_RESERVATION_TIME', 30)
            ]
        ],
        'tasks' => [
            'reservation' => [
                'maxReservationTime' => env('PROJECT_TASK_RESERVATION_TIME', 3)
            ]
        ],
        'taskHistoryStatuses' => [
            'assigned' => 'Task assigned to %s',
            'claimed' => 'Task claimed by %s',
            'paused' => 'Task paused because of: "%s"',
            'resumed' => 'Task resumed',
            'qa_ready' => 'Task ready for QA',
            'qa_fail' => 'Task failed QA',
            'qa_success' => 'Task passed QA',
        ],
        'taskComplexityOptions' => [
            1,
            2,
            3,
            4,
            5,
            6,
            7,
            8,
            9,
            10
        ],
        'profile_update_xp_message' => 'Hey, your XP changed by {N}',
        'guestRole' => 'guest',
        'defaultRole' => 'standard',
        'skills' => [
            'PHP',
            'React',
            'Cloud',
            'Node',
            'Planning'
        ],
        'webDomain' => env('WEB_DOMAIN', 'http://the-shop.io:3000/'),
        'employeeMonthlyMinimum' => [
            'apprentice' => 10000,
            'junior' => 17500,
            'standard' => 30000,
            'senior' => 45000
        ],
        'taskPriorities' => [
            'High',
            'Medium',
            'Low'
        ],
        'floatPrecision' => 2,
        'priorityMapping' => [
            '1' => 0,
            '2' => 30,
            '3' => 120
        ]
    ],

    /**
     * Dynamic internal configuration
     */
    'internalDynamicConfiguration' => [
        'userRoles' => [
            [
                'resolver' => [
                    'class' => \App\Resolvers\UserRoles::class,
                    'method' => 'getRoles'
                ],
                'settingName' => 'userRoles'
            ]
        ],
    ],

    /**
     * Dynamic configuration that depends on external services
     */
    'externalConfiguration' => [
        'slack' => [
            [
                'resolver' => [
                    'class' => \Vluzrmos\SlackApi\Facades\SlackTeam::class,
                    'method' => 'info'
                ],
                'settingName' => 'teamInfo'
            ]
        ],
    ]

];
