<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Http;

use GreatMarketrealmTabletop\Tables\Memberships\Contracts\TableMemberIdentityDirectory;
use GreatMarketrealmTabletop\Tables\Memberships\Contracts\TableMembershipRepository;
use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMemberStatus;
use GreatMarketrealmTabletop\Tables\Memberships\Presentation\TableMemberProjector;
use GreatMarketrealmTabletop\Tables\Memberships\Services\TableGathering;
use GreatMarketrealmTabletop\Tables\Memberships\Delivery\WordPressTableInvitationDelivery;
use Throwable;

defined('ABSPATH') || exit;

final class GatheringAjaxController
{
    public function __construct(
        private TableGathering $gathering,
        private TableMembershipRepository $members,
        private TableMemberIdentityDirectory $identities,
        private WordPressTableInvitationDelivery $delivery
    ) {}

    public function invite(): void
    {
        $this->guard();

        try {
            $tableId = $this->tableId();
            $this->assertDungeonMaster($tableId);
            $identifier = sanitize_text_field(
                (string) ($_POST['player'] ?? '')
            );
            $userId = $this->identities->resolve($identifier);

            if ($userId === null) {
                throw new \RuntimeException(
                    'No WordPress user could be found with that username, email, or user ID.'
                );
            }

            $member = $this->gathering->invitePlayer($tableId, $userId);
            $projection = (new TableMemberProjector($this->identities))
                ->project($member);
            $delivery = $this->delivery->deliver(
                $tableId,
                $userId,
                get_current_user_id()
            );
            $message = $projection['display_name'] . ' has been summoned to the Table.';

            if (! $delivery['email_sent']) {
                $message .= ' Their seat is reserved, but WordPress could not send the email.';
            }

            wp_send_json_success([
                'message' => $message,
                'member' => $projection,
                'delivery' => $delivery,
            ]);
        } catch (Throwable $exception) {
            wp_send_json_error(['message' => $exception->getMessage()], 400);
        }
    }

    public function accept(): void
    {
        $this->guard();

        try {
            $member = $this->gathering->join(
                $this->tableId(),
                get_current_user_id()
            );

            wp_send_json_success([
                'message' => 'Your seat at the Table is ready.',
                'member' => $member->toArray(),
            ]);
        } catch (Throwable $exception) {
            wp_send_json_error(['message' => $exception->getMessage()], 400);
        }
    }

    private function guard(): void
    {
        check_ajax_referer(TabletopAjaxController::NONCE_ACTION, 'nonce');

        if (! is_user_logged_in()) {
            wp_send_json_error(['message' => 'Please sign in first.'], 401);
        }
    }

    private function assertDungeonMaster(string $tableId): void
    {
        $member = $this->members->find($tableId, get_current_user_id());

        if (
            $member === null
            || ! $member->isDungeonMaster()
            || $member->status() !== TableMemberStatus::ACTIVE
        ) {
            throw new \RuntimeException(
                'Only the active Dungeon Master may invite players.'
            );
        }
    }

    private function tableId(): string
    {
        $tableId = sanitize_text_field((string) ($_POST['table_id'] ?? ''));

        if ($tableId === '') {
            throw new \RuntimeException('A Table ID is required.');
        }

        return $tableId;
    }
}
