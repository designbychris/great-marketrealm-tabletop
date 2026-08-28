<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Core;

use GreatMarketrealmTabletop\Integration\Companion\CompanionAvailability;
use GreatMarketrealmTabletop\Integration\Companion\CompanionGateway;
use GreatMarketrealmTabletop\Tables\Services\TableRegistry;
use GreatMarketrealmTabletop\Tables\Services\TableRegistryFactory;
use GreatMarketrealmTabletop\Tables\Memberships\Services\TableGathering;
use GreatMarketrealmTabletop\Tables\Memberships\Services\TableGatheringFactory;
use GreatMarketrealmTabletop\Tables\Scenes\Services\TableSceneManager;
use GreatMarketrealmTabletop\Tables\Scenes\Services\TableSceneManagerFactory;
use GreatMarketrealmTabletop\Tables\Tokens\Services\TableTokenManager;
use GreatMarketrealmTabletop\Tables\Tokens\Services\TableTokenManagerFactory;
use GreatMarketrealmTabletop\Tabletop\TabletopServiceProvider;

defined('ABSPATH') || exit;

final class Application
{
    private static ?self $instance = null;

    private bool $booted = false;

    private CompanionGateway $companion;

    private TableRegistry $tables;

    private TableGathering $gathering;

    private TableSceneManager $scenes;

    private TableTokenManager $tokens;

    private TabletopServiceProvider $tabletop;

    private function __construct()
    {
        $this->companion = new CompanionAvailability();
        $this->tables = TableRegistryFactory::make();
        $this->gathering = TableGatheringFactory::make();
        $this->scenes = TableSceneManagerFactory::make();
        $this->tokens = TableTokenManagerFactory::make();
        $this->tabletop = new TabletopServiceProvider();
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

        $this->tabletop->register();
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
            : '0.26.7-alpha.1';
    }

    public function companion(): CompanionGateway
    {
        return $this->companion;
    }

    public function tables(): TableRegistry
    {
        return $this->tables;
    }

    public function gathering(): TableGathering
    {
        return $this->gathering;
    }

    public function scenes(): TableSceneManager
    {
        return $this->scenes;
    }

    public function tokens(): TableTokenManager
    {
        return $this->tokens;
    }
}
