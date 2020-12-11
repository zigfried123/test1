<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Response;

use Predis\Client;

class VisitCounter
{
    /**
     * @var Client
     */
    private $redis;

    /**
     * VisitCounter constructor.
     * @param Client $redis
     */
    public function __construct(Client $redis)
    {
        $this->redis = $redis;
    }

    public function logIn(): void
    {
        $redis = $this->redis;

        $redis->incr('id');

        $redis->incr('count');

        $count = $redis->get('count');


        $id = $redis->get('id');


        $redis->hmset("log:$id", ['datetime' => time(), 'status' => 1, 'count' => $count]);
    }

    public function logOff(): void
    {
        $redis = $this->redis;

        $redis->incr('id');

        if ($redis->get('count') > 0) {
            $redis->decr('count');
        }

        $count = $redis->get('count');

        $id = $redis->get('id');

        $redis->hmset("log:$id", ['datetime' => time(), 'status' => 2, 'count' => $count]);
    }

    /**
     * @param Client $redis
     * @return array|Response
     * @throws \Exception
     */
    public function getCountsByRange(Client $redis)
    {
        $logData = $this->getLogData($redis);

        $ranges = $this->getRanges('1', 's', current($logData['dates']), end($logData['dates']));

        if (!$ranges) {
            return new Response('Interval less range');
        }

        $counts = [];

        foreach ($ranges as $range => $val) {
            $range1 = +explode('-', $range)[0];
            $range2 = +explode('-', $range)[1];

            foreach ($logData['counterData'] as $datetime => $count) {
                if ($datetime >= $range1 && $datetime < $range2) {
                    $counts[$range] = $count;
                    echo $count;
                }
            }
        }

        return $counts;
    }

    /**
     * @param $redis
     * @return array
     */
    private function getLogData($redis): array
    {
        $id = 0;
        $dates = [];
        $counterData = [];

        while (true) {
            ++$id;
            $log = $redis->hmget("log:$id", ['datetime', 'count']);
            if ($log[0] == null) {
                break;
            }

            [$datetime, $count] = $log;

            $dates[] = $datetime;
            $counterData[$datetime] = $count;
        }

        return compact('dates', 'counterData');
    }

    /**
     * @param int $interval
     * @param string $unit
     * @param int $start
     * @param int $end
     * @return array|false
     * @throws \Exception
     */
    private function getRanges(int $interval, string $unit, int $start, int $end)
    {
        switch ($unit) {
            case 's':
                $intervalSec = $interval;
                break;
            case 'm':
                $intervalSec = $interval * 60;
                break;
            case 'h':
                $intervalSec = $interval * 3600;
                break;
            case 'd':
                $intervalSec = $interval * 86400;
                break;
            default:
                throw new \Exception('Undefined unit type');
        }

        if ($end - $start >= $intervalSec) {
            $ranges = range($start, $end, $intervalSec);

            $reformatRange = [];

            foreach ($ranges as $key => &$range) {
                if (isset($ranges[$key + 1])) {
                    $reformatRange[(string)$range . '-' . (string)$ranges[$key + 1]] = $key;
                } else {
                    unset($ranges[$key]);
                }
            }

            return $reformatRange;
        }

        return false;
    }
}
