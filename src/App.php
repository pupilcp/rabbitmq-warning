<?php

/*
 * This file is part of PHP CS Fixer.
 * (c) php-team@yaochufa <php-team@yaochufa.com>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace LCP;

use LCP\Service\Format;
use LCP\Service\Monitor;
use LCP\Service\Notice;

class App
{
    private $config     = null;
    private $connection = null;

    /**
     * 构造方法 - 连接MQ.
     */
    public function __construct()
    {
    }

    /**
     * 启动监控服务.
     */
    public function start()
    {
        var_dump('Job start...');
        $p1 = new \Swoole\Process([$this, 'checkConnection']);
        $p2 = new \Swoole\Process([$this, 'checkOverStock']);
        $p1->start();
        $p2->start();
        while (true) {
            if (!\Swoole\Process::wait()) {
                break;
            }
        }
        var_dump('Job end...');
    }

    /**
     * 监控连接MQ是否正常.
     */
    public function checkConnection()
    {
        try {
            $this->connection = Monitor::getInstance()->checkConnection();
			//触发MQ连接成功动作
			\LCP\Event\Event::trigger('MQ_CONNECT_SUCCESS');
        } catch (\Throwable $e) {
            $data          = (new Format())->formatConnectErrorMsg(GC::$config['connection']);
            $data['token'] = GC::$config['connectRules']['mode']['token'];
            Notice::getInstance()->notice($data, GC::$config['connectRules']['mode']['type']);
			//触发MQ连接失败动作
			\LCP\Event\Event::trigger('MQ_CONNECT_ERROR');
        }
    }

    /**
     * 监控MQ队列消息是否积压.
     */
	public function checkOverStock()
    {
        if (!empty(GC::$config['queueRules'])) {
            foreach (GC::$config['queueRules'] as $queueName => $queueConfig) {
                try {
                    $result = Monitor::getInstance()->checkOverStock($queueConfig);
                    if (false !== $result) {
                        $data          = (new Format())->formatOverStockMsg(GC::$config['connection'], $queueConfig['vhost'], $queueName, $result);
                        $data['token'] = $queueConfig['mode']['token'];
                        Notice::getInstance()->notice($data, $queueConfig['mode']['type']);
                    }
                } catch (\AMQPConnectionException $e) {
                    var_dump($e->getMessage());
                    break;
                } catch (\Throwable $e) {
                    var_dump($e->getMessage());
                    break;
                }
            }
        }
    }
}
