<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tests\Unit\Tabletop\SceneObjects;

use PHPUnit\Framework\TestCase;

final class FurnitureAdminPostDispatchRegressionTest extends TestCase
{
    private function source(string $path): string
    {
        return (string) file_get_contents(dirname(__DIR__, 4) . '/' . $path);
    }

    public function test_catalogue_keeps_normal_admin_post_hooks(): void
    {
        $admin = $this->source('app/Tabletop/SceneObjects/Admin/FurnitureCatalogueAdmin.php');

        self::assertStringContainsString(
            "add_action('admin_post_gmrt_save_custom_furniture', [\$this, 'save']);",
            $admin
        );
        self::assertStringContainsString(
            "add_action('admin_post_gmrt_delete_custom_furniture', [\$this, 'delete']);",
            $admin
        );
    }

    public function test_admin_init_can_claim_catalogue_post_actions_before_admin_post_dispatch(): void
    {
        $admin = $this->source('app/Tabletop/SceneObjects/Admin/FurnitureCatalogueAdmin.php');

        self::assertStringContainsString(
            "add_action('admin_init', [\$this, 'dispatchPostedAction']);",
            $admin
        );
        self::assertStringContainsString('public function dispatchPostedAction(): void', $admin);
        self::assertStringContainsString("(\$_SERVER['REQUEST_METHOD'] ?? '') !== 'POST'", $admin);
        self::assertStringContainsString("(\$_POST['action'] ?? '')", $admin);
        self::assertStringContainsString("\$action === 'gmrt_save_custom_furniture'", $admin);
        self::assertStringContainsString("\$action === 'gmrt_delete_custom_furniture'", $admin);
    }

    public function test_dispatch_reuses_existing_nonce_and_capability_protected_handlers(): void
    {
        $admin = $this->source('app/Tabletop/SceneObjects/Admin/FurnitureCatalogueAdmin.php');

        self::assertStringContainsString('$this->save();', $admin);
        self::assertStringContainsString('$this->delete();', $admin);
        self::assertStringContainsString('private function authorise(): void', $admin);
        self::assertStringContainsString("current_user_can('manage_options')", $admin);
        self::assertStringContainsString('check_admin_referer(self::NONCE)', $admin);
    }

    public function test_successful_save_still_redirects_back_to_the_catalogue(): void
    {
        $admin = $this->source('app/Tabletop/SceneObjects/Admin/FurnitureCatalogueAdmin.php');

        self::assertStringContainsString('$this->redirect(\'saved\');', $admin);
        self::assertStringContainsString("admin_url('tools.php')", $admin);
        self::assertStringContainsString("'page' => self::PAGE", $admin);
    }
}
