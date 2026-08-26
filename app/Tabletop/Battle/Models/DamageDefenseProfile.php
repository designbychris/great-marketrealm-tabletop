<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Battle\Models;

use InvalidArgumentException;

defined('ABSPATH') || exit;

final class DamageDefenseProfile
{
    /**
     * @param array<int,string> $resistances
     * @param array<int,string> $vulnerabilities
     * @param array<int,string> $immunities
     */
    public function __construct(
        private string $tokenId,
        private array $resistances = [],
        private array $vulnerabilities = [],
        private array $immunities = []
    ) {
        if (trim($tokenId) === '') {
            throw new InvalidArgumentException(
                'A damage defense profile requires a token ID.'
            );
        }

        $this->resistances = $this->normalize($resistances);
        $this->vulnerabilities = $this->normalize($vulnerabilities);
        $this->immunities = $this->normalize($immunities);
    }

    public function tokenId(): string
    {
        return $this->tokenId;
    }

    public function resists(string $type): bool
    {
        return in_array(
            DamageType::assert($type),
            $this->resistances,
            true
        );
    }

    public function vulnerableTo(string $type): bool
    {
        return in_array(
            DamageType::assert($type),
            $this->vulnerabilities,
            true
        );
    }

    public function immuneTo(string $type): bool
    {
        return in_array(
            DamageType::assert($type),
            $this->immunities,
            true
        );
    }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return [
            'token_id' => $this->tokenId,
            'resistances' => $this->resistances,
            'vulnerabilities' => $this->vulnerabilities,
            'immunities' => $this->immunities,
        ];
    }

    /** @param array<string,mixed> $record */
    public static function reconstitute(array $record): self
    {
        return new self(
            (string) ($record['token_id'] ?? ''),
            is_array($record['resistances'] ?? null)
                ? $record['resistances']
                : [],
            is_array($record['vulnerabilities'] ?? null)
                ? $record['vulnerabilities']
                : [],
            is_array($record['immunities'] ?? null)
                ? $record['immunities']
                : []
        );
    }

    /**
     * @param array<int,string> $types
     * @return array<int,string>
     */
    private function normalize(array $types): array
    {
        $normalized = [];

        foreach ($types as $type) {
            $normalized[] = DamageType::assert(
                (string) $type
            );
        }

        return array_values(
            array_unique($normalized)
        );
    }
}
