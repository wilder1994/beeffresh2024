<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\NotificationActionUrl;
use PHPUnit\Framework\TestCase;

class NotificationActionUrlTest extends TestCase
{
    public function test_normalize_strips_old_ngrok_host_to_relative_path(): void
    {
        $url = 'https://a1c2-2803-e5e0-1d03-ca00-8441-fd6f-8ef3-8e0c.ngrok-free.app/domiciliario/pedidos/1';

        $this->assertSame('/domiciliario/pedidos/1', NotificationActionUrl::normalize($url));
    }

    public function test_normalize_keeps_relative_paths(): void
    {
        $this->assertSame('/notificaciones', NotificationActionUrl::normalize('/notificaciones'));
    }
}
