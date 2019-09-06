<?php

/*
 * This file is part of PHP CS Fixer.
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

//钉钉预警
define('DINGDING_NOTICE', 1);
//邮件预警
define('EMAIL_NOTICE', 2);

return [
    //redis连接信息
    'redis' => [
        'host'     => '192.168.1.10',
        'port'     => '6379',
        //'password' => '', //无密码的话不要设置为空
        'database' => 1,
        'timeout'  => 2,
    ],
    //MQ连接信息
    'connection' => [
		'host'            => '192.168.9.10',
		'vhost'           => 'v1',
		'port'            => '5672',
		'login'           => 'test',
		'password'        => 'test',
		'connect_timeout' => 10, //连接超时时间
    ],
    //连接MQ失败预警
    'connectRules' => [
        'connectFailTimes' => 3, //单次执行，连续连接MQ失败达到预警的次数
        'interval'         => 2, //尝试重连的时间间隔（单位：s）
        'mode'             => [ //预警模式
            'type'  => DINGDING_NOTICE,
            'token' => '钉钉机器人token', //钉钉机器人token
        ],
    ],
    //监控队列配置
    'queueRules' => [
    	//队列名称
        'test' => [
            'name'             => 'test',
			'vhost'            => 'v1',
			'isConsecutive'    => 1, //1： 在有效时间内连续达到预警数量， 0：不需要连续，只需要在有效时间内达到预警数量即可，不配置，默认为1
            'warningMsgCount'  => 10, //队列积压达到预警的数量
            'warningTimes'     => 3, //连续监控到队列积压达到预警的次数，结合warningMsgCount使用
            'duringTime'       => 600, //在有效duringTime的时间内，检测到队列的数量连续warningTimes次达到warningMsgCount，则预警
            'mode'             => [ //预警模式
                'type'  => DINGDING_NOTICE,
                'token' => '钉钉机器人token',
            ],
        ],
		'test2' => [
			'name'             => 'test2',
			'vhost'            => 'v2',
			'warningMsgCount'  => 20,
			'warningTimes'     => 3,
			'duringTime'       => 600,
			'mode'             => [ //预警模式
				'type'  => DINGDING_NOTICE,
				'token' => '钉钉机器人token',
			],
		],
    ],
];
