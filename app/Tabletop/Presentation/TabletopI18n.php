<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Presentation;

defined('ABSPATH') || exit;

/**
 * Translation catalogue for strings consumed by the live Tabletop JavaScript.
 *
 * Keeping the catalogue in PHP makes every browser-facing string discoverable
 * by WordPress translation tooling while the JavaScript receives only the
 * translated values it needs at runtime.
 */
final class TabletopI18n
{
    /** @return array<string,string> */
    public static function strings(): array
    {
        return [
            'showRecap' => __('Show Recap', 'great-marketrealm-tabletop'),
            'hideRecap' => __('Hide Recap', 'great-marketrealm-tabletop'),
            'liveRefreshFailed' => __('The live Chamber could not be refreshed.', 'great-marketrealm-tabletop'),
            'refreshedMarkupMissing' => __('The refreshed Chamber markup was not found.', 'great-marketrealm-tabletop'),
            'requestRejected' => __('The Tabletop rejected that request.', 'great-marketrealm-tabletop'),
            'pressurePlate' => __('Pressure Plate', 'great-marketrealm-tabletop'),
            'tripwire' => __('Tripwire', 'great-marketrealm-tabletop'),
            'trapSelected' => __('Trap selected — click the battlemap to place it.', 'great-marketrealm-tabletop'),
            'trapCancelled' => __('Trap placement cancelled. Pippin has lifted his feet very carefully.', 'great-marketrealm-tabletop'),
            'trapMoveSelected' => __('Move selected — click the battlemap for the trap’s new position.', 'great-marketrealm-tabletop'),
            'removeTrapConfirm' => __('Remove this trap from the Scene?', 'great-marketrealm-tabletop'),
            'trapUpdated' => __('Trap updated.', 'great-marketrealm-tabletop'),
            'trapFailed' => __('Pippin could not tend that trap.', 'great-marketrealm-tabletop'),
            'trapPositionUpdated' => __('Trap position updated.', 'great-marketrealm-tabletop'),
            'trapPlacementFailed' => __('The trap could not be placed.', 'great-marketrealm-tabletop'),
            'placementRemainsArmed' => __('Placement remains armed; click again or cancel.', 'great-marketrealm-tabletop'),
            'adventureNotesUpdated' => __('Adventure notes updated.', 'great-marketrealm-tabletop'),
            'adventureNotesFailed' => __('Pippin could not turn that page.', 'great-marketrealm-tabletop'),
            'treasureSelected' => __('Treasure selected — click the battlemap to place it.', 'great-marketrealm-tabletop'),
            'treasureCancelled' => __('Treasure placement cancelled. Pippin has stopped drawing little X marks.', 'great-marketrealm-tabletop'),
            'treasureMoveSelected' => __('Move selected — click the battlemap for the treasure’s new position.', 'great-marketrealm-tabletop'),
            'removeTreasureConfirm' => __('Remove this treasure from the Scene?', 'great-marketrealm-tabletop'),
            'treasureUpdated' => __('Treasure updated.', 'great-marketrealm-tabletop'),
            'treasureFailed' => __('Pippin could not amend that treasure record.', 'great-marketrealm-tabletop'),
            'treasurePositionUpdated' => __('Treasure position updated.', 'great-marketrealm-tabletop'),
            'treasurePlacementFailed' => __('The treasure could not be placed.', 'great-marketrealm-tabletop'),
            'close' => __('Close', 'great-marketrealm-tabletop'),
            'open' => __('Open', 'great-marketrealm-tabletop'),
            'interact' => __('Interact', 'great-marketrealm-tabletop'),
            'furniture' => __('Furniture', 'great-marketrealm-tabletop'),
            'selected' => __('selected', 'great-marketrealm-tabletop'),
            'noFurnitureSelected' => __('No furniture selected', 'great-marketrealm-tabletop'),
            'furnitureRearrangeFailed' => __('Pippin could not rearrange that furnishing.', 'great-marketrealm-tabletop'),
            'snapEnabled' => __('Snap to Grid enabled. Pippin has restored order.', 'great-marketrealm-tabletop'),
            'snapDisabled' => __('Snap to Grid disabled. Pippin is trying not to look.', 'great-marketrealm-tabletop'),
            'furniturePlacementCancelled' => __('Placement cancelled. Pippin has put the tape measure away.', 'great-marketrealm-tabletop'),
            'furnitureMoved' => __('Furniture moved. Pippin has amended the floor plan.', 'great-marketrealm-tabletop'),
            'furnitureMoveCancelled' => __('Furniture move cancelled.', 'great-marketrealm-tabletop'),
            'furnitureRotated' => __('Furniture rotated. Pippin has rotated the paper too.', 'great-marketrealm-tabletop'),
            'furnitureResized' => __('Furniture resized. Pippin disputes the new dimensions.', 'great-marketrealm-tabletop'),
            'chestOpened' => __('Chest opened. Pippin has taken three prudent steps backwards.', 'great-marketrealm-tabletop'),
            'chestClosed' => __('Chest closed. Pippin is pretending this solves the problem.', 'great-marketrealm-tabletop'),
            'chooseMimic' => __('Choose a Mimic from the Bestiary first.', 'great-marketrealm-tabletop'),
            'chooseCreature' => __('Choose a creature from the Bestiary first.', 'great-marketrealm-tabletop'),
            'mimicRevealed' => __('The furniture was a Mimic. Pippin would like the record to show that he objected.', 'great-marketrealm-tabletop'),
            'disarmMimicConfirm' => __('Disarm this disguised Mimic? The furnishing will remain.', 'great-marketrealm-tabletop'),
            'mimicDisarmed' => __('Mimic disguise disarmed. Pippin remains unconvinced.', 'great-marketrealm-tabletop'),
            'furnitureDuplicated' => __('Furniture duplicated. Pippin is counting again.', 'great-marketrealm-tabletop'),
            'removeFurnitureConfirm' => __('Remove this furnishing from the Scene?', 'great-marketrealm-tabletop'),
            'furnitureRemoved' => __('Furniture removed. Pippin has reclaimed the floor space.', 'great-marketrealm-tabletop'),
            'furnitureSelectionCleared' => __('Furniture selection cleared.', 'great-marketrealm-tabletop'),
            'preparing' => __('Preparing…', 'great-marketrealm-tabletop'),
            'trainingGroundsPreparing' => __('Sage is preparing the Training Grounds…', 'great-marketrealm-tabletop'),
            'testTableFailed' => __('The test Table could not be prepared.', 'great-marketrealm-tabletop'),
            'prepareTestTable' => __('Prepare Test Table', 'great-marketrealm-tabletop'),
            'chooseAtlasMap' => __('Choose the saved Atlas map Pippin should place first.', 'great-marketrealm-tabletop'),
            'forgePreparing' => __('Pippin is clearing a workbench beside the Forge…', 'great-marketrealm-tabletop'),
            'tablePreparing' => __('Pippin is finding a suitable patch of table…', 'great-marketrealm-tabletop'),
            'tableCreateFailed' => __('The Tabletop could not be created.', 'great-marketrealm-tabletop'),
            'campaignLinking' => __('Pippin is joining the Table Atlas to the Companion Ledger…', 'great-marketrealm-tabletop'),
            'campaignLinked' => __('Campaign linked.', 'great-marketrealm-tabletop'),
            'campaignLinkFailed' => __('The Companion Campaign could not be linked.', 'great-marketrealm-tabletop'),
            'sendingSummons' => __('Sending the Summons…', 'great-marketrealm-tabletop'),
            'invitationSent' => __('Invitation sent.', 'great-marketrealm-tabletop'),
            'inviteFailed' => __('The player could not be invited.', 'great-marketrealm-tabletop'),
            'closingSeat' => __('Closing that seat…', 'great-marketrealm-tabletop'),
            'playerRemoved' => __('Player removed.', 'great-marketrealm-tabletop'),
            'removePlayerFailed' => __('The player could not be removed.', 'great-marketrealm-tabletop'),
            'tableRemoving' => __('Pippin is erasing this road from the atlas…', 'great-marketrealm-tabletop'),
            'tableRemoved' => __('Tabletop removed.', 'great-marketrealm-tabletop'),
            'noSavedRoads' => __('Pippin has no saved roads for you yet. A Keeper can set a new Table, or an Adventurer can return after receiving a Summons.', 'great-marketrealm-tabletop'),
            'tableRemoveFailed' => __('The Tabletop could not be removed.', 'great-marketrealm-tabletop'),
            'sessionCalling' => __('The Keeper is calling the Session…', 'great-marketrealm-tabletop'),
            'sessionBegan' => __('The Session has begun — the Table remembers tonight.', 'great-marketrealm-tabletop'),
            'sessionStartFailed' => __('The Session could not be started.', 'great-marketrealm-tabletop'),
            'endSessionConfirm' => __('End the current Session? The campaign itself will remain active and can be resumed in a new Session later.', 'great-marketrealm-tabletop'),
            'sessionClosing' => __('Closing the Session ledger…', 'great-marketrealm-tabletop'),
            'sessionEnded' => __('Until next time — this Session has concluded.', 'great-marketrealm-tabletop'),
            'sessionEndFailed' => __('The Session could not be ended.', 'great-marketrealm-tabletop'),
            'selectedCharacter' => __('Selected character', 'great-marketrealm-tabletop'),
        ];
    }
}
