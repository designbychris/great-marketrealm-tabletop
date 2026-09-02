<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\Presentation;

use GreatMarketrealmTabletop\Tabletop\Exceptions\TabletopAccessDenied;
use GreatMarketrealmTabletop\Tabletop\Services\TabletopChamber;
use Throwable;
use GreatMarketrealmTabletop\Tables\Memberships\Contracts\TableMembershipRepository;
use GreatMarketrealmTabletop\Tables\Memberships\Contracts\TableMemberIdentityDirectory;
use GreatMarketrealmTabletop\Tables\Memberships\Presentation\TableMemberProjector;
use GreatMarketrealmTabletop\Tables\Memberships\Models\TableMemberStatus;
use GreatMarketrealmTabletop\Tables\Services\TableRegistryFactory;
use GreatMarketrealmTabletop\Tabletop\Campaigns\WordPressDungeonMasterPolicy;

defined('ABSPATH') || exit;

final class TabletopShortcode
{
    private ?string $doorLoginError = null;

    public const TAG = 'great_marketrealm_tabletop';
    public const LOGIN_NONCE_ACTION = 'gmrt_tabletop_frontend_login';

    public function __construct(
        private TabletopChamber $chamber,
        private TabletopChamberRenderer $renderer,
        private ?TableMembershipRepository $members = null,
        private ?TableMemberIdentityDirectory $identities = null
    ) {}

    /** @param array<string,mixed>|string $attributes */
    public function render(
        array|string $attributes = []
    ): string {
        $attributes = shortcode_atts(
            ['table' => ''],
            is_array($attributes) ? $attributes : [],
            self::TAG
        );

        $this->enqueueAssets();

        if (! is_user_logged_in()) {
            return $this->renderer->render(
                null,
                null,
                false,
                null,
                [
                    'action_url' => $this->returnUrl(),
                    'nonce' => wp_create_nonce(self::LOGIN_NONCE_ACTION),
                    'error' => $this->doorLoginError,
                    'art_url' => GMRT_URL . 'assets/images/pippin-peppercorn-cartographer.png',
                ]
            );
        }

        $tableId = $this->tableId(
            (string) ($attributes['table'] ?? '')
        );

        if ($tableId === '') {
            $userId = get_current_user_id();
            $projector = $this->identities !== null
                ? new TableMemberProjector($this->identities)
                : null;
            $registry = TableRegistryFactory::make();
            $campaigns = [];

            foreach ($registry->all() as $table) {
                $member = $this->members?->find($table->id(), $userId);
                $isOwner = $table->dungeonMasterUserId() === $userId;
                $memberStatus = $member?->status() ?? '';

                if (! $isOwner && ! in_array($memberStatus, [TableMemberStatus::ACTIVE, TableMemberStatus::INVITED], true)) {
                    continue;
                }

                $roster = [];
                if ($this->members !== null) {
                    foreach ($this->members->forTable($table->id()) as $rosterMember) {
                        $roster[] = $projector !== null
                            ? $projector->project($rosterMember)
                            : $rosterMember->toArray();
                    }
                }

                $campaigns[] = [
                    'id' => $table->id(),
                    'name' => $table->name(),
                    'description' => $table->description(),
                    'status' => $table->status(),
                    'roster' => $roster,
                    'is_owner' => $isOwner,
                    'membership_status' => $memberStatus,
                    'last_visited' => $table->lastHeartbeatAt()?->format(DATE_ATOM),
                ];
            }

            usort($campaigns, static function (array $left, array $right): int {
                return strcmp((string) ($right['last_visited'] ?? ''), (string) ($left['last_visited'] ?? ''));
            });

            return $this->renderer->render(
                null,
                null,
                true,
                null,
                null,
                [
                    'may_create' => (new WordPressDungeonMasterPolicy())->mayCreate($userId),
                    'tables' => $campaigns,
                    'art_url' => GMRT_URL . 'assets/images/pippin-peppercorn-cartographer.png',
                ]
            );
        }

        try {
            $state = $this->chamber->state(
                $tableId,
                get_current_user_id()
            );

            return $this->renderer->render($state);
        } catch (TabletopAccessDenied $exception) {
            $member = $this->members?->find(
                $tableId,
                get_current_user_id()
            );

            if ($member?->status() === TableMemberStatus::INVITED) {
                return $this->renderer->render(
                    null,
                    null,
                    false,
                    [
                        'table_id' => $tableId,
                        'role' => $member->role(),
                        'status' => $member->status(),
                    ]
                );
            }

            return $this->renderer->render(
                null,
                $exception->getMessage()
            );
        } catch (Throwable $exception) {
            return $this->renderer->render(
                null,
                $exception->getMessage()
            );
        }
    }


    public function handleDoorLogin(): void
    {
        if (is_user_logged_in() || ! $this->isDoorLoginRequest()) {
            return;
        }

        $this->doorLoginError = $this->authenticateAtDoor();

        if ($this->doorLoginError === null) {
            wp_safe_redirect($this->returnUrl());
            exit;
        }
    }

    private function isDoorLoginRequest(): bool
    {
        return isset($_POST['gmrt_tabletop_login'])
            && (string) $_POST['gmrt_tabletop_login'] === '1';
    }

    private function authenticateAtDoor(): ?string
    {
        $nonce = isset($_POST['gmrt_tabletop_login_nonce'])
            ? sanitize_text_field(wp_unslash((string) $_POST['gmrt_tabletop_login_nonce']))
            : '';

        if ($nonce === '' || ! wp_verify_nonce($nonce, self::LOGIN_NONCE_ACTION)) {
            return 'The Door could not verify that request. Please try again.';
        }

        $login = isset($_POST['log'])
            ? sanitize_text_field(wp_unslash((string) $_POST['log']))
            : '';
        $password = isset($_POST['pwd'])
            ? (string) wp_unslash($_POST['pwd'])
            : '';
        $remember = ! empty($_POST['rememberme']);

        if ($login === '' || $password === '') {
            return 'Enter your Companion username or email and password to open the Door.';
        }

        $user = wp_signon(
            [
                'user_login' => $login,
                'user_password' => $password,
                'remember' => $remember,
            ],
            is_ssl()
        );

        if (is_wp_error($user)) {
            return 'The Door stayed shut. Check your username or email and password, then try again.';
        }

        wp_set_current_user((int) $user->ID);

        return null;
    }

    private function tableId(string $attribute): string
    {
        $attribute = sanitize_text_field($attribute);

        if ($attribute !== '') {
            return $attribute;
        }

        return sanitize_text_field(
            (string) (
                $_GET['table']
                ?? $_GET['gmrt_table']
                ?? ''
            )
        );
    }

    private function returnUrl(): string
    {
        $requestUri = isset($_SERVER['REQUEST_URI'])
            ? wp_unslash((string) $_SERVER['REQUEST_URI'])
            : '/';

        return esc_url_raw(home_url($requestUri));
    }

    private function enqueueAssets(): void
    {
        if (is_user_logged_in()) {
            wp_enqueue_media();
        }

        wp_enqueue_style(
            'gmrt-tabletop',
            GMRT_URL . 'assets/css/tabletop.css',
            [],
            GMRT_VERSION
        );

        wp_enqueue_script(
            'gmrt-tabletop',
            GMRT_URL . 'assets/js/tabletop.js',
            [],
            GMRT_VERSION,
            true
        );

        wp_localize_script(
            'gmrt-tabletop',
            'gmrtTabletop',
            [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce(
                    \GreatMarketrealmTabletop\Tabletop\Http\TabletopAjaxController::NONCE_ACTION
                ),
            ]
        );
    }
}
