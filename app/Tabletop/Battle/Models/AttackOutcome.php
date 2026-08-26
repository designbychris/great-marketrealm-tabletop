<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Battle\Models;

defined('ABSPATH') || exit;

final class AttackOutcome
{
    public const CRITICAL_HIT = 'critical-hit';
    public const HIT = 'hit';
    public const MISS = 'miss';
    public const CRITICAL_MISS = 'critical-miss';

    /**
     * @param array<int,int>|null $rolls
     */
    public function __construct(
        private int $roll,
        private int $modifier,
        private int $armorClass,
        private string $rollMode = AttackRollMode::NORMAL,
        private ?array $rolls = null
    ) {
        AttackRollMode::assert($this->rollMode);

        $this->rolls ??= [$this->roll];
    }

    public function total(): int
    {
        return $this->roll + $this->modifier;
    }

    public function result(): string
    {
        if ($this->roll === 20) {
            return self::CRITICAL_HIT;
        }

        if ($this->roll === 1) {
            return self::CRITICAL_MISS;
        }

        return $this->total() >= $this->armorClass
            ? self::HIT
            : self::MISS;
    }

    public function isHit(): bool
    {
        return in_array(
            $this->result(),
            [self::HIT, self::CRITICAL_HIT],
            true
        );
    }

    /** @return array<string,int|string|bool> */
    public function toArray(): array
    {
        return [
            'roll' => $this->roll,
            'rolls' => $this->rolls,
            'roll_mode' => $this->rollMode,
            'modifier' => $this->modifier,
            'total' => $this->total(),
            'armor_class' => $this->armorClass,
            'result' => $this->result(),
            'hit' => $this->isHit(),
        ];
    }
}
