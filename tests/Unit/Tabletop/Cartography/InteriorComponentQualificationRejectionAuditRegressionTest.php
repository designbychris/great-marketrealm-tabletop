<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class InteriorComponentQualificationRejectionAuditRegressionTest extends TestCase
{
    private string $source;

    protected function setUp(): void
    {
        $this->source = (string) file_get_contents(__DIR__ . '/../../../../assets/js/tabletop.js');
    }

    public function test_rejected_interior_components_are_accounted_for_without_new_seed_authority(): void
    {
        self::assertStringContainsString('IV.30.1G.5Z.10 — Interior Component Qualification Rejection Audit', $this->source);
        self::assertStringContainsString('const illustratedInteriorQualificationRejectedRecords=[];', $this->source);
        self::assertStringContainsString('illustratedInteriorQualificationRejectedComponents', $this->source);
        self::assertStringContainsString('illustratedInteriorQualificationRejectedCells', $this->source);
        self::assertStringContainsString('illustratedInteriorQualificationSingletonRejects', $this->source);
        self::assertStringContainsString('illustratedInteriorQualificationMultiCellRejects', $this->source);
        self::assertStringContainsString('illustratedInteriorQualificationRejectedInitiallyNearRecovered', $this->source);
        self::assertStringContainsString('componentKeys.push(queue[cursor]);', $this->source);
    }

    public function test_rejected_components_are_reaudited_after_secondary_recovery_diagnostically(): void
    {
        self::assertStringContainsString('illustratedInteriorQualificationRejectedAbsorbedComponents', $this->source);
        self::assertStringContainsString('illustratedInteriorQualificationRejectedRemainingComponents', $this->source);
        self::assertStringContainsString('illustratedInteriorQualificationRejectedPostRecoveryAdjacent', $this->source);
        self::assertStringContainsString('illustratedInteriorQualificationRejectedPostRecoveryNarrowGap', $this->source);
        self::assertStringContainsString('they never receive seed authority here.', $this->source);
        self::assertStringContainsString('const secondaryFloodRecovered=illustratedFloorFloodPass();', $this->source);
        self::assertStringContainsString('const maximumPathVertices = 256;', $this->source);
        self::assertStringContainsString('maximumReviewSuggestions', $this->source);
    }
}
