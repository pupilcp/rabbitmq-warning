<?php

/*
 * This file is part of PHP CS Fixer.
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace LCP\Service;

use LCP\GC;
use RedisClient\ClientFactory;

class Redis
{
    private static $instance = null;
    public $redis = null;

    private function __construct()
    {
		$redisConf = [
			'server'   => GC::$config['redis']['host'] . ':' . GC::$config['redis']['port'],
			'timeout'  => GC::$config['redis']['timeout'],
			'database' => GC::$config['redis']['database'],
		];
		if(isset(GC::$config['redis']['password'])){
			$redisConf['password'] = GC::$config['redis']['password'];
		}
		$this->redis = ClientFactory::create($redisConf);
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
}
