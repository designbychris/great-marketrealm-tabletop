<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Light\Models;

final class MagicalLight
{
    public function __construct(
        private string $id,
        private string $tableId,
        private string $sceneId,
        private string $tokenId,
        private int $ownerUserId,
        private string $spellId,
        private string $label,
        private int $brightFeet,
        private int $dimFeet,
        private int $expiresAt
    ) {}

    public function id(): string { return $this->id; }
    public function tableId(): string { return $this->tableId; }
    public function sceneId(): string { return $this->sceneId; }
    public function tokenId(): string { return $this->tokenId; }
    public function ownerUserId(): int { return $this->ownerUserId; }
    public function spellId(): string { return $this->spellId; }
    public function label(): string { return $this->label; }
    public function brightFeet(): int { return $this->brightFeet; }
    public function dimFeet(): int { return $this->dimFeet; }
    public function totalFeet(): int { return $this->brightFeet + $this->dimFeet; }
    public function expiresAt(): int { return $this->expiresAt; }
    public function expired(?int $now = null): bool { return $this->expiresAt > 0 && $this->expiresAt <= ($now ?? time()); }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return [
            'id'=>$this->id,'table_id'=>$this->tableId,'scene_id'=>$this->sceneId,
            'token_id'=>$this->tokenId,'owner_user_id'=>$this->ownerUserId,
            'spell_id'=>$this->spellId,'label'=>$this->label,
            'bright_feet'=>$this->brightFeet,'dim_feet'=>$this->dimFeet,
            'expires_at'=>$this->expiresAt,
        ];
    }

    /** @param array<string,mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            (string)($data['id']??''),(string)($data['table_id']??''),(string)($data['scene_id']??''),
            (string)($data['token_id']??''),(int)($data['owner_user_id']??0),(string)($data['spell_id']??''),
            (string)($data['label']??'Magical Light'),max(0,(int)($data['bright_feet']??0)),max(0,(int)($data['dim_feet']??0)),
            max(0,(int)($data['expires_at']??0))
        );
    }
}
