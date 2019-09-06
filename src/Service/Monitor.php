<?php

/*
 * This file is part of PHP CS Fixer.
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace LCP\Service;

use LCP\GC;
use LCP\Queue\RabbitQueue;

class Monitor
{
    private static $instance  = null;
    private $connectFailTimes = 5; //默认尝试连接失败次数
    private $connectInterval  = 3; //默认尝试连接时间间隔（second）

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    public static function getInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * 监控连接MQ是否正常.
     *
     * @throws
     *
     * @return object
     */
    public function checkConnection()
    {
        $count            = 1;
        $success          = false;
        $connection       = null;
        $execption        = null;
        $connectRules     = GC::$config['connectRules'];
        $connectFailTimes = $connectRules['connectFailTimes'] <= 0 ? $this->connectFailTimes : (int) $connectRules['connectFailTimes'];
        $interval         = $connectRules['interval'] <= 0 ? $this->connectInterval : (int) $connectRules['interval'];
        while ($count <= $connectFailTimes && !$success) {
            try {
                $connection = RabbitQueue::getConnection(GC::$config['connection']);
                $success    = true;
                break;
            } catch (\AMQPConnectionException $e) {
                $execption = $e;
            } catch (\Throwable $e) {
                $execption = $e;
            }
            $count++;
            sleep($interval);
        }
        if (!$success) {
            throw $execption;
        }

        return $connection;
    }

    /**
     * 监控MQ消息数量是否有积压.
     *
     * @param mixed $queueConfig queue配置
     *
     * @throws
     *
     * @return int/false
     */
    public function checkOverStock($queueConfig)
    {
        try {
            $config          = GC::$config['connection'];
            $config['vhost'] = $queueConfig['vhost'];
            $connection      = RabbitQueue::getConnection($config);
            $len             = $connection->getMessageCount($queueConfig['name']);
            $key             = $queueConfig['name'] . 'WarningMsgCount';
            if ($this->triggerOverStock($len, $key, $queueConfig)) {
                return $len;
            }
        } catch (\AMQPConnectionException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw $e;
        }

        return false;
    }

    /**
     * MQ数量积压的次数是否达到预警.
     *
     * @param string $key         存储某个队列积压次数的key
     * @param array  $queueConfig 当前队列配置
     * @param int    $len         队列当前消息量
     *
     * @throws
     *
     * @return bool 是否达到预警
     */
    private function triggerOverStock($len, $key, $queueConfig)
    {
        try {
            $redis = Redis::getInstance()->redis;
            if ($len >= (int) $queueConfig['warningMsgCount']) {
                if ($redis->exists($key)) {
                    $redis->incr($key);
                    if ((int) $redis->get($key) >= (int) $queueConfig['warningTimes']) {
                        //达到预警次数，预警并清零
                        $redis->del($key);

                        return true;
                    }
                } else {
                    $redis->setex($key, $queueConfig['duringTime'], 1);
                }
            } else {
                //是否连续监控到达预警次数
                if (isset($queueConfig['isConsecutive'])) {
                    if (1 == (int) $queueConfig['isConsecutive']) {
                        $redis->exists($key) && $redis->del($key);
                    }
                } else {
                    //不存在该参数，默认为连续
                    $redis->exists($key) && $redis->del($key);
                }
            }
        } catch (\Throwable $e) {
            throw $e;
        }

        return false;
    }
}
