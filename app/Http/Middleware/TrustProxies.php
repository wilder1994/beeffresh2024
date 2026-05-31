<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * @var array<int, string>|string|null
     */
    protected $proxies;

    public function __construct()
    {
        // Solo proxies de loopback (ngrok → 127.0.0.1:8080). Evita que un cliente
        // envíe X-Forwarded-Proto: https en localhost y rompa cookies/CSRF en HTTP.
        if (app()->environment('local')) {
            $this->proxies = ['127.0.0.1', '::1'];
        }
    }

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;
}
