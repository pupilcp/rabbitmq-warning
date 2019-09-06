<?php

/*
 * This file is part of PHP CS Fixer.
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace LCP\Service;

class Notice
{
    private static $instance = null;

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
     * 发送通知入口.
     *
     * @param array $data 通知数据，根据不同的方式
     * @param int   $type 通知方式
     */
    public function notice($data = [], $type = DINGDING_NOTICE)
    {
        switch ($type) {
            case DINGDING_NOTICE:
                $this->sendDingding($data['token'], $data['title'], $data['content'], $data['isAtAll'] ?? false, $data['atMobiles'] ?? []);
                break;
            case EMAIL_NOTICE:
                $this->sendEmail($data['title'], $data['content'], $data['email']);
                break;
            default:
                break;
        }
    }

    /**
     * 发送email.
     *
     * @param mixed $title
     * @param mixed $content
     * @param mixed $email
     */
    private function sendEmail($title, $content, $email)
    {
    }

    /**
     * 调用钉钉接口请求发送自定义机器人消息.
     *
     * @param string $token     请求token
     * @param mixed  $content
     * @param mixed  $isAtAll
     * @param mixed  $atMobiles
     * @param mixed  $title
     *
     * @return bool
     */
    private function sendDingding($token, $title, $content, $isAtAll = false, $atMobiles = [])
    {
        if (empty($token) || empty($content)) {
            return false;
        }
        $markdown = [
            'title' => $title,
            'text'  => $content,
        ];
        $data = [
            'msgtype'  => 'markdown',
            'markdown' => json_encode($markdown),
            'at'       => [
                'isAtAll'   => $isAtAll,
                'atMobiles' => $atMobiles,
            ],
        ];
        $apiUrl = 'https://oapi.dingtalk.com/robot/send?access_token=' . $token;

        return $this->httpPost($apiUrl, json_encode($data));
    }

    /**
     * http post request.
     *
     * @param mixed $url_mixed
     * @param mixed $dataString
     * @param mixed $timeoutTime
     * @param mixed $https
     *
     * @return array
     */
    private function httpPost($url_mixed, $dataString, $timeoutTime = 5, $https = false)
    {
        $headerArr = [
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen($dataString),
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url_mixed);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $https);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArr);
        if (null !== $timeoutTime) {
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeoutTime);
        }
        ob_start();
        curl_exec($ch);
        $response = ob_get_contents();
        ob_end_clean();

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        return [
            'httpCode' => $httpCode,
            'response' => $response,
        ];
    }
}
