<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Conditions\Services;

use GreatMarketrealmTabletop\Tabletop\Battle\Models\AttackRollMode;
use GreatMarketrealmTabletop\Tabletop\Conditions\Models\ConditionType;
use GreatMarketrealmTabletop\Tabletop\Conditions\Models\TokenCondition;

defined('ABSPATH') || exit;

final class ConditionCombatRules
{
    /**
     * @param array<int,TokenCondition> $attacker
     * @param array<int,TokenCondition> $target
     */
    public function attackRollMode(
        array $attacker,
        array $target
    ): string {
        $attackerTypes = $this->types($attacker);
        $targetTypes = $this->types($target);

        $advantage = $this->hasAny(
            $targetTypes,
            [
                ConditionType::BLINDED,
                ConditionType::RESTRAINED,
                ConditionType::STUNNED,
            ]
        );

        $disadvantage = $this->hasAny(
            $attackerTypes,
            [
                ConditionType::BLINDED,
                ConditionType::POISONED,
                ConditionType::PRONE,
                ConditionType::RESTRAINED,
            ]
        );

        if ($advantage && $disadvantage) {
            return AttackRollMode::NORMAL;
        }

        if ($advantage) {
            return AttackRollMode::ADVANTAGE;
        }

        if ($disadvantage) {
            return AttackRollMode::DISADVANTAGE;
        }

        return AttackRollMode::NORMAL;
    }

    /**
     * @param array<int,TokenCondition> $conditions
     */
    public function blocksBattleDeeds(array $conditions): bool
    {
        return in_array(
            ConditionType::STUNNED,
            $this->types($conditions),
            true
        );
    }

    /**
     * @param array<int,TokenCondition> $conditions
     */
    public function blocksMovement(array $conditions): bool
    {
        return $this->hasAny(
            $this->types($conditions),
            [
                ConditionType::GRAPPLED,
                ConditionType::RESTRAINED,
                ConditionType::STUNNED,
            ]
        );
    }

    /**
     * @param array<int,TokenCondition> $conditions
     * @return array<int,string>
     */
    private function types(array $conditions): array
    {
        return array_map(
            static fn (TokenCondition $condition): string =>
                $condition->condition(),
            $conditions
        );
    }

    /**
     * @param array<int,string> $haystack
     * @param array<int,string> $needles
     */
    private function hasAny(
        array $haystack,
        array $needles
    ): bool {
        return array_intersect(
            $haystack,
            $needles
        ) !== [];
    }
}
