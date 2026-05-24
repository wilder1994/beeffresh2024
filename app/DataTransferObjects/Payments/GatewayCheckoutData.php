<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Payments;

final readonly class GatewayCheckoutData
{
    /**
     * @param  array<string, mixed>  $widgetConfig
     */
    public function __construct(
        public string $widgetScriptUrl,
        public array $widgetConfig,
    ) {}
}
