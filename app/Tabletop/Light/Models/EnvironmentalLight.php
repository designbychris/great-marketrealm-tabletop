<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Light\Models;

defined('ABSPATH') || exit;

final class EnvironmentalLight
{
    public function __construct(
        private string $id, private string $tableId, private string $sceneId,
        private string $kind, private string $label, private float $x, private float $y,
        private int $brightFeet, private int $dimFeet, private bool $lit = true
    ) {}
    public function id(): string { return $this->id; }
    public function tableId(): string { return $this->tableId; }
    public function sceneId(): string { return $this->sceneId; }
    public function kind(): string { return $this->kind; }
    public function label(): string { return $this->label; }
    public function x(): float { return $this->x; }
    public function y(): float { return $this->y; }
    public function brightFeet(): int { return $this->brightFeet; }
    public function dimFeet(): int { return $this->dimFeet; }
    public function totalFeet(): int { return $this->brightFeet + $this->dimFeet; }
    public function lit(): bool { return $this->lit; }
    public function withLit(bool $lit): self { return new self($this->id,$this->tableId,$this->sceneId,$this->kind,$this->label,$this->x,$this->y,$this->brightFeet,$this->dimFeet,$lit); }
    public function toArray(): array { return ['id'=>$this->id,'table_id'=>$this->tableId,'scene_id'=>$this->sceneId,'kind'=>$this->kind,'label'=>$this->label,'x'=>$this->x,'y'=>$this->y,'bright_feet'=>$this->brightFeet,'dim_feet'=>$this->dimFeet,'lit'=>$this->lit]; }
    public static function fromArray(array $r): self { return new self((string)($r['id']??''),(string)($r['table_id']??''),(string)($r['scene_id']??''),(string)($r['kind']??'torch'),(string)($r['label']??'Torch'),(float)($r['x']??0),(float)($r['y']??0),max(0,(int)($r['bright_feet']??20)),max(0,(int)($r['dim_feet']??20)),!array_key_exists('lit',$r)||!empty($r['lit'])); }
}
