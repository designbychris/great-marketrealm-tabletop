<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Satchel\Services;

use GreatMarketrealmTabletop\Tabletop\Battle\Contracts\D20Roller;
use InvalidArgumentException;

final class QuickHandsRoller
{
    public function __construct(private D20Roller $roller) {}

    /** @param array<string,mixed> $character @return array<string,mixed> */
    public function roll(array $character, string $kind, string $key): array
    {
        $play = is_array($character['play'] ?? null) ? $character['play'] : [];
        $kind = trim($kind);
        $key = trim($key);
        $modifier = null;
        $label = '';
        $proficient = false;
        $expertise = false;

        if ($kind === 'ability') {
            $entry = is_array($play['abilities'][$key] ?? null) ? $play['abilities'][$key] : null;
            $modifier = $entry === null ? null : (int) ($entry['modifier'] ?? 0);
            $label = strtoupper(substr($key, 0, 3)) . ' check';
        } elseif ($kind === 'save') {
            $entry = is_array($play['saving_throws'][$key] ?? null) ? $play['saving_throws'][$key] : null;
            $modifier = $entry === null ? null : (int) ($entry['modifier'] ?? 0);
            $proficient = ! empty($entry['proficient']);
            $label = strtoupper(substr($key, 0, 3)) . ' saving throw';
        } elseif ($kind === 'skill') {
            $entry = is_array($play['skills'][$key] ?? null) ? $play['skills'][$key] : null;
            $modifier = $entry === null ? null : (int) ($entry['modifier'] ?? 0);
            $proficient = ! empty($entry['proficient']);
            $expertise = ! empty($entry['expertise']);
            $label = ucwords(str_replace('-', ' ', $key));
        } elseif ($kind === 'initiative' && $key === 'initiative') {
            $modifier = (int) ($play['initiative'] ?? 0);
            $label = 'Initiative';
        }

        if ($modifier === null || $label === '') {
            throw new InvalidArgumentException('That Quick Hands roll is not present on this Companion Character.');
        }

        $die = $this->roller->roll();
        return [
            'kind' => $kind, 'key' => $key, 'label' => $label,
            'die' => $die, 'modifier' => $modifier, 'total' => $die + $modifier,
            'proficient' => $proficient, 'expertise' => $expertise,
            'natural_twenty' => $die === 20, 'natural_one' => $die === 1,
        ];
    }
}
