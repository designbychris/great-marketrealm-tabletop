<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tabletop\Light\Contracts;
use GreatMarketrealmTabletop\Tabletop\Light\Models\EnvironmentalLight;
defined('ABSPATH') || exit;
interface EnvironmentalLightRepository { /** @return array<int,EnvironmentalLight> */ public function forScene(string $tableId,string $sceneId): array; public function find(string $tableId,string $sceneId,string $id): ?EnvironmentalLight; public function save(EnvironmentalLight $light): void; public function delete(string $tableId,string $sceneId,string $id): void; }
