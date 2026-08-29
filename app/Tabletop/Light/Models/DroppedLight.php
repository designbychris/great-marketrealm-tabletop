<?php
declare(strict_types=1);
namespace GreatMarketrealmTabletop\Tabletop\Light\Models;
defined('ABSPATH') || exit;
final class DroppedLight
{
    public function __construct(private string $id, private string $tableId, private string $sceneId, private int $ownerUserId, private float $x, private float $y) {}
    public function id(): string { return $this->id; }
    public function tableId(): string { return $this->tableId; }
    public function sceneId(): string { return $this->sceneId; }
    public function ownerUserId(): int { return $this->ownerUserId; }
    public function x(): float { return $this->x; }
    public function y(): float { return $this->y; }
    public function toArray(): array { return ['id'=>$this->id,'table_id'=>$this->tableId,'scene_id'=>$this->sceneId,'owner_user_id'=>$this->ownerUserId,'x'=>$this->x,'y'=>$this->y]; }
    public static function fromArray(array $r): self { return new self((string)$r['id'],(string)$r['table_id'],(string)$r['scene_id'],(int)$r['owner_user_id'],(float)$r['x'],(float)$r['y']); }
}
