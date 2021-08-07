<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Redis;
use Redis as RedisClient;

class ActivityController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function add($url, $date)
    {
        Redis::transaction(function (RedisClient $redis) use ($url, $date) {
            $redis->hIncrBy('counters', $url, 1);
            $redis->hSet('last_visits', $url, $date);
        });
    }

    public function get()
    {
        $counters = Redis::hGetAll('counters');
        $visits = Redis::hGetAll('last_visits');

        $result = [];

        foreach ($counters as $url => $count)
        {
            $result[$url] = [
                'count' => $count,
                'last_visit' => $visits[$url],
            ];
        }

        return $result;
    }
}
