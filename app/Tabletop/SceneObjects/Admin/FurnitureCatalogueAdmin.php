<?php

declare(strict_types=1);

namespace GreatMarketrealmTabletop\Tabletop\SceneObjects\Admin;

use GreatMarketrealmTabletop\Tabletop\SceneObjects\FurnitureCatalogue;
use GreatMarketrealmTabletop\Tabletop\SceneObjects\FurnitureSvgSanitizer;
use GreatMarketrealmTabletop\Tabletop\SceneObjects\Models\SceneObjectCategory;
use GreatMarketrealmTabletop\Tabletop\SceneObjects\Repositories\WordPressCustomFurnitureRepository;

defined('ABSPATH') || exit;

final class FurnitureCatalogueAdmin
{
    private const PAGE = 'gmrt-furniture-catalogue';
    private const NONCE = 'gmrt_save_custom_furniture';

    public function __construct(
        private FurnitureCatalogue $catalogue,
        private WordPressCustomFurnitureRepository $custom,
        private FurnitureSvgSanitizer $svg
    ) {}

    public function register(): void
    {
        add_action('admin_menu', [$this, 'menu']);

        // Keep the normal WordPress admin-post boundary, but also register a
        // defensive admin_init dispatcher. Some hosting stacks can reach
        // admin-post.php without our named action completing its redirect;
        // admin_init runs earlier in that request and gives the Catalogue a
        // reliable, nonce-protected chance to claim its own form submission.
        add_action('admin_init', [$this, 'dispatchPostedAction']);
        add_action('admin_post_gmrt_save_custom_furniture', [$this, 'save']);
        add_action('admin_post_gmrt_delete_custom_furniture', [$this, 'delete']);
    }

    public function dispatchPostedAction(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            return;
        }

        $action = sanitize_key((string) ($_POST['action'] ?? ''));
        if ($action === 'gmrt_save_custom_furniture') {
            $this->save();
        }
        if ($action === 'gmrt_delete_custom_furniture') {
            $this->delete();
        }
    }

    public function menu(): void
    {
        add_management_page(
            'Furniture Catalogue',
            'Furniture Catalogue',
            'manage_options',
            self::PAGE,
            [$this, 'render']
        );
    }

    public function save(): void
    {
        $this->authorise();

        $originalKind = sanitize_key((string) ($_POST['original_kind'] ?? ''));
        $kind = sanitize_key((string) ($_POST['kind'] ?? ''));
        $label = sanitize_text_field((string) ($_POST['label'] ?? ''));

        if ($kind === '' || $label === '' || $this->catalogue->isBuiltIn($kind)) {
            $this->redirect('invalid');
        }
        if ($originalKind !== '' && $originalKind !== $kind && $this->catalogue->isBuiltIn($originalKind)) {
            $this->redirect('invalid');
        }

        $sprite = '';
        $existing = $originalKind !== '' ? $this->custom->find($originalKind) : null;
        if (is_array($existing)) {
            $sprite = (string) ($existing['sprite_svg'] ?? '');
        }

        if (! empty($_FILES['sprite_svg']['tmp_name'])) {
            $tmp = (string) $_FILES['sprite_svg']['tmp_name'];
            $size = (int) ($_FILES['sprite_svg']['size'] ?? 0);
            $name = strtolower((string) ($_FILES['sprite_svg']['name'] ?? ''));
            if ($size < 1 || $size > 262144 || ! str_ends_with($name, '.svg') || ! is_uploaded_file($tmp)) {
                $this->redirect('svg');
            }
            $contents = file_get_contents($tmp);
            $sprite = is_string($contents) ? $this->svg->sanitize($contents) : '';
            if ($sprite === '') {
                $this->redirect('svg');
            }
        }

        if ($sprite === '') {
            $this->redirect('svg');
        }

        $category = sanitize_key((string) ($_POST['category'] ?? SceneObjectCategory::DECORATIVE));
        if (! in_array($category, [
            SceneObjectCategory::DECORATIVE,
            SceneObjectCategory::STRUCTURAL,
            SceneObjectCategory::INTERACTIVE,
        ], true)) {
            $category = SceneObjectCategory::DECORATIVE;
        }

        $cover = sanitize_key((string) ($_POST['cover'] ?? 'none'));
        if (! in_array($cover, ['none', 'half', 'three_quarters', 'full'], true)) {
            $cover = 'none';
        }

        $interaction = sanitize_key((string) ($_POST['interaction'] ?? 'none'));
        if (! in_array($interaction, ['none', 'open_close'], true)) {
            $interaction = 'none';
        }

        $forgeTags = [];
        $allowedForgeTags = [
            'mess', 'store', 'study', 'treasure', 'quarters', 'camp', 'cache', 'lair',
            'dungeon', 'village', 'forest', 'outdoor', 'market',
        ];
        foreach (is_array($_POST['forge_tags'] ?? null) ? $_POST['forge_tags'] : [] as $tag) {
            $tag = sanitize_key((string) $tag);
            if (in_array($tag, $allowedForgeTags, true)) {
                $forgeTags[$tag] = $tag;
            }
        }
        $forgeTags = array_values($forgeTags);

        $definition = [
            'label' => $label,
            'category' => $category,
            'width_units' => max(0.25, min(8.0, (float) ($_POST['width_units'] ?? 1.0))),
            'height_units' => max(0.25, min(8.0, (float) ($_POST['height_units'] ?? 1.0))),
            'description' => sanitize_textarea_field((string) ($_POST['description'] ?? '')),
            'blocks_movement' => isset($_POST['blocks_movement']),
            'cover' => $cover,
            'blocks_vision' => isset($_POST['blocks_vision']),
            'light_occlusion' => max(0.0, min(1.0, (float) ($_POST['light_occlusion'] ?? 0.0))),
            'interaction' => $interaction,
            'mimic_capable' => isset($_POST['mimic_capable']),
            'forge_enabled' => isset($_POST['forge_enabled']),
            'forge_tags' => $forgeTags,
            'sprite_svg' => $sprite,
            'custom' => true,
        ];

        if ($originalKind !== '' && $originalKind !== $kind) {
            $this->custom->remove($originalKind);
        }
        $this->custom->save($kind, $definition);
        $this->redirect('saved');
    }

    public function delete(): void
    {
        $this->authorise();
        $kind = sanitize_key((string) ($_POST['kind'] ?? ''));
        if ($kind !== '' && ! $this->catalogue->isBuiltIn($kind)) {
            $this->custom->remove($kind);
        }
        $this->redirect('deleted');
    }

    public function render(): void
    {
        if (! current_user_can('manage_options')) {
            wp_die('You do not have permission to manage the Furniture Catalogue.');
        }

        $custom = $this->custom->all();
        $builtIns = $this->catalogue->builtIns();
        $editingKind = sanitize_key((string) ($_GET['edit'] ?? ''));
        $editing = $editingKind !== '' ? ($custom[$editingKind] ?? null) : null;
        $notice = sanitize_key((string) ($_GET['gmrt_notice'] ?? ''));

        ?>
        <div class="wrap">
            <h1>Furniture Catalogue</h1>
            <p>Built-in furnishings are code-owned. Add SVG-backed custom Scene Objects below; they immediately join the Keeper's Furniture Palette.</p>

            <?php if ($notice === 'saved') : ?><div class="notice notice-success"><p>Furniture saved. Pippin has updated the catalogue.</p></div><?php endif; ?>
            <?php if ($notice === 'deleted') : ?><div class="notice notice-success"><p>Custom furniture removed.</p></div><?php endif; ?>
            <?php if ($notice === 'invalid') : ?><div class="notice notice-error"><p>Please provide a unique custom key and label. Built-in keys cannot be replaced.</p></div><?php endif; ?>
            <?php if ($notice === 'svg') : ?><div class="notice notice-error"><p>Please upload a valid SVG sprite smaller than 256 KB.</p></div><?php endif; ?>

            <h2>Built-in furniture</h2>
            <table class="widefat striped"><thead><tr><th>Key</th><th>Label</th><th>Category</th><th>Size</th><th>Cover</th></tr></thead><tbody>
            <?php foreach ($builtIns as $kind => $definition) : ?>
                <tr>
                    <td><code><?php echo esc_html($kind); ?></code></td>
                    <td><?php echo esc_html((string) $definition['label']); ?></td>
                    <td><?php echo esc_html((string) $definition['category']); ?></td>
                    <td><?php echo esc_html((string) $definition['width_units'] . ' × ' . (string) $definition['height_units']); ?></td>
                    <td><?php echo esc_html((string) $definition['cover']); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody></table>

            <h2>Custom furniture</h2>
            <table class="widefat striped"><thead><tr><th>Sprite</th><th>Key</th><th>Label</th><th>Tactical traits</th><th>Actions</th></tr></thead><tbody>
            <?php if ($custom === []) : ?>
                <tr><td colspan="5">No custom furniture yet. The Suspicious Ottoman awaits.</td></tr>
            <?php else : foreach ($custom as $kind => $definition) : ?>
                <tr>
                    <td><div style="width:48px;height:48px"><?php echo (string) ($definition['sprite_svg'] ?? ''); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div></td>
                    <td><code><?php echo esc_html($kind); ?></code></td>
                    <td><?php echo esc_html((string) ($definition['label'] ?? $kind)); ?></td>
                    <td>
                        <?php echo ! empty($definition['blocks_movement']) ? 'Movement · ' : ''; ?><?php echo esc_html((string) ($definition['cover'] ?? 'none')); ?> cover<?php echo ! empty($definition['blocks_vision']) ? ' · Vision' : ''; ?>
                        <?php if (! empty($definition['forge_enabled'])) : ?>
                            <br><small>Forge: <?php echo esc_html(implode(', ', is_array($definition['forge_tags'] ?? null) ? $definition['forge_tags'] : [])); ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a class="button" href="<?php echo esc_url(add_query_arg(['page' => self::PAGE, 'edit' => $kind], admin_url('tools.php'))); ?>">Edit</a>
                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline">
                            <input type="hidden" name="action" value="gmrt_delete_custom_furniture">
                            <input type="hidden" name="kind" value="<?php echo esc_attr($kind); ?>">
                            <?php wp_nonce_field(self::NONCE); ?>
                            <button class="button button-link-delete" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody></table>

            <h2><?php echo is_array($editing) ? 'Edit custom furniture' : 'Add custom furniture'; ?></h2>
            <form method="post" enctype="multipart/form-data" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="gmrt_save_custom_furniture">
                <input type="hidden" name="original_kind" value="<?php echo esc_attr($editingKind); ?>">
                <?php wp_nonce_field(self::NONCE); ?>
                <table class="form-table" role="presentation">
                    <tr><th><label for="gmrt-kind">Key</label></th><td><input id="gmrt-kind" name="kind" type="text" class="regular-text" required value="<?php echo esc_attr($editingKind); ?>"><p class="description">Lowercase slug, e.g. <code>suspicious-ottoman</code>.</p></td></tr>
                    <tr><th><label for="gmrt-label">Label</label></th><td><input id="gmrt-label" name="label" type="text" class="regular-text" required value="<?php echo esc_attr((string) ($editing['label'] ?? '')); ?>"></td></tr>
                    <tr><th><label for="gmrt-svg">SVG sprite</label></th><td><input id="gmrt-svg" name="sprite_svg" type="file" accept=".svg,image/svg+xml" <?php echo is_array($editing) ? '' : 'required'; ?>><p class="description">Maximum 256 KB. Scripts, external references, event handlers and embedded CSS are removed/rejected.</p></td></tr>
                    <tr><th>Size (grid units)</th><td><input name="width_units" type="number" min=".25" max="8" step=".25" value="<?php echo esc_attr((string) ($editing['width_units'] ?? 1)); ?>"> × <input name="height_units" type="number" min=".25" max="8" step=".25" value="<?php echo esc_attr((string) ($editing['height_units'] ?? 1)); ?>"></td></tr>
                    <tr><th><label for="gmrt-category">Category</label></th><td><select id="gmrt-category" name="category"><?php foreach ([SceneObjectCategory::DECORATIVE, SceneObjectCategory::STRUCTURAL, SceneObjectCategory::INTERACTIVE] as $category) : ?><option value="<?php echo esc_attr($category); ?>" <?php selected((string) ($editing['category'] ?? SceneObjectCategory::DECORATIVE), $category); ?>><?php echo esc_html(ucwords(str_replace('_', ' ', $category))); ?></option><?php endforeach; ?></select></td></tr>
                    <tr><th><label for="gmrt-cover">Cover</label></th><td><select id="gmrt-cover" name="cover"><?php foreach (['none','half','three_quarters','full'] as $cover) : ?><option value="<?php echo esc_attr($cover); ?>" <?php selected((string) ($editing['cover'] ?? 'none'), $cover); ?>><?php echo esc_html(ucwords(str_replace('_', ' ', $cover))); ?></option><?php endforeach; ?></select></td></tr>
                    <tr><th><label for="gmrt-occlusion">Light occlusion</label></th><td><input id="gmrt-occlusion" name="light_occlusion" type="number" min="0" max="1" step=".05" value="<?php echo esc_attr((string) ($editing['light_occlusion'] ?? 0)); ?>"></td></tr>
                    <tr><th><label for="gmrt-interaction">Interaction</label></th><td><select id="gmrt-interaction" name="interaction"><option value="none" <?php selected((string) ($editing['interaction'] ?? 'none'), 'none'); ?>>None</option><option value="open_close" <?php selected((string) ($editing['interaction'] ?? 'none'), 'open_close'); ?>>Open / Close</option></select></td></tr>
                    <tr><th>Rules</th><td>
                        <?php foreach ([
                            'blocks_movement' => 'Blocks movement',
                            'blocks_vision' => 'Blocks vision',
                            'mimic_capable' => 'Mimic-capable',
                            'forge_enabled' => 'Available to Dungeon Forge',
                        ] as $field => $label) : ?>
                            <label style="display:block"><input type="checkbox" name="<?php echo esc_attr($field); ?>" <?php checked(! empty($editing[$field])); ?>> <?php echo esc_html($label); ?></label>
                        <?php endforeach; ?>
                    </td></tr>
                    <tr><th>Dungeon Forge labels</th><td>
                        <?php
                        $selectedForgeTags = is_array($editing['forge_tags'] ?? null)
                            ? array_map('sanitize_key', $editing['forge_tags'])
                            : [];
                        $forgeTagGroups = [
                            'Room purpose' => [
                                'mess' => 'Mess / dining',
                                'store' => 'Store room',
                                'study' => 'Study / archive',
                                'treasure' => 'Treasure room',
                                'quarters' => 'Quarters',
                                'camp' => 'Camp',
                                'cache' => 'Cache',
                                'lair' => 'Boss lair',
                            ],
                            'Environment' => [
                                'dungeon' => 'Dungeon',
                                'village' => 'Village',
                                'forest' => 'Forest',
                                'outdoor' => 'Outdoor',
                                'market' => 'Market',
                            ],
                        ];
                        ?>
                        <p class="description">Pippin may use an opted-in furnishing when any selected label matches the generated room/context. Leave all labels empty to keep it out of automatic placement.</p>
                        <?php foreach ($forgeTagGroups as $groupLabel => $forgeTags) : ?>
                            <fieldset style="margin:10px 0">
                                <legend><strong><?php echo esc_html($groupLabel); ?></strong></legend>
                                <?php foreach ($forgeTags as $tag => $tagLabel) : ?>
                                    <label style="display:inline-block;min-width:170px;margin:3px 10px 3px 0">
                                        <input type="checkbox" name="forge_tags[]" value="<?php echo esc_attr($tag); ?>" <?php checked(in_array($tag, $selectedForgeTags, true)); ?>>
                                        <?php echo esc_html($tagLabel); ?>
                                    </label>
                                <?php endforeach; ?>
                            </fieldset>
                        <?php endforeach; ?>
                    </td></tr>
                    <tr><th><label for="gmrt-description">Description</label></th><td><textarea id="gmrt-description" name="description" rows="4" class="large-text"><?php echo esc_textarea((string) ($editing['description'] ?? '')); ?></textarea></td></tr>
                </table>
                <?php submit_button(is_array($editing) ? 'Update furniture' : 'Add furniture'); ?>
            </form>
        </div>
        <?php
    }

    private function authorise(): void
    {
        if (! current_user_can('manage_options')) {
            wp_die('You do not have permission to manage the Furniture Catalogue.');
        }
        check_admin_referer(self::NONCE);
    }

    private function redirect(string $notice): never
    {
        wp_safe_redirect(add_query_arg(
            ['page' => self::PAGE, 'gmrt_notice' => sanitize_key($notice)],
            admin_url('tools.php')
        ));
        exit;
    }
}
