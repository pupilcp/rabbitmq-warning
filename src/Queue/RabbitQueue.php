<?php

/*
 * This file is part of PHP CS Fixer.
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace LCP\Queue;

class RabbitQueue
{
    private $conn    = null;
    private $config  = null;

    /**
     * RabbitQueue构造方法.
     *
     * @param mixed $config
     *
     * @throws
     */
    private function __construct($config)
    {
        $conn = new \AMQPConnection($config);
        $conn->connect();
        $this->conn   = $conn;
        $this->config = $config;

        return $conn;
    }

    /**
     * 建立Queue连接.
     *
     * @param array $config 连接配置
     *
     * @throws
     *
     * @return mixed
     */
    public static function getConnection(array $config)
    {
        try {
            //连接broker
            return new self($config);
        } catch (\AMQPConnectionException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw $e;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * queue message length.
     *
     *
     * @param mixed $queueName
     *
     * @throws
     *
     * @return int
     */
    public function getMessageCount($queueName)
    {
        if (!$this->conn->isConnected()) {
            throw new \Exception('Connection Break');
        }
        //在连接内创建一个通道
        $ch = new \AMQPChannel($this->conn);
        $q  = new \AMQPQueue($ch);
        $q->setName($queueName);
        $q->setFlags(AMQP_PASSIVE);
        $len = $q->declareQueue();
        $this->close();

        return $len ?? 0;
    }

    /**
     * close connection.
     */
    public function close()
    {
        if (!$this->conn->isConnected()) {
            return true;
        }

        $this->conn->disconnect();
    }
}
