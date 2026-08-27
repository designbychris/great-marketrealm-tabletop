<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tables\Memberships\Delivery;

use GreatMarketrealmTabletop\Tables\Contracts\TableRepository;
use GreatMarketrealmTabletop\Tables\Memberships\Contracts\TableMemberIdentityDirectory;
use RuntimeException;

defined('ABSPATH') || exit;

final class WordPressTableInvitationDelivery
{
    public function __construct(
        private TableRepository $tables,
        private TableMemberIdentityDirectory $identities
    ) {}

    /** @return array{email_sent:bool,invite_url:string} */
    public function deliver(
        string $tableId,
        int $invitedUserId,
        int $dungeonMasterUserId
    ): array {
        $table = $this->tables->find($tableId);

        if ($table === null) {
            throw new RuntimeException('The requested Table could not be found.');
        }

        $player = $this->identities->forUser($invitedUserId);
        $dungeonMaster = $this->identities->forUser($dungeonMasterUserId);
        $inviteUrl = $this->inviteUrl($tableId);
        $emailSent = $this->sendEmail(
            (string) ($player['email'] ?? ''),
            (string) ($player['display_name'] ?? 'Adventurer'),
            (string) ($dungeonMaster['display_name'] ?? 'The Dungeon Master'),
            $table->name(),
            $inviteUrl
        );

        if (function_exists('do_action')) {
            do_action('gmrt_table_invitation_created', [
                'table_id' => $tableId,
                'table_name' => $table->name(),
                'invited_user_id' => $invitedUserId,
                'dungeon_master_user_id' => $dungeonMasterUserId,
                'invite_url' => $inviteUrl,
                'email_sent' => $emailSent,
            ]);
        }

        return [
            'email_sent' => $emailSent,
            'invite_url' => $inviteUrl,
        ];
    }

    private function inviteUrl(string $tableId): string
    {
        $path = '/tabletop/' . rawurlencode($tableId) . '/';

        return function_exists('home_url')
            ? (string) home_url($path)
            : $path;
    }

    private function sendEmail(
        string $email,
        string $playerName,
        string $dungeonMasterName,
        string $tableName,
        string $inviteUrl
    ): bool {
        if (
            $email === ''
            || ! function_exists('wp_mail')
            || (function_exists('is_email') && ! is_email($email))
        ) {
            return false;
        }

        $subject = sprintf(
            'You are summoned to %s',
            $tableName
        );
        $message = implode("\n\n", [
            sprintf('Hello %s,', $playerName),
            sprintf(
                '%s has invited you to take a seat at "%s" in The Great Marketrealm Tabletop.',
                $dungeonMasterName,
                $tableName
            ),
            'Take My Seat:',
            $inviteUrl,
            'Sign in with the WordPress account that received this invitation. Your seat remains reserved until you accept it.',
        ]);

        $headers = [];
        $adminEmail = function_exists('get_option')
            ? trim((string) get_option('admin_email', ''))
            : '';

        if (
            $adminEmail !== ''
            && (! function_exists('is_email') || is_email($adminEmail))
        ) {
            $headers[] = 'From: Great Marketrealm <' . $adminEmail . '>';
        }

        return (bool) wp_mail($email, $subject, $message, $headers);
    }
}
