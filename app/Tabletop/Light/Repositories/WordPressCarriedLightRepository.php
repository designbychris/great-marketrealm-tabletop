<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tabletop\Light\Repositories;
use GreatMarketrealmTabletop\Tabletop\Light\Contracts\CarriedLightRepository;
defined('ABSPATH') || exit;
final class WordPressCarriedLightRepository implements CarriedLightRepository
{
    private const OPTION = 'gmrt_carried_lights';
    public function isLit(string $tableId, string $sceneId, string $tokenId): bool
    {
        $all = get_option(self::OPTION, []);
        return !empty($all[$tableId][$sceneId][$tokenId]);
    }
    public function setLit(string $tableId, string $sceneId, string $tokenId, bool $lit): void
    {
        $all = get_option(self::OPTION, []);
        if (!is_array($all)) $all = [];
        if ($lit) {
            $all[$tableId][$sceneId][$tokenId] = true;
        } else {
            unset($all[$tableId][$sceneId][$tokenId]);
            if (empty($all[$tableId][$sceneId])) unset($all[$tableId][$sceneId]);
            if (empty($all[$tableId])) unset($all[$tableId]);
        }
        update_option(self::OPTION, $all, false);
    }
}
