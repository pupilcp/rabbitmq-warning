<?php

/*
 * This file is part of PHP CS Fixer.
 * (c) php-team@yaochufa <php-team@yaochufa.com>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace LCP\Service;

class Format
{
    /**
     * 格式化连接MQ超时错误信息.
     *
     *
     * @param array $config MQ配置信息
     *
     * @return array
     */
    public function formatConnectErrorMsg(array $config)
    {
        //调用预警服务
        $data              = [];
        $data['title']     = '监控多次连接MQ服务器失败';
        $data['content']   = '### <font color=#C70909 face="黑体">Error: ' . $data['title'] . '</font>' . PHP_EOL
            . '- Host: ' . $config['host'] . PHP_EOL
            . '- User: ' . $config['login'] . PHP_EOL
            . '- Port: ' . $config['port'] . PHP_EOL
            . '- Vhost: ' . $config['vhost'] . PHP_EOL
            . '- Level: 一级' . PHP_EOL
        ;

        return $data;
    }

    /**
     * 格式化连接MQ数据积压的信息.
     *
     *
     * @param array  $config    MQ配置信息
     * @param string $vhost     vhost
     * @param string $queueName 队列名
     * @param int    $amount    积压数量
     *
     * @return array
     */
    public function formatOverStockMsg(array $config, string $vhost, string $queueName, int $amount)
    {
        //调用预警服务
        $data              = [];
        $data['title']     = 'MQ积压消息过多';
        $data['content']   = '### <font color=#FF8C1C face="黑体">Notice: ' . $data['title'] . '</font>' . PHP_EOL
            . '- Host: ' . $config['host'] . PHP_EOL
            . '- User: ' . $config['login'] . PHP_EOL
            . '- Port: ' . $config['port'] . PHP_EOL
            . '- Vhost: ' . $vhost . PHP_EOL
            . '- Queue: ' . $queueName . PHP_EOL
            . '- 积压数量: ' . $amount . PHP_EOL
        ;

        return $data;
    }
}
