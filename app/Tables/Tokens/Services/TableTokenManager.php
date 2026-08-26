<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tables\Tokens\Services;

use GreatMarketrealmTabletop\Tables\Contracts\TableClock;
use GreatMarketrealmTabletop\Tables\Contracts\TableRepository;
use GreatMarketrealmTabletop\Tables\Models\TableStatus;
use GreatMarketrealmTabletop\Tables\Scenes\Contracts\TableSceneRepository;
use GreatMarketrealmTabletop\Tables\Tokens\Contracts\TableTokenIdGenerator;
use GreatMarketrealmTabletop\Tables\Tokens\Contracts\TableTokenRepository;
use GreatMarketrealmTabletop\Tables\Tokens\Exceptions\TableTokenException;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableToken;
use GreatMarketrealmTabletop\Tables\Tokens\Models\TableTokenVisibility;
use RuntimeException;

defined('ABSPATH') || exit;

final class TableTokenManager
{
    public function __construct(
        private TableRepository $tables,
        private TableSceneRepository $scenes,
        private TableTokenRepository $tokens,
        private TableTokenIdGenerator $ids,
        private TableClock $clock
    ) {}

    public function place(
        string $tableId,
        string $sceneId,
        string $label,
        string $type,
        ?string $sourceReference,
        ?int $controllerUserId,
        float $x,
        float $y,
        float $widthUnits = 1,
        float $heightUnits = 1,
        string $visibility =
            TableTokenVisibility::VISIBLE
    ): TableToken {
        $this->openTable($tableId);
        $scene = $this->requiredScene(
            $tableId,
            $sceneId
        );

        $scene->coordinates($x, $y);

        $token = TableToken::create(
            $this->ids->generate(),
            $tableId,
            $sceneId,
            $label,
            $type,
            $sourceReference,
            $controllerUserId,
            $x,
            $y,
            $widthUnits,
            $heightUnits,
            $visibility,
            $this->clock->now()
        );

        $this->tokens->save($token);

        return $token;
    }

    public function move(
        string $tableId,
        string $tokenId,
        float $x,
        float $y
    ): TableToken {
        $this->openTable($tableId);
        $token = $this->requiredToken(
            $tableId,
            $tokenId
        );
        $scene = $this->requiredScene(
            $tableId,
            $token->sceneId()
        );

        $scene->coordinates($x, $y);
        $token->move($x, $y);
        $this->tokens->save($token);

        return $token;
    }

    public function resize(
        string $tableId,
        string $tokenId,
        float $widthUnits,
        float $heightUnits
    ): TableToken {
        $this->openTable($tableId);
        $token = $this->requiredToken(
            $tableId,
            $tokenId
        );

        $token->resize(
            $widthUnits,
            $heightUnits
        );
        $this->tokens->save($token);

        return $token;
    }

    public function hide(
        string $tableId,
        string $tokenId
    ): TableToken {
        return $this->setVisibility(
            $tableId,
            $tokenId,
            false
        );
    }

    public function show(
        string $tableId,
        string $tokenId
    ): TableToken {
        return $this->setVisibility(
            $tableId,
            $tokenId,
            true
        );
    }

    /** @return array<int,TableToken> */
    public function forScene(
        string $tableId,
        string $sceneId
    ): array {
        $this->requiredTable($tableId);
        $this->requiredScene(
            $tableId,
            $sceneId
        );

        return $this->tokens->forScene(
            $tableId,
            $sceneId
        );
    }

    private function setVisibility(
        string $tableId,
        string $tokenId,
        bool $visible
    ): TableToken {
        $this->openTable($tableId);
        $token = $this->requiredToken(
            $tableId,
            $tokenId
        );

        $visible
            ? $token->show()
            : $token->hide();

        $this->tokens->save($token);

        return $token;
    }

    private function openTable(
        string $tableId
    ): void {
        $table = $this->requiredTable($tableId);

        if ($table->status() === TableStatus::ENDED) {
            throw new TableTokenException(
                'Tokens cannot be changed after a Table has ended.'
            );
        }
    }

    private function requiredTable(
        string $tableId
    ): \GreatMarketrealmTabletop\Tables\Models\Table {
        $table = $this->tables->find($tableId);

        if ($table === null) {
            throw new RuntimeException(
                'The requested Table could not be found.'
            );
        }

        return $table;
    }

    private function requiredScene(
        string $tableId,
        string $sceneId
    ): \GreatMarketrealmTabletop\Tables\Scenes\Models\TableScene {
        $scene = $this->scenes->find(
            $tableId,
            $sceneId
        );

        if ($scene === null) {
            throw new TableTokenException(
                'The requested token Scene could not be found at this Table.'
            );
        }

        return $scene;
    }

    private function requiredToken(
        string $tableId,
        string $tokenId
    ): TableToken {
        $token = $this->tokens->find(
            $tableId,
            $tokenId
        );

        if ($token === null) {
            throw new TableTokenException(
                'The requested Table token could not be found.'
            );
        }

        return $token;
    }
}
