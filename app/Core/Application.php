<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Core;

use GreatMarketrealmTabletop\Integration\Companion\CompanionAvailability;
use GreatMarketrealmTabletop\Integration\Companion\CompanionGateway;

defined('ABSPATH') || exit;

final class Application
{
    private static ?self $instance = null;

    private bool $booted = false;

    private CompanionGateway $companion;

    private function __construct()
    {
        $this->companion = new CompanionAvailability();
    }

    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    public function boot(): void
    {
        if ($this->booted) {
            return;
        }

        $this->booted = true;

        do_action('gmrt_booted', $this);
    }

    public function booted(): bool
    {
        return $this->booted;
    }

    public function version(): string
    {
        return defined('GMRT_VERSION')
            ? (string) GMRT_VERSION
            : '0.1.0-alpha.1';
    }

    public function companion(): CompanionGateway
    {
        return $this->companion;
    }
}
