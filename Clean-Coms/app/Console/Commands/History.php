<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use RongCloud\RongCloud;
use Carbon\Carbon;

class History extends Command
{
    protected $name = 'getDailyRecord';
    protected $description = 'Get daily history chat record from RongCloud';
    private $appKey = 'uwd1c0sxupuf1';
    private $appSecret = 'b2wDnmqRM4';

    public function handle()
    {
        $rongCloud = new RongCloud($this->appKey, $this->appSecret);
        $date = explode(' ', Carbon::now())[0];
        $dateSplit = explode('-', $date);
        $dateInFormat = $dateSplit[0] . $dateSplit[1] . $dateSplit[2] . '14';
        $result = $rongCloud->message()->getHistory($dateInFormat);
        
    }

}
