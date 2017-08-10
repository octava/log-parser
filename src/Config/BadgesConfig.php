<?php

namespace App\Config;

class BadgesConfig
{
    const KEY_BADGES = 'badges';
    const KEY_SEVERITY = 'severity';
    const KEY_HTTP = 'http';

    /**
     * @var string[]
     */
    protected $severity;

    /**
     * @var string[]
     */
    protected $http;

    public function __construct(array $config = [])
    {
        $this->severity = $config[self::KEY_SEVERITY];
        $this->http = $config[self::KEY_HTTP];
    }
}
