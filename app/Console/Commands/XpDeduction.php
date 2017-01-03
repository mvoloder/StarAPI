<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\GenericModel;
use MongoDB\BSON\ObjectID;


class XpDeduction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'XpDeduction';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check user activity - deduct XP based on user inactivity';

    /**
     * XpDeduction constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        GenericModel::setCollection('profiles');
        $profiles = GenericModel::all();

        $profileHashMap = [];
        foreach ($profiles as $profile) {
            $profileHashMap[$profile->_id] = $profile;
        }

        $daysChecked = 0;

        do {
            // set current time of cron start and get all logs for previous 4 days
            $date = new \DateTime();
            $cronTime = $date->format('U');
            GenericModel::setCollection('logs');
            $unixNow = $date->format('U') - (24 * 60 * 60 * $daysChecked);
            $unixDayAgo = $unixNow - 24 * 60 * 60;
            $hexNow = dechex($unixNow);
            $hexDayAgo = dechex($unixDayAgo);
            $logs = GenericModel::where('_id', '<', new ObjectID($hexNow . '0000000000000000'))
                ->where('_id', '>=', new ObjectID($hexDayAgo . '0000000000000000'))
                ->get();

            $logHashMap = [];
            foreach ($logs as $log) {
                $logHashMap[$log->id] = $log;
            }

            foreach ($profileHashMap as $user) {
                if (isset($user->banned) && $user->banned === true) {
                    unset ($profileHashMap[$user->_id]);
                    continue;
                }

                if (isset($user->active) && $user->active === false) {
                    unset ($profileHashMap[$user->_id]);
                    continue;
                }

                if (key_exists($user->_id, $logHashMap)) {
                    $profileHashMap[$user->_id]->lastTimeActivityCheck = $cronTime;
                    $profileHashMap[$user->_id]->save();
                    unset($profileHashMap[$user->_id]);
                    continue;
                }

                if (!key_exists($user->_id, $logHashMap) && $daysChecked === 4) {
                    $profile = $profileHashMap[$user->_id];
                    if ($profile->xp - 1 == 0) {
                        //set banned flag and save to DB
                        GenericModel::setCollection('xp');
                        $userXP = GenericModel::where('_id', '=', $profile->xp_id)->first();
                        $records = $userXP->records;
                        $records[] = [
                            'xp' => -1,
                            'details' => 'Xp deducted for inactivity. Profile banned!',
                            'timestamp' => $cronTime
                        ];
                        $userXP->records = $records;
                        $userXP->save();
                        $profile->xp = 0;
                        $profile->banned = true;
                        $profile->save();
                    } else {
                        // New XP record creation and save to DB
                        GenericModel::setCollection('xp');
                        $userXP = GenericModel::where('_id', '=', $profile->xp_id)->first();
                        $records = $userXP->records;
                        $records[] = [
                            'xp' => -1,
                            'details' => 'Xp deducted for inactivity.',
                            'timestamp' => $cronTime
                        ];
                        $userXP->records = $records;
                        $userXP->save();
                        $profile->xp--;
                        $profile->lastTimeActivityCheck = $cronTime;
                        $profile->save();
                    }
                }
            }

            $daysChecked++;
            unset ($logHashMap);
        } while (count($profileHashMap) > 0 || $daysChecked === 4);
    }
}
