<?php
if (!function_exists('sportsplay_role_demo_data')) {
    function sportsplay_role_demo_data(): array
    {
        $userName = (string)($_SESSION['user_name'] ?? 'SportsPlay User');
        $first = trim(explode(' ', $userName)[0] ?? 'User');
        if ($first === '') { $first = 'User'; }

        return [
            'meta' => [
                'user_name' => $userName,
                'first' => $first,
                'season' => 'Spring 2026',
            ],

            'coach' => [
                'team' => [
                    'name' => 'U14 Blue Soccer',
                    'league' => 'Rochester Youth League · Division A',
                    'season' => 'Spring 2026',
                    'home_field' => 'RIT Turf Field 2',
                    'assistant' => 'Mia Peterson',
                    'record' => '5W · 1D · 1L',
                    'training_days' => 'Tue / Thu',
                ],
                'players' => [
                    ['number'=>7,'name'=>'Luka Petrovic','pos'=>'CM','age'=>13,'attendance'=>'96%','parent'=>'Ana Petrovic','phone'=>'(585) 555-2101'],
                    ['number'=>10,'name'=>'Noah Williams','pos'=>'ST','age'=>14,'attendance'=>'91%','parent'=>'Chris Williams','phone'=>'(585) 555-2198'],
                    ['number'=>4,'name'=>'Mila Johnson','pos'=>'CB','age'=>13,'attendance'=>'94%','parent'=>'Kelsey Johnson','phone'=>'(585) 555-2034'],
                    ['number'=>1,'name'=>'Ethan Brown','pos'=>'GK','age'=>14,'attendance'=>'89%','parent'=>'Marcus Brown','phone'=>'(585) 555-2220'],
                    ['number'=>11,'name'=>'Sofia Ramirez','pos'=>'RW','age'=>13,'attendance'=>'95%','parent'=>'Elena Ramirez','phone'=>'(585) 555-2488'],
                    ['number'=>8,'name'=>'Alex Chen','pos'=>'CM','age'=>13,'attendance'=>'90%','parent'=>'David Chen','phone'=>'(585) 555-2441'],
                ],
                'parents' => [
                    ['parent'=>'Ana Petrovic','child'=>'Luka Petrovic','email'=>'ana.parent@sportsplay.test','phone'=>'(585) 555-2101'],
                    ['parent'=>'Chris Williams','child'=>'Noah Williams','email'=>'chris.parent@sportsplay.test','phone'=>'(585) 555-2198'],
                    ['parent'=>'Kelsey Johnson','child'=>'Mila Johnson','email'=>'kelsey.parent@sportsplay.test','phone'=>'(585) 555-2034'],
                    ['parent'=>'Marcus Brown','child'=>'Ethan Brown','email'=>'marcus.parent@sportsplay.test','phone'=>'(585) 555-2220'],
                    ['parent'=>'Elena Ramirez','child'=>'Sofia Ramirez','email'=>'elena.parent@sportsplay.test','phone'=>'(585) 555-2488'],
                ],
                'schedule' => [
                    ['kind'=>'Training','title'=>'Technical Session','date'=>'Tue, Mar 3','time'=>'6:00 PM','location'=>'RIT Turf Field 2'],
                    ['kind'=>'Training','title'=>'Set Pieces + Fitness','date'=>'Thu, Mar 5','time'=>'6:15 PM','location'=>'RIT Turf Field 1'],
                    ['kind'=>'Match','title'=>'vs Irondequoit Strikers U14','date'=>'Sat, Mar 7','time'=>'11:00 AM','location'=>'East Ridge Complex'],
                    ['kind'=>'Match','title'=>'vs Brighton Elite U14','date'=>'Sun, Mar 15','time'=>'2:30 PM','location'=>'RIT Turf Field 2'],
                ],
                'conversations' => [
                    ['name'=>'Ana Petrovic','child'=>'Luka Petrovic','channel'=>'Parent','last'=>'Can Luka arrive 10 min later?','time'=>'9:48 AM','unread'=>2],
                    ['name'=>'Chris Williams','child'=>'Noah Williams','channel'=>'Parent','last'=>'Thanks coach, payment is done.','time'=>'Yesterday','unread'=>0],
                    ['name'=>'Luka Petrovic','child'=>'#7 CM','channel'=>'Player','last'=>'I practiced free kicks today.','time'=>'Yesterday','unread'=>1],
                    ['name'=>'Team Staff','child'=>'Assistant Coach','channel'=>'Staff','last'=>'Indoor dome confirmed for Thursday.','time'=>'Mon','unread'=>0],
                ],
                'chat_thread' => [
                    ['who'=>'other','text'=>'Hi coach, Luka has a school event and might be 10 minutes late to training.', 'time'=>'9:42 AM'],
                    ['who'=>'me','text'=>'Thanks for letting me know. No problem — have him join warm-up as soon as he arrives.', 'time'=>'9:45 AM'],
                    ['who'=>'other','text'=>'Perfect, thank you! Also, should he still bring both kits for Saturday?', 'time'=>'9:47 AM'],
                    ['who'=>'me','text'=>'Yes — blue + white kit, shin guards, and water bottle. I’ll post a team announcement too.', 'time'=>'9:49 AM'],
                ],
                'announcements' => [
                    ['title'=>'Saturday Match Logistics', 'audience'=>'Players + Parents', 'channel'=>'Team Broadcast', 'time'=>'Today · 10:05 AM', 'body'=>'Please arrive 15 minutes early. Bring both kits (blue/white), shin guards, and water. Parking at east lot entrance.'],
                    ['title'=>'Training Moved Indoors', 'audience'=>'Players', 'channel'=>'Team Broadcast', 'time'=>'Yesterday · 6:13 PM', 'body'=>'Thursday training will be in the Indoor Dome due to weather. Same time, same check-in process.'],
                ],
            ],

            'parent' => [
                'children' => [
                    ['name'=>'Luka Petrovic','age'=>13,'team'=>'U14 Blue Soccer','position'=>'CM','jersey'=>7],
                    ['name'=>'Mia Petrovic','age'=>10,'team'=>'U10 Blue Soccer','position'=>'LW','jersey'=>11],
                ],
                'selected' => ['name'=>'Luka Petrovic','age'=>13,'team'=>'U14 Blue Soccer','position'=>'CM','jersey'=>7,'coach'=>'Coach Jovan','coach_email'=>'coach@sportsplay.test','coach_phone'=>'(585) 555-2007'],
                'notifications' => [
                    ['type'=>'warning','text'=>'Uniform kit payment is still pending.'],
                    ['type'=>'info','text'=>'Saturday match starts at 11:00 AM (arrive by 10:45 AM).'],
                    ['type'=>'success','text'=>'Attendance this month improved to 92%.'],
                ],
                'schedule' => [
                    ['kind'=>'Training','title'=>'Technical Session','date'=>'Tue, Mar 3','time'=>'6:00 PM','location'=>'RIT Turf Field 2'],
                    ['kind'=>'Match','title'=>'vs Irondequoit Strikers U14','date'=>'Sat, Mar 7','time'=>'11:00 AM','location'=>'East Ridge Complex'],
                    ['kind'=>'Training','title'=>'Set Pieces + Fitness','date'=>'Thu, Mar 5','time'=>'6:15 PM','location'=>'RIT Turf Field 1'],
                ],
                'payments' => [
                    ['item'=>'Spring 2026 Season Fee','amount'=>'$180.00','status'=>'Paid','date'=>'Feb 10, 2026'],
                    ['item'=>'Uniform Kit','amount'=>'$45.00','status'=>'Unpaid','date'=>'—'],
                    ['item'=>'Tournament Fee (Optional)','amount'=>'$35.00','status'=>'Pending','date'=>'Due Mar 12, 2026'],
                ],
                'chat_thread' => [
                    ['who'=>'other','text'=>'Hi! Can Luka leave 10 minutes early after Thursday training?', 'time'=>'Yesterday · 7:02 PM'],
                    ['who'=>'me','text'=>'Yes, he has a school thing. I’ll bring him earlier to make up warm-up.', 'time'=>'Yesterday · 7:04 PM'],
                    ['who'=>'other','text'=>'That works. Please make sure he brings both kits for Saturday.', 'time'=>'Yesterday · 7:05 PM'],
                ],
                'announcements' => [
                    ['title'=>'Saturday Match Logistics','time'=>'Today · 10:05 AM','body'=>'Arrive 15 minutes early. Bring both kits and water. Parking at east lot entrance.'],
                    ['title'=>'Indoor Training Update','time'=>'Yesterday · 6:13 PM','body'=>'Thursday training moved to Indoor Dome due to weather.'],
                ],
            ],

            'player' => [
                'profile' => [
                    'name' => $userName !== 'SportsPlay User' ? $userName : 'Player Aleksa',
                    'team' => 'U14 Blue Soccer',
                    'position' => 'Central Midfielder',
                    'number' => 8,
                    'season' => 'Spring 2026',
                ],
                'stats' => [
                    'matches' => 7,
                    'goals' => 3,
                    'assists' => 5,
                    'attendance' => '92%',
                    'pass_accuracy' => '84%',
                    'minutes' => 468,
                ],
                'schedule' => [
                    ['kind'=>'Training','title'=>'Technical Session','date'=>'Tue, Mar 3','time'=>'6:00 PM','location'=>'RIT Turf Field 2'],
                    ['kind'=>'Match','title'=>'vs Irondequoit Strikers U14','date'=>'Sat, Mar 7','time'=>'11:00 AM','location'=>'East Ridge Complex'],
                    ['kind'=>'Training','title'=>'Set Pieces + Fitness','date'=>'Thu, Mar 5','time'=>'6:15 PM','location'=>'RIT Turf Field 1'],
                ],
                'standings' => [
                    ['team'=>'U14 Blue Soccer','pts'=>16,'w'=>5,'d'=>1,'l'=>1],
                    ['team'=>'Brighton Elite U14','pts'=>15,'w'=>5,'d'=>0,'l'=>2],
                    ['team'=>'Irondequoit Strikers U14','pts'=>12,'w'=>4,'d'=>0,'l'=>3],
                    ['team'=>'Webster Eagles U14','pts'=>10,'w'=>3,'d'=>1,'l'=>3],
                ],
                'payments' => [
                    ['item'=>'Spring 2026 Season Fee','amount'=>'$180.00','status'=>'Paid','date'=>'Feb 10, 2026'],
                    ['item'=>'Uniform Kit','amount'=>'$45.00','status'=>'Unpaid','date'=>'—'],
                ],
                'messages' => [
                    ['from'=>'Coach Jovan','subject'=>'Saturday match prep','body'=>'Arrive 15 minutes early. Bring water + shin guards.','time'=>'Today · 4:10 PM'],
                    ['from'=>'Coach Jovan','subject'=>'Training update','body'=>'Training moved to Indoor Dome due to weather.','time'=>'Yesterday · 7:22 PM'],
                ],
                'chat_thread' => [
                    ['who'=>'other','text'=>'Nice work today. Keep scanning before receiving the ball.', 'time'=>'Today · 7:19 PM'],
                    ['who'=>'me','text'=>'Thanks coach! I’ll work on that. Should I play more one-touch in midfield?', 'time'=>'Today · 7:24 PM'],
                    ['who'=>'other','text'=>'Yes, especially in buildup. Also check your shoulder before the pass arrives.', 'time'=>'Today · 7:26 PM'],
                ],
                'announcements' => [
                    ['title'=>'Saturday Match Logistics','time'=>'Today · 10:05 AM','body'=>'Please arrive 15 minutes early. Bring both kits, shin guards, and water.'],
                    ['title'=>'Thursday Training Indoor','time'=>'Yesterday · 6:13 PM','body'=>'Training will be in the Indoor Dome. Same time.'],
                ],
            ],
        ];
    }
}
