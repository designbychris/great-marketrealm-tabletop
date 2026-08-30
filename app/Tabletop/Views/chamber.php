<?php

use GreatMarketrealmTabletop\Tabletop\Models\TabletopChamberState;
use GreatMarketrealmTabletop\Tabletop\Presentation\CompanionTokenImageSource;
use GreatMarketrealmTabletop\Tables\Memberships\Models\TableColourPalette;

defined('ABSPATH') || exit;

/** @var TabletopChamberState|null $state */
/** @var string|null $message */
/** @var bool $canPrepareTestTable */
/** @var array<string,mixed>|null $invitation */

$table = $state?->table() ?? [];
$viewer = $state?->viewer() ?? [];
$scene = $state?->scene();
$scenes = $state?->scenes() ?? [];
$preparation = $state?->preparation() ?? [];
$thresholds = $state?->thresholds() ?? [];
$bestiary = $state?->bestiary() ?? [];
$isPreparingScene = ! empty($preparation['active']);
$tokens = $state?->tokens() ?? [];
$members = $state?->members() ?? [];
$encounter = $state?->encounter();
$vitality = $state?->vitality() ?? [];
$deathSaves = $state?->deathSaves() ?? [];
$conditions = $state?->conditions() ?? [];
$battleLog = $state?->battleLog() ?? [];
$chamberLog = $state?->chamberLog() ?? [];
$chronicleLog = $encounter !== null ? $battleLog : $chamberLog;
$combatantStates = $state?->combatantStates() ?? [];
$arsenals = $state?->arsenals() ?? [];
$fog = $state?->fog() ?? [];
$visionLayer = $state?->visionLayer() ?? [];
$integrations = $state?->integrations() ?? [];
$companion = is_array($integrations['companion'] ?? null)
    ? $integrations['companion']
    : [];
$adventurer = is_array($companion['selected_character'] ?? null)
    ? $companion['selected_character']
    : null;
$viewerColour = (string) ($viewer['table_colour'] ?? 'market-teal');
$viewerColourHex = (string) ($viewer['table_colour_hex'] ?? TableColourPalette::hex($viewerColour));
$adventurerPlay = is_array($adventurer['play'] ?? null)
    ? $adventurer['play']
    : [];


$tokenLabels = [];
foreach ($tokens as $token) {
    $tokenId = (string) ($token['id'] ?? '');
    if ($tokenId !== '') {
        $tokenLabels[$tokenId] = (string) (
            $token['label'] ?? 'Unknown combatant'
        );
    }
}

$sceneImage = $scene !== null
    ? wp_get_attachment_image_url(
        (int) ($scene['map_attachment_id'] ?? 0),
        'full'
    )
    : false;
?>
<main
    class="gmrt-chamber"
    id="main-content"
    data-table-id="<?php echo esc_attr(
        (string) ($table['id'] ?? ($invitation['table_id'] ?? ''))
    ); ?>"
    data-viewer-role="<?php echo esc_attr(
        (string) ($viewer['role'] ?? '')
    ); ?>"
    data-viewer-user-id="<?php echo esc_attr(
        (string) ($viewer['user_id'] ?? '')
    ); ?>"
    data-sync-revision="<?php echo esc_attr(
        $state?->syncRevision() ?? ''
    ); ?>"
    data-scene-id="<?php echo esc_attr((string) ($scene['id'] ?? '')); ?>"
    data-preparation-scene-id="<?php echo esc_attr((string) ($preparation['scene_id'] ?? '')); ?>"
>
    <?php if ($adventurer !== null && $adventurerPlay !== []) : ?>
        <?php
        $satchelToken = is_array($adventurer['token'] ?? null) ? $adventurer['token'] : [];
        $satchelImage = CompanionTokenImageSource::escaped((string) ($satchelToken['image_url'] ?? ''));
        $satchelHp = is_array($adventurerPlay['hit_points'] ?? null) ? $adventurerPlay['hit_points'] : [];
        $satchelAbilities = is_array($adventurerPlay['abilities'] ?? null) ? $adventurerPlay['abilities'] : [];
        $satchelSaves = is_array($adventurerPlay['saving_throws'] ?? null) ? $adventurerPlay['saving_throws'] : [];
        $satchelSkills = is_array($adventurerPlay['skills'] ?? null) ? $adventurerPlay['skills'] : [];
        $satchelAttacks = is_array($adventurerPlay['attacks'] ?? null) ? $adventurerPlay['attacks'] : [];
        $satchelSpellcasting = is_array($adventurerPlay['spellcasting'] ?? null) ? $adventurerPlay['spellcasting'] : [];
        $satchelSpells = is_array($satchelSpellcasting['spells'] ?? null) ? $satchelSpellcasting['spells'] : [];
        $satchelSlots = is_array($satchelSpellcasting['slots'] ?? null) ? $satchelSpellcasting['slots'] : [];
        $abilityLabels = ['strength'=>'STR','dexterity'=>'DEX','constitution'=>'CON','intelligence'=>'INT','wisdom'=>'WIS','charisma'=>'CHA'];
        ?>
        <aside class="gmrt-satchel" style="--gmrt-fellowship-colour: <?php echo esc_attr($viewerColourHex); ?>" data-adventurer-satchel data-open="false" aria-label="Adventurer's Satchel">
            <button class="gmrt-satchel__toggle" type="button" data-satchel-toggle aria-expanded="false" aria-controls="gmrt-adventurer-satchel-panel">
                <span aria-hidden="true">🎒</span><span>Satchel</span>
            </button>
            <div class="gmrt-satchel__panel" id="gmrt-adventurer-satchel-panel">
                <header class="gmrt-satchel__identity">
                    <?php if ($satchelImage !== '') : ?><img src="<?php echo $satchelImage; ?>" alt="" aria-hidden="true"><?php endif; ?>
                    <div><p class="gmrt-chamber__eyebrow">Adventurer's Satchel</p><h2><?php echo esc_html((string) ($adventurer['name'] ?? 'Adventurer')); ?></h2><p>Level <?php echo esc_html((string) ($adventurer['level'] ?? '')); ?> <?php echo esc_html((string) ($adventurer['race'] ?? '')); ?> <?php echo esc_html((string) ($adventurer['class'] ?? '')); ?></p></div>
                </header>
                <div class="gmrt-satchel__measures">
                    <div><span>AC</span><strong><?php echo esc_html((string) ($adventurerPlay['armour_class'] ?? '—')); ?></strong></div>
                    <div class="gmrt-satchel__hp" data-adventuring-measures>
                        <span>HP</span>
                        <strong><span data-current-hp><?php echo esc_html((string) ($satchelHp['current'] ?? '—')); ?></span>/<span data-maximum-hp><?php echo esc_html((string) ($satchelHp['maximum'] ?? '—')); ?></span></strong>
                        <small><span data-temporary-hp><?php echo esc_html((string) ($satchelHp['temporary'] ?? 0)); ?></span> temp</small>
                        <button type="button" data-adventuring-measures-toggle aria-expanded="false">Adjust</button>
                        <form data-adventuring-measures-form hidden>
                            <label>Current HP<input type="number" name="current_hp" min="0" max="<?php echo esc_attr((string) ($satchelHp['maximum'] ?? 0)); ?>" value="<?php echo esc_attr((string) ($satchelHp['current'] ?? 0)); ?>"></label>
                            <label>Temporary HP<input type="number" name="temporary_hp" min="0" max="999" value="<?php echo esc_attr((string) ($satchelHp['temporary'] ?? 0)); ?>"></label>
                            <p>Maximum HP: <strong><?php echo esc_html((string) ($satchelHp['maximum'] ?? '—')); ?></strong> <small>Companion-certified</small></p>
                            <button type="submit">Save Measures</button>
                            <span role="status" data-adventuring-measures-status></span>
                        </form>
                    </div>
                    <div><span>Speed</span><strong><?php echo esc_html((string) ($adventurerPlay['speed'] ?? '—')); ?> ft</strong></div>
                    <button type="button" class="gmrt-quick-roll gmrt-quick-roll--measure" data-quick-roll data-roll-kind="initiative" data-roll-key="initiative"><span>Initiative</span><strong><?php echo esc_html(sprintf('%+d', (int) ($adventurerPlay['initiative'] ?? 0))); ?></strong><small aria-hidden="true">🎲</small></button>
                    <div><span>Proficiency</span><strong><?php echo esc_html(sprintf('%+d', (int) ($adventurerPlay['proficiency_bonus'] ?? 0))); ?></strong></div>
                    <div><span>Passive Perception</span><strong><?php echo esc_html((string) ($adventurerPlay['passive_perception'] ?? '—')); ?></strong></div>
                    <?php $darkvision = max(0, (int) ($adventurerPlay['senses']['darkvision'] ?? 0)); ?>
                    <div><span>Sight</span><strong class="gmrt-sight-measure" data-sight-measure><?php echo esc_html($darkvision > 0 ? 'Darkvision ' . $darkvision . ' ft' : 'Normal'); ?></strong></div>
                    <?php $carriedLight = ! empty($fog['viewer_carried_light']); ?>
                    <div class="gmrt-lantern-measure"><span>Lantern</span><strong data-lantern-state><?php echo $carriedLight ? 'Burning' : 'Doused'; ?></strong><button type="button" data-toggle-carried-light><?php echo $carriedLight ? 'Douse Torch' : 'Light Torch'; ?></button><button type="button" data-dropped-light-action="drop"<?php echo $carriedLight ? '' : ' hidden'; ?>>Drop Torch</button><button type="button" data-dropped-light-action="pickup"<?php echo $carriedLight ? ' hidden' : ''; ?>>Pick Up Nearby Torch</button><small role="status" data-lantern-status></small></div>
                </div>
                <section class="gmrt-satchel__section"><h3>Abilities</h3><div class="gmrt-satchel__abilities">
                    <?php foreach ($abilityLabels as $key => $label) : $ability = is_array($satchelAbilities[$key] ?? null) ? $satchelAbilities[$key] : []; ?>
                    <button type="button" class="gmrt-quick-roll" data-quick-roll data-roll-kind="ability" data-roll-key="<?php echo esc_attr($key); ?>"><span><?php echo esc_html($label); ?></span><strong><?php echo esc_html((string) ($ability['score'] ?? '—')); ?></strong><small><?php echo esc_html(sprintf('%+d', (int) ($ability['modifier'] ?? 0))); ?> 🎲</small></button>
                    <?php endforeach; ?>
                </div></section>
                <details class="gmrt-satchel__section"><summary>Saving Throws</summary><div class="gmrt-satchel__list">
                    <?php foreach ($abilityLabels as $key => $label) : $save = is_array($satchelSaves[$key] ?? null) ? $satchelSaves[$key] : []; ?><button type="button" class="gmrt-quick-roll gmrt-quick-roll--row" data-quick-roll data-roll-kind="save" data-roll-key="<?php echo esc_attr($key); ?>"><span><?php echo ! empty($save['proficient']) ? '◆ ' : ''; ?><?php echo esc_html($label); ?></span><strong><?php echo esc_html(sprintf('%+d', (int) ($save['modifier'] ?? 0))); ?> 🎲</strong></button><?php endforeach; ?>
                </div></details>
                <details class="gmrt-satchel__section"><summary>Skills</summary><div class="gmrt-satchel__list">
                    <?php foreach ($satchelSkills as $key => $skill) : if (! is_array($skill)) continue; ?><button type="button" class="gmrt-quick-roll gmrt-quick-roll--row" data-quick-roll data-roll-kind="skill" data-roll-key="<?php echo esc_attr((string) $key); ?>"><span><?php echo ! empty($skill['expertise']) ? '◆◆ ' : (! empty($skill['proficient']) ? '◆ ' : ''); ?><?php echo esc_html(ucwords(str_replace('-', ' ', (string) $key))); ?></span><strong><?php echo esc_html(sprintf('%+d', (int) ($skill['modifier'] ?? 0))); ?> 🎲</strong></button><?php endforeach; ?>
                </div></details>
                <details class="gmrt-satchel__section gmrt-satchel__weapons"><summary>Weapons to Hand</summary>
                    <?php if ($satchelAttacks === []) : ?><p class="gmrt-satchel__empty">No weapon is currently readied in the Companion Adventurer's Pack.</p><?php else : ?>
                    <div class="gmrt-weapon-list">
                        <?php foreach ($satchelAttacks as $attack) : if (! is_array($attack)) continue; ?>
                        <article class="gmrt-weapon-card">
                            <header><div><strong><?php echo esc_html((string) ($attack['label'] ?? 'Weapon')); ?></strong><small><?php echo esc_html((string) ($attack['range'] ?? '')); ?></small></div><b><?php echo esc_html(sprintf('%+d', (int) ($attack['attack_bonus'] ?? 0))); ?></b></header>
                            <p><?php echo esc_html((string) ($attack['damage_die'] ?? '')); ?><?php echo esc_html(sprintf('%+d', (int) ($attack['damage_modifier'] ?? 0))); ?> <?php echo esc_html((string) ($attack['damage_type'] ?? 'damage')); ?> · <?php echo esc_html((string) ($attack['ability'] ?? '')); ?></p>
                            <?php $properties = is_array($attack['properties'] ?? null) ? $attack['properties'] : []; if ($properties !== []) : ?><small class="gmrt-weapon-card__properties"><?php echo esc_html(implode(' · ', array_map('ucfirst', array_map('strval', $properties)))); ?></small><?php endif; ?>
                            <div class="gmrt-weapon-card__actions">
                                <button type="button" data-weapon-roll data-weapon-action="attack" data-attack-id="<?php echo esc_attr((string) ($attack['id'] ?? '')); ?>">Attack 🎲</button>
                                <button type="button" data-weapon-roll data-weapon-action="damage" data-attack-id="<?php echo esc_attr((string) ($attack['id'] ?? '')); ?>">Damage 🎲</button>
                            </div>
                        </article>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </details>
                <details class="gmrt-satchel__section gmrt-satchel__spells" open><summary>Spell Pouch ✨</summary>
                    <?php if ($satchelSpellcasting === [] || $satchelSpells === []) : ?><p class="gmrt-satchel__empty">No spells are currently recorded in this adventurer's Companion spellbook.</p><?php else : ?>
                    <div class="gmrt-spellcasting-measures">
                        <div><span>Ability</span><strong><?php echo esc_html((string) ($satchelSpellcasting['ability'] ?? '—')); ?></strong></div>
                        <div><span>Spell Attack</span><strong><?php echo $satchelSpellcasting['spell_attack'] === null ? '—' : esc_html(sprintf('%+d', (int) $satchelSpellcasting['spell_attack'])); ?></strong></div>
                        <div><span>Save DC</span><strong><?php echo $satchelSpellcasting['save_dc'] === null ? '—' : esc_html((string) $satchelSpellcasting['save_dc']); ?></strong></div>
                    </div>
                    <?php if ($satchelSlots !== []) : ?><div class="gmrt-spell-slots" aria-label="Spell slots"><?php foreach ($satchelSlots as $slot) : if (! is_array($slot)) continue; ?><span>Lv <?php echo esc_html((string) ($slot['level'] ?? '')); ?> <b><?php echo esc_html((string) ($slot['total'] ?? 0)); ?></b></span><?php endforeach; ?></div><?php endif; ?>
                    <div class="gmrt-spell-list">
                    <?php foreach ($satchelSpells as $spell) : if (! is_array($spell)) continue;
                        $spellId = (string) ($spell['id'] ?? '');
                        $spellAttack = array_key_exists('spell_attack', $spell) ? $spell['spell_attack'] : null;
                        $rollKind = (string) ($spell['roll_kind'] ?? '');
                        $spellFormula = (string) ($spell['formula'] ?? '');
                        $spellIllumination = is_array($spell['illumination'] ?? null) ? $spell['illumination'] : [];
                    ?>
                        <article class="gmrt-spell-card">
                            <header><div><strong><?php echo esc_html((string) ($spell['label'] ?? 'Spell')); ?></strong><small><?php echo ((int) ($spell['spell_level'] ?? 0)) === 0 ? 'Cantrip' : 'Level ' . esc_html((string) ($spell['spell_level'] ?? '')); ?></small></div><?php if ($spellAttack !== null) : ?><b>Attack <?php echo esc_html(sprintf('%+d', (int) $spellAttack)); ?></b><?php elseif (! empty($spell['save_ability'])) : ?><b><?php echo esc_html(strtoupper((string) $spell['save_ability'])); ?> DC <?php echo esc_html((string) ($spell['save_dc'] ?? '')); ?></b><?php endif; ?></header>
                            <p class="gmrt-spell-card__meta"><?php echo esc_html((string) ($spell['activation'] ?? '')); ?> · <?php echo esc_html((string) ($spell['range'] ?? '')); ?> · <?php echo esc_html((string) ($spell['duration'] ?? '')); ?></p>
                            <p><?php echo esc_html((string) ($spell['description'] ?? '')); ?></p>
                            <?php if ($spellFormula !== '') : ?><small class="gmrt-spell-card__formula"><?php echo esc_html($spellFormula); ?><?php if (! empty($spell['damage_type'])) : ?> <?php echo esc_html((string) $spell['damage_type']); ?><?php endif; ?></small><?php endif; ?>
                            <?php if ($spellId !== '' && ($spellAttack !== null || ($spellFormula !== '' && in_array($rollKind, ['damage', 'healing'], true)))) : ?>
                            <div class="gmrt-spell-card__actions">
                                <?php if ($spellAttack !== null) : ?><button type="button" data-spell-roll data-spell-action="attack" data-spell-id="<?php echo esc_attr($spellId); ?>">Attack 🎲</button><?php endif; ?>
                                <?php if ($spellFormula !== '' && $rollKind === 'damage') : ?><button type="button" data-spell-roll data-spell-action="damage" data-spell-id="<?php echo esc_attr($spellId); ?>">Damage 🎲</button><?php endif; ?>
                                <?php if ($spellFormula !== '' && $rollKind === 'healing') : ?><button type="button" data-spell-roll data-spell-action="healing" data-spell-id="<?php echo esc_attr($spellId); ?>">Healing 🎲</button><?php endif; ?>
                            </div>
                            <?php endif; ?>
                            <?php if ($spellId !== '' && ($spellIllumination['source'] ?? '') === 'magical') : ?>
                            <div class="gmrt-spell-card__actions gmrt-spell-card__illumination">
                                <button type="button" data-magical-light data-spell-id="<?php echo esc_attr($spellId); ?>">Weave / Quench <?php echo esc_html((string) ($spell['label'] ?? 'Light')); ?> ✨</button>
                                <small><?php echo esc_html((string) ($spellIllumination['bright_feet'] ?? 0)); ?> ft bright + <?php echo esc_html((string) ($spellIllumination['dim_feet'] ?? 0)); ?> ft dim · Companion-certified</small>
                            </div>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                    </div><?php endif; ?>
                </details>
                <div class="gmrt-quick-hands-result" data-quick-hands-result role="status" aria-live="polite">Choose a check, save, skill, Initiative roll, readied weapon or spell.</div><p class="gmrt-satchel__promise">◆ proficient · ◆◆ expertise. Weapons and spells are projected from the Companion; the Tabletop does not duplicate their mechanics.</p>
            </div>
        </aside>
    <?php endif; ?>

    <header class="gmrt-chamber__masthead">
        <div>
            <p class="gmrt-chamber__eyebrow">
                Great Marketrealm Tabletop
            </p>
            <h1>
                <?php echo esc_html(
                    (string) (
                        $table['name']
                        ?? 'The Tabletop Chamber'
                    )
                ); ?>
            </h1>
        </div>

        <?php if ($state !== null) : ?>
            <div class="gmrt-chamber__viewer">
                <span>
                    <?php echo esc_html(
                        $state->isDungeonMaster()
                            ? 'Dungeon Master'
                            : 'Adventurer'
                    ); ?>
                </span>
                <small>
                    Table <?php echo esc_html(
                        (string) (
                            $table['status']
                            ?? 'unknown'
                        )
                    ); ?>
                </small>
            </div>
        <?php endif; ?>
    </header>


    <?php if ($state !== null && $state->isDungeonMaster()) : ?>
        <aside class="gmrt-atlas-drawer" data-keepers-atlas data-open="false" aria-label="The Keeper's Atlas">
            <button class="gmrt-atlas-drawer__toggle" type="button" data-atlas-toggle aria-expanded="false" aria-controls="gmrt-keepers-atlas-panel"><span aria-hidden="true">🗺️</span><span>Atlas</span></button>
            <div class="gmrt-atlas gmrt-atlas-drawer__panel" id="gmrt-keepers-atlas-panel">
            <header class="gmrt-atlas__header"><div><p class="gmrt-chamber__eyebrow">Dungeon Master's Drawer</p><h2>The Keeper's Atlas</h2><small><?php echo esc_html((string) count($scenes)); ?> mapped place<?php echo count($scenes) === 1 ? '' : 's'; ?></small></div><button type="button" data-atlas-close aria-label="Close the Keeper's Atlas">×</button></header>
            <div class="gmrt-atlas__body">
                <div class="gmrt-atlas__introduction">
                    <p>
                        Keep every battlemap belonging to this Table in one register.
                        Opening another place changes only the active Scene; its tokens,
                        Veil, grid and cartography remain bound to that Scene.
                    </p>
                    <span data-atlas-status role="status" aria-live="polite"></span>
                </div>

                <?php if ($scene !== null) : ?>
                    <section class="gmrt-threshold-tools" aria-labelledby="gmrt-threshold-tools-title">
                        <div>
                            <p class="gmrt-chamber__eyebrow">IV.28D · Threshold Markers</p>
                            <h3 id="gmrt-threshold-tools-title">Arrival &amp; Deployment</h3>
                            <p>Choose a marker, then click the map. Party thresholds welcome adventurers who do not already have a remembered token in this Scene. Monster thresholds are reserved for the Keeper's Bestiary.</p>
                        </div>
                        <div class="gmrt-threshold-tools__actions">
                            <button type="button" data-threshold-place="party" data-scene-id="<?php echo esc_attr((string) ($scene['id'] ?? '')); ?>">Place Party Arrival</button>
                            <button type="button" data-threshold-place="monster" data-scene-id="<?php echo esc_attr((string) ($scene['id'] ?? '')); ?>">Place Monster Deployment</button>
                        </div>
                        <small data-threshold-count><?php echo esc_html((string) count($thresholds)); ?> marker<?php echo count($thresholds) === 1 ? '' : 's'; ?> on the Scene currently before the Keeper.</small>
                    </section>
                <?php endif; ?>

                <?php if ($scenes !== []) : ?>
                    <div class="gmrt-atlas__register" aria-label="Mapped Scenes">
                        <?php foreach ($scenes as $atlasScene) :
                            if (! is_array($atlasScene)) continue;
                            $atlasImage = wp_get_attachment_image_url(
                                (int) ($atlasScene['map_attachment_id'] ?? 0),
                                'medium'
                            );
                            $atlasActive = ! empty($atlasScene['active']);
                        ?>
                            <article class="gmrt-atlas-card<?php echo $atlasActive ? ' is-active' : ''; ?>" data-atlas-scene="<?php echo esc_attr((string) ($atlasScene['id'] ?? '')); ?>">
                                <div class="gmrt-atlas-card__image">
                                    <?php if (is_string($atlasImage) && $atlasImage !== '') : ?>
                                        <img src="<?php echo esc_url($atlasImage); ?>" alt="" loading="lazy">
                                    <?php else : ?>
                                        <span aria-hidden="true">◇</span>
                                    <?php endif; ?>
                                </div>
                                <div class="gmrt-atlas-card__copy">
                                    <strong><?php echo esc_html((string) ($atlasScene['name'] ?? 'Unnamed Scene')); ?></strong>
                                    <small>
                                        <?php echo esc_html((string) ((int) ($atlasScene['width'] ?? 0))); ?> ×
                                        <?php echo esc_html((string) ((int) ($atlasScene['height'] ?? 0))); ?> ·
                                        <?php echo esc_html((string) ($atlasScene['grid_type'] ?? 'gridless')); ?>
                                    </small>
                                </div>
                                <?php if ($atlasActive) : ?>
                                    <span class="gmrt-atlas-card__active">Live Scene</span>
                                <?php else : ?>
                                    <div class="gmrt-atlas-card__actions">
                                        <button type="button" data-atlas-prepare-map data-scene-id="<?php echo esc_attr((string) ($atlasScene['id'] ?? '')); ?>">Prepare Scene</button>
                                        <button type="button" data-atlas-open-map data-scene-id="<?php echo esc_attr((string) ($atlasScene['id'] ?? '')); ?>">Open Scene</button>
                                        <button class="gmrt-atlas-card__remove" type="button" data-atlas-delete-map data-scene-id="<?php echo esc_attr((string) ($atlasScene['id'] ?? '')); ?>" data-scene-name="<?php echo esc_attr((string) ($atlasScene['name'] ?? 'Unnamed Scene')); ?>">Remove from Atlas</button>
                                    </div>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="gmrt-atlas__add">
                    <label>
                        Place name
                        <input type="text" maxlength="120" placeholder="e.g. The Pickled Cellar" data-atlas-scene-name>
                    </label>
                    <label>
                        Starting grid
                        <span><input type="number" min="1" max="500" value="64" data-atlas-grid-size> px</span>
                    </label>
                    <button type="button" data-atlas-add-map>Add Map to Atlas</button>
                    <small>The new map is added safely in the background. It does not become active until you open it.</small>
                </div>
            </div>
            </div>
        </aside>
    <?php endif; ?>

    <?php if ($state !== null && $state->isDungeonMaster()) : ?>
        <aside class="gmrt-bestiary-drawer" data-keepers-bestiary data-open="false" aria-label="The Keeper's Bestiary">
            <button class="gmrt-bestiary-drawer__toggle" type="button" data-bestiary-toggle aria-expanded="false" aria-controls="gmrt-keepers-bestiary-panel"><span aria-hidden="true">🐲</span><span>Bestiary</span></button>
            <div class="gmrt-bestiary gmrt-bestiary-drawer__panel" id="gmrt-keepers-bestiary-panel">
                <header class="gmrt-bestiary__header">
                    <div><p class="gmrt-chamber__eyebrow">Dungeon Master's Drawer · IV.29B</p><h2>The Keeper's Bestiary</h2><small><?php echo esc_html((string) count($bestiary)); ?> creature record<?php echo count($bestiary) === 1 ? '' : 's'; ?></small></div>
                    <button type="button" data-bestiary-close aria-label="Close the Keeper's Bestiary">×</button>
                </header>
                <div class="gmrt-bestiary__body">
                    <p class="gmrt-bestiary__introduction">Browse reusable creature definitions, then summon Scene-owned instances onto the live map or a privately prepared Scene.</p>
                    <label class="gmrt-bestiary__search">Search the shelves<input type="search" autocomplete="off" placeholder="Name, kind, attack, damage…" data-bestiary-search></label>
                    <small data-bestiary-results aria-live="polite"><?php echo esc_html((string) count($bestiary)); ?> records shown</small>
                    <div class="gmrt-bestiary__register" data-bestiary-register>
                        <?php foreach ($bestiary as $creature) :
                            if (! is_array($creature)) continue;
                            $attacks = is_array($creature['attacks'] ?? null) ? $creature['attacks'] : [];
                            $resistances = is_array($creature['resistances'] ?? null) ? $creature['resistances'] : [];
                            $immunities = is_array($creature['immunities'] ?? null) ? $creature['immunities'] : [];
                            $weaknesses = is_array($creature['weaknesses'] ?? null) ? $creature['weaknesses'] : [];
                            $traits = is_array($creature['traits'] ?? null) ? $creature['traits'] : [];
                            $searchParts = [(string) ($creature['name'] ?? ''), (string) ($creature['kind'] ?? ''), (string) ($creature['size'] ?? '')];
                            foreach ($attacks as $attack) {
                                if (! is_array($attack)) continue;
                                $searchParts[] = (string) ($attack['name'] ?? '');
                                $searchParts[] = (string) (($attack['damage']['type'] ?? ''));
                            }
                            $searchParts = array_merge($searchParts, $resistances, $immunities, $weaknesses, $traits);
                        ?>
                            <article class="gmrt-bestiary-card" data-bestiary-card data-bestiary-search-text="<?php echo esc_attr(strtolower(implode(' ', array_map('strval', $searchParts)))); ?>">
                                <header><div class="gmrt-bestiary-card__sigil" aria-hidden="true">◆</div><div><strong><?php echo esc_html((string) ($creature['name'] ?? 'Unknown Creature')); ?></strong><small><?php echo esc_html((string) ($creature['size'] ?? 'Unknown')); ?> · <?php echo esc_html((string) ($creature['kind'] ?? 'creature')); ?></small></div></header>
                                <dl class="gmrt-bestiary-card__measures"><div><dt>AC</dt><dd><?php echo esc_html((string) ($creature['armor_class'] ?? '—')); ?></dd></div><div><dt>HP</dt><dd><?php echo esc_html((string) ($creature['hit_points'] ?? '—')); ?></dd></div><div><dt>Speed</dt><dd><?php echo esc_html((string) ($creature['speed_feet'] ?? '—')); ?> ft</dd></div></dl>
                                <details class="gmrt-bestiary-card__record">
                                    <summary>Inspect creature record</summary>
                                    <?php if ($attacks !== []) : ?><section><h3>Actions</h3><ul><?php foreach ($attacks as $attack) : if (! is_array($attack)) continue; $damage = is_array($attack['damage'] ?? null) ? $attack['damage'] : []; ?><li><strong><?php echo esc_html((string) ($attack['name'] ?? 'Attack')); ?></strong><span>+<?php echo esc_html((string) ($attack['attack_modifier'] ?? 0)); ?> · <?php echo esc_html((string) ($attack['range_feet'] ?? 5)); ?><?php if ((int) ($attack['long_range_feet'] ?? 5) > (int) ($attack['range_feet'] ?? 5)) : ?>/<?php echo esc_html((string) ($attack['long_range_feet'] ?? 5)); ?><?php endif; ?> ft · <?php echo esc_html((string) ($damage['dice_count'] ?? 1)); ?>d<?php echo esc_html((string) ($damage['die_sides'] ?? 4)); ?><?php $mod = (int) ($damage['modifier'] ?? 0); if ($mod !== 0) echo esc_html(($mod > 0 ? '+' : '') . (string) $mod); ?> <?php echo esc_html((string) ($damage['type'] ?? 'damage')); ?></span></li><?php endforeach; ?></ul></section><?php endif; ?>
                                    <?php if ($resistances !== [] || $immunities !== [] || $weaknesses !== []) : ?><section><h3>Defences</h3><?php if ($resistances !== []) : ?><p><strong>Resists:</strong> <?php echo esc_html(implode(', ', $resistances)); ?></p><?php endif; ?><?php if ($immunities !== []) : ?><p><strong>Immune:</strong> <?php echo esc_html(implode(', ', $immunities)); ?></p><?php endif; ?><?php if ($weaknesses !== []) : ?><p><strong>Weak:</strong> <?php echo esc_html(implode(', ', $weaknesses)); ?></p><?php endif; ?></section><?php endif; ?>
                                    <?php if ($traits !== []) : ?><section><h3>Traits</h3><ul><?php foreach ($traits as $trait) : ?><li><?php echo esc_html((string) $trait); ?></li><?php endforeach; ?></ul></section><?php endif; ?>
                                    <small class="gmrt-bestiary-card__source">Definition: <?php echo esc_html((string) ($creature['source'] ?? 'gmrt-bestiary')); ?></small>
                                </details>
                                <div class="gmrt-bestiary-card__deployment" data-bestiary-deployment data-creature-id="<?php echo esc_attr((string) ($creature['id'] ?? '')); ?>">
                                    <div class="gmrt-bestiary-card__deployment-options">
                                        <label>Copies <input type="number" min="1" max="12" value="1" data-bestiary-quantity></label>
                                        <label class="gmrt-bestiary-card__hidden"><input type="checkbox" data-bestiary-hidden> Hidden from Players</label>
                                    </div>
                                    <div class="gmrt-bestiary-card__deployment-actions">
                                        <button type="button" class="gmrt-bestiary-card__summon" data-bestiary-place>Place on Map</button>
                                        <button type="button" class="gmrt-bestiary-card__summon" data-bestiary-threshold>Use Monster Threshold</button>
                                    </div>
                                    <small>Target: <?php echo esc_html((string) ($scene['name'] ?? 'Current Scene')); ?><?php echo $isPreparingScene ? ' · private preparation' : ' · live Scene'; ?></small>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                    <p class="gmrt-bestiary__empty" data-bestiary-empty hidden>No creature records match that search.</p>
                </div>
            </div>
        </aside>
    <?php endif; ?>

    <?php if ($state !== null && $state->isDungeonMaster() && $isPreparingScene && $scene !== null) : ?>
        <section class="gmrt-preparation-banner" data-preparation-banner>
            <div><p class="gmrt-chamber__eyebrow">Behind the Curtain</p><strong>Preparing <?php echo esc_html((string) ($scene['name'] ?? 'Scene')); ?></strong><span>Players remain on the live Scene. Cartography changes here are bound only to this prepared map.</span></div>
            <button type="button" data-exit-preparation>Return to Live Scene</button>
        </section>
    <?php endif; ?>

    <div data-live-lifecycle>
    <?php if ($state !== null && $encounter === null) : ?>
        <section
            class="gmrt-exploration-strip"
            aria-labelledby="gmrt-exploration-title"
            data-exploration-mode
        >
            <div>
                <p class="gmrt-chamber__eyebrow">Peace upon the path</p>
                <h2 id="gmrt-exploration-title">Exploration Mode</h2>
                <p class="gmrt-exploration-strip__copy">
                    Move through the Scene freely. The Living Veil, doors, walls
                    and remembered route remain active without rounds or turns.
                </p>
            </div>

            <?php if ($state->isDungeonMaster() && $scene !== null && $tokens !== []) : ?>
                <details class="gmrt-start-encounter" data-start-encounter-panel>
                    <summary>Start Encounter ⚔</summary>
                    <div class="gmrt-start-encounter__body">
                        <label>
                            Encounter name
                            <input
                                type="text"
                                value="A Sudden Encounter"
                                data-encounter-name
                            >
                        </label>
                        <fieldset>
                            <legend>Combatants &amp; initiative</legend>
                            <?php foreach ($tokens as $token) : ?>
                                <?php $tokenId = (string) ($token['id'] ?? ''); ?>
                                <?php if ($tokenId === '') { continue; } ?>
                                <label class="gmrt-start-encounter__combatant">
                                    <input
                                        type="checkbox"
                                        value="<?php echo esc_attr($tokenId); ?>"
                                        data-encounter-combatant
                                        checked
                                    >
                                    <span><?php echo esc_html((string) ($token['label'] ?? 'Combatant')); ?></span>
                                    <span>Initiative</span>
                                    <input
                                        type="number"
                                        min="-20"
                                        max="99"
                                        value="10"
                                        data-encounter-initiative="<?php echo esc_attr($tokenId); ?>"
                                        aria-label="Initiative for <?php echo esc_attr((string) ($token['label'] ?? 'combatant')); ?>"
                                    >
                                </label>
                            <?php endforeach; ?>
                        </fieldset>
                        <button type="button" data-start-encounter>
                            Begin Battle ⚔
                        </button>
                    </div>
                </details>
            <?php endif; ?>
        </section>
    <?php endif; ?>


    <?php if ($encounter !== null) : ?>
        <section
            class="gmrt-encounter-strip"
            aria-labelledby="gmrt-encounter-title"
            data-encounter-id="<?php echo esc_attr(
                (string) $encounter['id']
            ); ?>"
            data-encounter-revision="<?php echo esc_attr(
                (string) ($encounter['revision'] ?? 1)
            ); ?>"
        >
            <div>
                <p class="gmrt-chamber__eyebrow">
                    The Turn of Battle
                </p>
                <h2 id="gmrt-encounter-title">
                    <?php echo esc_html(
                        (string) $encounter['name']
                    ); ?>
                </h2>
            </div>

            <div class="gmrt-encounter-strip__state">
                <strong>
                    <?php echo esc_html(
                        strtoupper((string) $encounter['status'])
                    ); ?>
                </strong>

                <?php if ((int) ($encounter['round'] ?? 0) > 0) : ?>
                    <span data-live-round>
                        Round <?php echo esc_html(
                            (string) $encounter['round']
                        ); ?>
                    </span>
                <?php endif; ?>

                <?php if (! empty($encounter['current_token_id'])) : ?>
                    <?php
                    $turnTokenId = (string) $encounter['current_token_id'];
                    $turnLabel = $tokenLabels[$turnTokenId]
                        ?? 'Unknown combatant';
                    ?>
                    <span class="gmrt-current-turn" data-current-turn-label>
                        Turn:
                        <strong data-live-current-combatant><?php echo esc_html($turnLabel); ?></strong>
                    </span>
                <?php endif; ?>

                <?php if (
                    $state !== null
                    && $state->isDungeonMaster()
                    && ($encounter['status'] ?? '') === 'active'
                ) : ?>
                    <button
                        type="button"
                        class="gmrt-end-turn"
                        data-end-turn
                    >
                        End Turn ▶
                    </button>
                    <button
                        type="button"
                        class="gmrt-end-encounter"
                        data-end-encounter
                    >
                        End Encounter ◇
                    </button>
                <?php endif; ?>
            </div>

        <?php if (
            ($encounter['status'] ?? '') === 'active'
            && ! empty($encounter['current_token_id'])
        ) : ?>
            <?php
            $currentTokenId = (string) (
                $encounter['current_token_id']
                ?? ''
            );
            $currentVitality = $vitality[$currentTokenId] ?? null;
            $currentDeathSaves = $deathSaves[$currentTokenId] ?? null;
            $currentArsenal = $arsenals[$currentTokenId]['attacks'] ?? [];
            ?>
            <?php if (
                is_array($currentVitality)
                && (int) $currentVitality['current_hp'] === 0
            ) : ?>
                <div class="gmrt-death-saves" data-death-saves>
                    <strong>
                        <?php echo (
                            is_array($currentDeathSaves)
                            && ! empty($currentDeathSaves['dead'])
                        ) ? 'DECEASED' : 'DOWN'; ?>
                    </strong>
                    <?php if (
                        is_array($currentDeathSaves)
                        && ! empty($currentDeathSaves['dead'])
                    ) : ?>
                        <span>Death confirmed</span>
                    <?php elseif (
                        is_array($currentDeathSaves)
                        && ! empty($currentDeathSaves['stable'])
                    ) : ?>
                        <span>Stable</span>
                    <?php else : ?>
                        <span>
                            Saves <?php echo esc_html(
                                (string) ($currentDeathSaves['successes'] ?? 0)
                            ); ?>/3
                            · Failures <?php echo esc_html(
                                (string) ($currentDeathSaves['failures'] ?? 0)
                            ); ?>/3
                        </span>
                        <button type="button" data-roll-death-save>
                            Roll Death Save
                        </button>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div
                class="gmrt-deeds"
                aria-label="Battle deeds"
                data-current-token="<?php echo esc_attr(
                    (string) $encounter['current_token_id']
                ); ?>"
            >
                <?php if ($currentArsenal !== []) : ?>
                    <label class="gmrt-deeds__attack">
                        <span>Attack</span>
                        <select data-arsenal-attack>
                            <?php foreach ($currentArsenal as $arsenalAttack) :
                                $combat = $arsenalAttack['combat'] ?? [];
                                $damage = $arsenalAttack['damage'] ?? [];
                                $normal = (int) ($combat['attack_range_feet'] ?? 5);
                                $long = (int) ($combat['long_range_feet'] ?? $normal);
                                $rangeLabel = $long > $normal
                                    ? $normal . '/' . $long . ' ft'
                                    : $normal . ' ft';
                                $diceLabel = (int) ($damage['dice_count'] ?? 1)
                                    . 'd'
                                    . (int) ($damage['die_sides'] ?? 6);
                                $damageModifier = (int) ($damage['modifier'] ?? 0);
                                if ($damageModifier > 0) {
                                    $diceLabel .= '+' . $damageModifier;
                                } elseif ($damageModifier < 0) {
                                    $diceLabel .= (string) $damageModifier;
                                }
                                ?>
                                <option value="<?php echo esc_attr(
                                    (string) ($arsenalAttack['id'] ?? '')
                                ); ?>">
                                    <?php echo esc_html(
                                        (string) ($arsenalAttack['name'] ?? 'Attack')
                                        . ' · ' . $diceLabel
                                        . ' ' . strtoupper(
                                            (string) ($damage['damage_type'] ?? '')
                                        )
                                        . ' · ' . $rangeLabel
                                    ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                <?php endif; ?>
                <label class="gmrt-deeds__target">
                    <span>Target</span>
                    <select data-attack-target>
                        <option value="">Choose target…</option>
                        <?php foreach ($tokens as $targetToken) :
                            if (
                                ($targetToken['id'] ?? '')
                                === ($encounter['current_token_id'] ?? '')
                            ) {
                                continue;
                            }
                            ?>
                            <option
                                value="<?php echo esc_attr(
                                    (string) ($targetToken['id'] ?? '')
                                ); ?>"
                            >
                                <?php echo esc_html(
                                    (string) ($targetToken['label'] ?? 'Token')
                                ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <span
                    class="gmrt-target-range"
                    data-target-range-status
                    role="status"
                    aria-live="polite"
                >
                    Choose target
                </span>
                <?php foreach (
                    ['attack' => 'Attack', 'dash' => 'Dash',
                     'disengage' => 'Disengage', 'dodge' => 'Dodge',
                     'help' => 'Help']
                    as $deedKey => $deedLabel
                ) : ?>
                    <button
                        type="button"
                        class="gmrt-deed"
                        data-battle-deed="<?php echo esc_attr(
                            $deedKey
                        ); ?>"
                    >
                        <?php echo esc_html($deedLabel); ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <?php if ($state !== null && $state->isDungeonMaster()) : ?>
                <div class="gmrt-afflictions" data-affliction-controls>
                    <strong>Afflictions</strong>
                    <select data-condition-target aria-label="Condition target">
                        <option value="">Choose combatant…</option>
                        <?php foreach ($tokens as $conditionToken) : ?>
                            <option value="<?php echo esc_attr(
                                (string) ($conditionToken['id'] ?? '')
                            ); ?>">
                                <?php echo esc_html(
                                    (string) ($conditionToken['label'] ?? 'Token')
                                ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <select data-condition-type aria-label="Condition">
                        <?php foreach (
                            ['blinded','charmed','frightened','grappled',
                             'poisoned','prone','restrained','stunned']
                            as $conditionType
                        ) : ?>
                            <option value="<?php echo esc_attr($conditionType); ?>">
                                <?php echo esc_html(ucfirst($conditionType)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <label>
                        <span class="screen-reader-text">Duration in turns</span>
                        <input
                            type="number"
                            min="0"
                            max="99"
                            value="0"
                            data-condition-duration
                            title="0 means until removed"
                        >
                    </label>
                    <button type="button" data-apply-condition>
                        Apply
                    </button>
                    <button type="button" data-remove-condition>
                        Remove
                    </button>
                </div>
            <?php endif; ?>

            <div
                class="gmrt-diceworks"
                data-combat-diceworks
                aria-live="polite"
                hidden
            >
                <div class="gmrt-diceworks__heading">
                    <span>Guild Diceworks</span>
                    <strong data-diceworks-mode>D20</strong>
                </div>
                <div class="gmrt-diceworks__tray" aria-hidden="true">
                    <span class="gmrt-pixel-d20" data-combat-die="0">
                        <b data-die-value>?</b>
                    </span>
                    <span class="gmrt-pixel-d20" data-combat-die="1" hidden>
                        <b data-die-value>?</b>
                    </span>
                </div>
                <div
                    class="gmrt-diceworks__outcome"
                    data-diceworks-outcome
                    hidden
                >
                    <strong data-diceworks-outcome-title>
                        Awaiting result
                    </strong>
                    <span data-diceworks-outcome-detail></span>
                </div>
                <small data-diceworks-result>
                    Awaiting the roll…
                </small>
                <i
                    class="gmrt-lonely-confetti"
                    data-lonely-confetti
                    aria-hidden="true"
                    hidden
                ></i>
            </div>
        <?php endif; ?>
        </section>
    <?php endif; ?>
    </div>

    <?php if (is_array($invitation)) : ?>
        <section class="gmrt-gathering-invitation" role="status">
            <p class="gmrt-chamber__eyebrow">The Gathering at the Table</p>
            <h2>Your chair is waiting</h2>
            <p>
                The Dungeon Master has invited you to join this Table.
                Taking your seat creates your persistent Table membership;
                your character remains a separate choice.
            </p>
            <button type="button" data-accept-table-invitation>
                Take My Seat
            </button>
            <span data-gathering-status role="status" aria-live="polite"></span>
        </section>
    <?php elseif ($message !== null) : ?>
        <section
            class="gmrt-chamber__notice"
            role="status"
        >
            <h2>The chamber awaits</h2>
            <p><?php echo esc_html($message); ?></p>
            <?php if (! empty($canPrepareTestTable)) : ?>
                <button type="button" class="gmrt-test-table-button" data-prepare-test-table>
                    Prepare Test Table
                </button>
                <small class="gmrt-test-table-note">
                    Creates Sage's Combat Testing Grounds with Auby,
                    a Training Slime and other test combatants.
                </small>
            <?php endif; ?>
        </section>
    <?php elseif ($scene === null) : ?>
        <section
            class="gmrt-chamber__notice"
            role="status"
        >
            <h2>No active Scene</h2>
            <p>
                This Table has gathered, but the Dungeon Master
                has not opened a battlemap Scene yet.
            </p>
        </section>
    <?php else : ?>
        <div class="gmrt-chamber__layout">
            <section
                class="gmrt-board"
                aria-labelledby="gmrt-scene-title"
            >
                <div class="gmrt-board__heading">
                    <div>
                        <p class="gmrt-chamber__eyebrow">
                            Active Scene
                        </p>
                        <h2 id="gmrt-scene-title">
                            <?php echo esc_html(
                                (string) $scene['name']
                            ); ?>
                        </h2>
                    </div>

                    <div class="gmrt-board__tools">
                        <button
                            type="button"
                            class="gmrt-remove-token-button"
                            data-remove-selected-token
                            hidden
                        >Remove from Chamber</button>
                        <?php if ($state->isDungeonMaster()) : ?>
                            <button
                                type="button"
                                class="gmrt-cartographer-button"
                                data-choose-battlemap
                            >
                                Choose Battlemap
                            </button>
                        <?php endif; ?>

                    <span>
                        <?php echo esc_html(
                            (string) $scene['grid_type']
                        ); ?>
                        <?php if (
                            ($scene['grid_type'] ?? '')
                            === 'square'
                        ) : ?>
                            · <?php echo esc_html(
                                (string) $scene['grid_size']
                            ); ?> px grid
                        <?php endif; ?>
                    </span>
                    </div>
                </div>

                <div class="gmrt-cartographers-lens" data-cartographers-lens>
                    <strong class="gmrt-cartographers-lens__title">Cartographer's Lens</strong>
                    <button type="button" data-lens-zoom-out aria-label="Zoom battlefield out">−</button>
                    <output data-lens-zoom aria-live="polite">100%</output>
                    <button type="button" data-lens-zoom-in aria-label="Zoom battlefield in">+</button>
                    <button type="button" data-lens-fit>Fit Map</button>
                    <button type="button" data-lens-reset>Reset View</button>
                    <span class="gmrt-cartographers-lens__hint">Drag the map to pan while zoomed.</span>
                </div>

                <?php if ($state->isDungeonMaster()) : ?>
                    <div class="gmrt-fog-controls">
                        <strong>The Veil of the Unknown</strong>
                        <label>
                            <input
                                type="checkbox"
                                data-fog-enabled
                                <?php checked(! empty($fog['enabled'])); ?>
                            >
                            Enable Fog of War
                        </label>
                        <label>
                            <input type="checkbox" data-fog-preview>
                            Preview Player Fog
                        </label>
                        <button type="button" data-fog-clear>
                            Reset Exploration
                        </button>
                        <span data-fog-status role="status" aria-live="polite"></span>
                    </div>

                    <div class="gmrt-vision-controls" data-vision-controls>
                        <strong>Sight Beyond the Door</strong>
                        <span>Teach the Veil where sight must stop.</span>
                        <button type="button" data-vision-tool="wall">Draw Wall</button>
                        <button type="button" data-vision-tool="door">Place Door</button>
                        <button type="button" data-vision-undo disabled>Undo Last</button>
                        <button type="button" data-vision-cancel disabled>Finish / Cancel</button>
                        <span data-vision-status role="status" aria-live="polite">Choose a wall or door, then click two grid intersections.</span>
                        <div class="gmrt-vision-roster" data-vision-roster></div>
                    </div>
                <?php endif; ?>


                <?php if (
                    $state->isDungeonMaster()
                    && ($scene['grid_type'] ?? '') === 'square'
                ) : ?>
                    <details class="gmrt-grid-calibrator" data-grid-calibrator>
                        <summary>Calibrate Grid</summary>
                        <div class="gmrt-grid-calibrator__controls">
                            <label>
                                Square
                                <input type="number" min="8" max="512" step="1"
                                    value="<?php echo esc_attr((string) ($scene['grid_size'] ?? 64)); ?>"
                                    data-grid-size> px
                            </label>
                            <label>
                                X offset
                                <input type="number" step="1"
                                    value="<?php echo esc_attr((string) ($scene['grid_offset_x'] ?? 0)); ?>"
                                    data-grid-offset-x> px
                            </label>
                            <label>
                                Y offset
                                <input type="number" step="1"
                                    value="<?php echo esc_attr((string) ($scene['grid_offset_y'] ?? 0)); ?>"
                                    data-grid-offset-y> px
                            </label>
                            <label>
                                Opacity
                                <input type="range" min="0" max="100" step="1"
                                    value="<?php echo esc_attr((string) ($scene['grid_opacity'] ?? 13)); ?>"
                                    data-grid-opacity>
                            </label>
                            <label class="gmrt-grid-calibrator__visible">
                                <input type="checkbox" data-grid-visible
                                    <?php checked((bool) ($scene['grid_visible'] ?? true)); ?>>
                                Show grid
                            </label>
                            <div class="gmrt-grid-calibrator__nudges" aria-label="Grid nudge controls">
                                <button type="button" data-grid-nudge="0,-1" aria-label="Nudge grid up">↑</button>
                                <button type="button" data-grid-nudge="-1,0" aria-label="Nudge grid left">←</button>
                                <button type="button" data-grid-nudge="1,0" aria-label="Nudge grid right">→</button>
                                <button type="button" data-grid-nudge="0,1" aria-label="Nudge grid down">↓</button>
                            </div>
                            <button type="button" data-save-grid>Save Grid</button>
                            <button type="button" data-reset-grid>Reset Preview</button>
                        </div>
                    </details>
                <?php endif; ?>

                <?php if ($state->isDungeonMaster()) : ?>
                    <p
                        class="gmrt-cartographer-status"
                        data-cartographer-status
                        role="status"
                        aria-live="polite"
                    >
                        Battlemap artwork may be changed without moving tokens
                        or changing the rules grid.
                    </p>
                <?php endif; ?>

                <div class="gmrt-board__lens-stage" data-lens-stage>
                <div
                    class="gmrt-board__viewport"
                    data-grid-type="<?php echo esc_attr(
                        (string) $scene['grid_type']
                    ); ?>"
                    data-grid-reference-width="<?php echo esc_attr(
                        (string) ((int) (
                            $scene['grid_reference_width']
                            ?? 0
                        ))
                    ); ?>"
                    style="--gmrt-grid-size: <?php echo esc_attr(
                        (string) max(
                            1,
                            (int) (
                                $scene['grid_size']
                                ?? 1
                            )
                        )
                    ); ?>px; --gmrt-grid-offset-x: <?php echo esc_attr((string) ((int) ($scene['grid_offset_x'] ?? 0))); ?>px; --gmrt-grid-offset-y: <?php echo esc_attr((string) ((int) ($scene['grid_offset_y'] ?? 0))); ?>px; --gmrt-grid-opacity: <?php echo esc_attr((string) ((int) ($scene['grid_opacity'] ?? 13) / 100)); ?>; --gmrt-grid-display: <?php echo ! array_key_exists('grid_visible', $scene) || ! empty($scene['grid_visible']) ? 'block' : 'none'; ?>;"
                >
                    <?php if ($sceneImage !== false) : ?>
                        <img
                            class="gmrt-board__map"
                        draggable="false"
                            data-battlemap-image
                            src="<?php echo esc_url(
                                $sceneImage
                            ); ?>"
                            alt=""
                            width="<?php echo esc_attr(
                                (string) $scene['width']
                            ); ?>"
                            height="<?php echo esc_attr(
                                (string) $scene['height']
                            ); ?>"
                        >
                    <?php else : ?>
                        <div
                            class="gmrt-board__missing-map"
                            role="status"
                        >
                            Battlemap artwork is unavailable.
                        </div>
                    <?php endif; ?>

                    <?php if (
                        ($scene['grid_type'] ?? '')
                        === 'square'
                    ) : ?>
                        <div
                            class="gmrt-board__grid"
                            aria-hidden="true"
                        ></div>
                    <?php endif; ?>

                    <?php if ($state->isDungeonMaster()) : ?>
                        <svg
                            class="gmrt-vision-layer"
                            data-vision-layer
                            data-vision='<?php echo esc_attr(wp_json_encode($visionLayer)); ?>'
                            aria-label="Dungeon Master vision barriers"
                        ></svg>
                    <?php endif; ?>

                    <div
                        class="gmrt-footstep-layer"
                        data-footstep-layer
                        data-footsteps="<?php echo esc_attr(wp_json_encode($state->footsteps())); ?>"
                        aria-hidden="true"
                    ></div>

                    <div class="gmrt-light-layer" data-light-layer aria-hidden="true"></div>

                    <div
                        class="gmrt-fog-layer"
                        data-fog-layer
                        data-fog="<?php echo esc_attr(
                            wp_json_encode($fog)
                        ); ?>"
                        aria-hidden="true"
                    ></div>

                    <svg
                        class="gmrt-targeting-layer"
                        data-targeting-layer
                        aria-hidden="true"
                    >
                        <line
                            class="gmrt-target-line"
                            data-target-line
                            x1="0"
                            y1="0"
                            x2="0"
                            y2="0"
                        ></line>
                    </svg>

                    <?php if ($state->isDungeonMaster() && $thresholds !== []) : ?>
                        <div class="gmrt-threshold-layer" data-threshold-layer aria-label="Dungeon Master Threshold Markers">
                            <?php foreach ($thresholds as $threshold) :
                                if (! is_array($threshold)) continue;
                                $thresholdType = (string) ($threshold['type'] ?? 'party');
                                $thresholdLabel = $thresholdType === 'monster'
                                    ? 'Monster Deployment Threshold'
                                    : 'Party Arrival Threshold';
                            ?>
                                <button
                                    type="button"
                                    class="gmrt-threshold-marker gmrt-threshold-marker--<?php echo esc_attr($thresholdType); ?>"
                                    data-threshold-marker="<?php echo esc_attr((string) ($threshold['id'] ?? '')); ?>"
                                    data-threshold-type="<?php echo esc_attr($thresholdType); ?>"
                                    data-scene-id="<?php echo esc_attr((string) ($threshold['scene_id'] ?? '')); ?>"
                                    style="--gmrt-threshold-x: <?php echo esc_attr((string) ((float) ($threshold['x'] ?? 0) * 100)); ?>%; --gmrt-threshold-y: <?php echo esc_attr((string) ((float) ($threshold['y'] ?? 0) * 100)); ?>%;"
                                    aria-label="<?php echo esc_attr($thresholdLabel . '. Click to reposition; Shift-click to remove.'); ?>"
                                    title="<?php echo esc_attr($thresholdLabel . ' · click to reposition · Shift-click to remove'); ?>"
                                ><span aria-hidden="true"><?php echo $thresholdType === 'monster' ? '◆' : '◇'; ?></span></button>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div
                        class="gmrt-board__tokens"
                        aria-label="Table tokens"
                    >
                        <?php foreach ($tokens as $token) :
                            $left = max(
                                0,
                                min(
                                    100,
                                    (float) (
                                        $token['x']
                                        ?? 0
                                    ) * 100
                                )
                            );
                            $top = max(
                                0,
                                min(
                                    100,
                                    (float) (
                                        $token['y']
                                        ?? 0
                                    ) * 100
                                )
                            );
                            $width = max(
                                1,
                                (float) (
                                    $token['width_units']
                                    ?? 1
                                )
                            );
                            $height = max(
                                1,
                                (float) (
                                    $token['height_units']
                                    ?? 1
                                )
                            );
                            $tokenId = (string) (
                                $token['id'] ?? ''
                            );
                            $combatantState = (string) (
                                $combatantStates[$tokenId]
                                ?? 'healthy'
                            );
                            $stateBadge = match ($combatantState) {
                                'downed' => 'DOWN',
                                'defeated' => 'KO',
                                'deceased' => 'DEAD',
                                default => '',
                            };
                            ?>
                            <div
                                class="gmrt-token gmrt-token--<?php echo esc_attr(
                                    (string) $token['type']
                                ); ?> is-state-<?php echo esc_attr(
                                    $combatantState
                                ); ?><?php echo (
                                    ($token['visibility'] ?? '')
                                    === 'hidden'
                                ) ? ' is-hidden-token' : ''; ?><?php echo (
                                    $encounter !== null
                                    && (string) ($encounter['current_token_id'] ?? '') === $tokenId
                                ) ? ' is-active-turn' : ''; ?>"
                                style="
                                    --gmrt-token-x: <?php echo esc_attr(
                                        (string) $left
                                    ); ?>%;
                                    --gmrt-token-y: <?php echo esc_attr(
                                        (string) $top
                                    ); ?>%;
                                    --gmrt-token-width: <?php echo esc_attr(
                                        (string) $width
                                    ); ?>;
                                    --gmrt-token-height: <?php echo esc_attr(
                                        (string) $height
                                    ); ?>;
                                    --gmrt-fellowship-colour: <?php echo esc_attr((string) ($token['table_colour_hex'] ?? '#d8ad4f')); ?>;
                                "
                                title="<?php echo esc_attr(
                                    (string) $token['label']
                                ); ?>"
                                data-token-id="<?php echo esc_attr(
                                    (string) $token['id']
                                ); ?>"
                                data-token-revision="<?php echo esc_attr(
                                    (string) ($token['revision'] ?? 1)
                                ); ?>"
                                data-token-controller="<?php echo esc_attr(
                                    (string) ($token['controller_user_id'] ?? '')
                                ); ?>"
                                data-token-type="<?php echo esc_attr(
                                    (string) ($token['type'] ?? '')
                                ); ?>"
                                data-combatant-state="<?php echo esc_attr(
                                    $combatantState
                                ); ?>"
                                tabindex="0"
                                role="button"
                                aria-label="<?php echo esc_attr(
                                    'Select token: '
                                    . (string) $token['label']
                                ); ?>"
                            >
                                <?php
                                $companionCharacter = is_array($token['companion_character'] ?? null)
                                    ? $token['companion_character']
                                    : null;
                                $tokenRecipe = is_array($companionCharacter['token'] ?? null)
                                    ? $companionCharacter['token']
                                    : [];
                                $tokenImage = (string) ($tokenRecipe['image_url'] ?? '');
                                ?>
                                <span class="gmrt-token__face" aria-hidden="true">
                                    <?php if ($tokenImage !== '') : ?>
                                        <img
                                            src="<?php echo CompanionTokenImageSource::escaped($tokenImage); ?>"
                                            alt=""
                                            style="--gmrt-token-focus-x: <?php echo esc_attr((string) ($tokenRecipe['focus_x'] ?? 50)); ?>%; --gmrt-token-focus-y: <?php echo esc_attr((string) ($tokenRecipe['focus_y'] ?? 50)); ?>%; --gmrt-token-zoom: <?php echo esc_attr((string) ($tokenRecipe['zoom'] ?? 100)); ?>%;"
                                        >
                                    <?php else : ?>
                                        <?php echo esc_html(strtoupper(substr((string) $token['label'], 0, 1))); ?>
                                    <?php endif; ?>
                                </span>
                                <span
                                    class="gmrt-token__state-badge"
                                    data-token-state-badge
                                    <?php echo $stateBadge === ''
                                        ? 'hidden'
                                        : ''; ?>
                                    aria-hidden="true"
                                >
                                    <?php echo esc_html($stateBadge); ?>
                                </span>
                                <?php
                                $tokenConditions = $conditions[
                                    (string) ($token['id'] ?? '')
                                ] ?? [];
                                ?>
                                <?php if ($tokenConditions !== []) : ?>
                                    <span
                                        class="gmrt-token__conditions"
                                        aria-hidden="true"
                                    >
                                        <?php foreach ($tokenConditions as $tokenCondition) : ?>
                                            <i
                                                class="gmrt-condition gmrt-condition--<?php echo esc_attr(
                                                    (string) ($tokenCondition['condition'] ?? '')
                                                ); ?>"
                                                title="<?php echo esc_attr(
                                                    ucfirst((string) ($tokenCondition['condition'] ?? ''))
                                                ); ?>"
                                            ></i>
                                        <?php endforeach; ?>
                                    </span>
                                <?php endif; ?>
                                <span class="screen-reader-text">
                                    <?php echo esc_html(
                                        (string) $token['label']
                                    ); ?>
                                    — <?php echo esc_html(
                                        $combatantState
                                    ); ?>
                                    <?php if (
                                        ($token['visibility'] ?? '')
                                        === 'hidden'
                                    ) : ?>
                                        — hidden from players
                                    <?php endif; ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                </div>
            </section>

            <aside
                class="gmrt-party"
                aria-labelledby="gmrt-party-title"
            >
                <p class="gmrt-chamber__eyebrow">
                    The Gathering
                </p>
                <h2 id="gmrt-party-title">
                    Adventurers at the Table
                </h2>

                <div class="gmrt-fellowship-colour" data-fellowship-colour-picker>
                    <span>Your Fellowship Ribbon</span>
                    <div class="gmrt-fellowship-colour__swatches" role="group" aria-label="Choose your Great Marketrealm Table colour">
                    <?php foreach (TableColourPalette::all() as $colourKey => $colour) : ?>
                        <button type="button" data-table-colour="<?php echo esc_attr($colourKey); ?>" style="--gmrt-swatch: <?php echo esc_attr($colour['hex']); ?>" aria-pressed="<?php echo $viewerColour === $colourKey ? 'true' : 'false'; ?>" title="<?php echo esc_attr($colour['label']); ?>"><span class="screen-reader-text"><?php echo esc_html($colour['label']); ?></span></button>
                    <?php endforeach; ?>
                    </div>
                </div>

                <ul data-live-gathering-list>
                    <?php foreach ($members as $member) : ?>
                        <li style="--gmrt-fellowship-colour: <?php echo esc_attr((string) ($member['table_colour_hex'] ?? '#65b9ae')); ?>" class="gmrt-party__member gmrt-party__member--<?php echo esc_attr((string) ($member['role'] ?? 'player')); ?> gmrt-party__member--<?php echo esc_attr((string) ($member['status'] ?? 'unknown')); ?>">
                            <?php $avatarUrl = (string) ($member['avatar_url'] ?? ''); ?>
                            <span class="gmrt-party__avatar" aria-hidden="true">
                                <?php if ($avatarUrl !== '') : ?>
                                    <img src="<?php echo esc_url($avatarUrl); ?>" alt="">
                                <?php else : ?>
                                    <?php echo esc_html(substr((string) ($member['display_name'] ?? '?'), 0, 1)); ?>
                                <?php endif; ?>
                            </span>
                            <span class="gmrt-party__role">
                                <?php echo esc_html(
                                    ($member['role'] ?? '')
                                    === 'dungeon-master'
                                        ? 'DM'
                                        : 'Player'
                                ); ?>
                            </span>
                            <strong>
                                <?php echo esc_html(
                                    (string) (
                                        $member['display_name']
                                        ?? ('User #' . (string) ($member['user_id'] ?? ''))
                                    )
                                ); ?>
                            </strong>
                            <small class="screen-reader-text">
                                Membership status: <?php echo esc_html(
                                    (string) ($member['status'] ?? '')
                                ); ?>
                            </small>
                            <?php
                            $memberCharacter = (string) (
                                $member['companion_character_id']
                                ?? ''
                            );
                            $memberVitality = null;
                            $memberCombatantState = null;
                            $memberCompanionCharacter = is_array($member['companion_character'] ?? null)
                                ? $member['companion_character']
                                : null;
                            $memberCompanionPlay = is_array($memberCompanionCharacter['play'] ?? null)
                                ? $memberCompanionCharacter['play']
                                : [];
                            $memberCompanionHp = is_array($memberCompanionPlay['hit_points'] ?? null)
                                ? $memberCompanionPlay['hit_points']
                                : [];

                            foreach ($tokens as $partyToken) {
                                if (
                                    $memberCharacter !== ''
                                    && (string) (
                                        $partyToken['source_reference']
                                        ?? ''
                                    ) === $memberCharacter
                                ) {
                                    $partyTokenId = (string) (
                                        $partyToken['id']
                                        ?? ''
                                    );
                                    $memberVitality = $vitality[
                                        $partyTokenId
                                    ] ?? null;
                                    $memberCombatantState = $combatantStates[
                                        $partyTokenId
                                    ] ?? null;
                                    break;
                                }
                            }

                            if ($memberCompanionHp !== []) {
                                $memberCurrentHp = max(0, (int) ($memberCompanionHp['current'] ?? 0));
                                $memberMaximumHp = max(0, (int) ($memberCompanionHp['maximum'] ?? 0));
                                $memberTemporaryHp = max(0, (int) ($memberCompanionHp['temporary'] ?? 0));
                                $memberPercentage = $memberMaximumHp > 0
                                    ? min(100, max(0, (int) round(($memberCurrentHp / $memberMaximumHp) * 100)))
                                    : 0;
                                $memberVitality = [
                                    'current_hp' => $memberCurrentHp,
                                    'maximum_hp' => $memberMaximumHp,
                                    'temporary_hp' => $memberTemporaryHp,
                                    'percentage' => $memberPercentage,
                                ];
                            }
                            ?>
                            <?php if (
                                $state->isDungeonMaster()
                                && ($member['role'] ?? '') === 'player'
                                && ($member['status'] ?? '') !== 'left'
                            ) : ?>
                                <button
                                    type="button"
                                    class="gmrt-party__remove"
                                    data-remove-table-player
                                    data-user-id="<?php echo esc_attr((string) ($member['user_id'] ?? '')); ?>"
                                >Remove from Table</button>
                            <?php endif; ?>

                            <?php if (is_array($memberVitality)) : ?>
                                <div
                                    class="gmrt-hp"
                                    <?php if ($memberCharacter !== '') : ?>data-party-character-hp="<?php echo esc_attr($memberCharacter); ?>"<?php endif; ?>
                                    aria-label="<?php echo esc_attr(
                                        'Hit Points '
                                        . (string) $memberVitality['current_hp']
                                        . ' of '
                                        . (string) $memberVitality['maximum_hp']
                                    ); ?>"
                                >
                                    <div class="gmrt-hp__track">
                                        <span
                                            class="gmrt-hp__fill"
                                            style="--gmrt-hp: <?php echo esc_attr(
                                                (string) $memberVitality['percentage']
                                            ); ?>%;"
                                        ></span>
                                    </div>
                                    <small>
                                        HP <span data-party-current-hp><?php echo esc_html(
                                            (string) $memberVitality['current_hp']
                                        ); ?></span>/<span data-party-maximum-hp><?php echo esc_html(
                                            (string) $memberVitality['maximum_hp']
                                        ); ?></span>
                                        <?php if (
                                            (int) $memberVitality['temporary_hp'] > 0
                                        ) : ?>
                                            +<span data-party-temporary-hp><?php echo esc_html(
                                                (string) $memberVitality['temporary_hp']
                                            ); ?></span> temp
                                        <?php endif; ?>
                                    </small>
                                    <?php if (
                                        is_string($memberCombatantState)
                                        && ! in_array(
                                            $memberCombatantState,
                                            ['healthy', 'wounded'],
                                            true
                                        )
                                    ) : ?>
                                        <strong
                                            class="gmrt-hp__state gmrt-hp__state--<?php echo esc_attr(
                                                $memberCombatantState
                                            ); ?>"
                                        >
                                            <?php echo esc_html(
                                                strtoupper(
                                                    $memberCombatantState
                                                )
                                            ); ?>
                                        </strong>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <?php if ($state->isDungeonMaster()) : ?>
                    <div class="gmrt-gathering-controls">
                        <strong>The Summons to the Table</strong>
                        <p>Invite an existing WordPress user by username, email, or user ID. Their seat is reserved immediately and WordPress will also attempt to email a direct Table link.</p>
                        <form data-gathering-invite-form>
                            <label>
                                <span>Player</span>
                                <input type="text" name="player" autocomplete="off" required
                                    placeholder="username or email">
                            </label>
                            <button type="submit">Send Summons</button>
                        </form>
                        <span data-gathering-status role="status" aria-live="polite"></span>
                    </div>
                <?php endif; ?>

                <div class="gmrt-companion-link">
                    <strong>Great Marketrealm Companion</strong>
                    <?php if (! empty($companion['available'])) : ?>
                        <span class="is-connected">Connected<?php echo ! empty($companion['version']) ? ' · ' . esc_html((string) $companion['version']) : ''; ?></span>
                        <?php
                        $eligibleCharacters = is_array($companion['characters'] ?? null)
                            ? $companion['characters']
                            : [];
                        $selectedCharacter = is_array($companion['selected_character'] ?? null)
                            ? $companion['selected_character']
                            : null;
                        ?>
                        <?php if ($selectedCharacter !== null) : ?>
                            <p class="gmrt-character-gate__chosen">
                                <strong><?php echo esc_html((string) ($selectedCharacter['name'] ?? 'Adventurer')); ?></strong>
                                · Level <?php echo esc_html((string) ($selectedCharacter['level'] ?? '')); ?>
                                <?php echo esc_html((string) ($selectedCharacter['race'] ?? '')); ?>
                                <?php echo esc_html((string) ($selectedCharacter['class'] ?? '')); ?>
                            </p>
                        <?php endif; ?>
                        <?php if ($eligibleCharacters !== []) : ?>
                            <?php if ($selectedCharacter === null) : ?>
                                <div class="gmrt-character-gate__callout" role="status">
                                    <strong>Choose Your Adventurer</strong>
                                    <span>Select the Companion Character you are bringing into this Chamber to open your Satchel and place their token.</span>
                                </div>
                            <?php endif; ?>
                            <form class="gmrt-character-gate" data-companion-character-form>
                                <label>
                                    <span>Character at this Table</span>
                                    <select name="character_id" required>
                                        <option value="">Choose your adventurer…</option>
                                        <?php foreach ($eligibleCharacters as $eligibleCharacter) : ?>
                                            <option
                                                value="<?php echo esc_attr((string) ($eligibleCharacter['id'] ?? '')); ?>"
                                                <?php selected(
                                                    (string) ($eligibleCharacter['id'] ?? ''),
                                                    (string) ($selectedCharacter['id'] ?? '')
                                                ); ?>
                                            ><?php echo esc_html((string) ($eligibleCharacter['name'] ?? 'Adventurer')); ?> — Lv <?php echo esc_html((string) ($eligibleCharacter['level'] ?? '')); ?> <?php echo esc_html((string) ($eligibleCharacter['race'] ?? '')); ?> <?php echo esc_html((string) ($eligibleCharacter['class'] ?? '')); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                                <button type="submit">Bring Character to Table</button>
                                <span data-companion-character-status role="status" aria-live="polite"></span>
                            </form>
                        <?php else : ?>
                            <p>No eligible Companion Characters were found for this Guild account.</p>
                        <?php endif; ?>
                    <?php else : ?>
                        <span>Not detected</span>
                        <p>The Tabletop remains fully usable with manual combatants and NPCs.</p>
                    <?php endif; ?>
                </div>

                <div class="gmrt-party__note">
                    <strong>
                        Chamber mode
                    </strong>
                    <p>
                        Select a token and use the arrow keys,
                        or click a position on the battlemap.
                        Movement is validated and persisted by
                        the Tabletop server.
                    </p>
                </div>


                <div data-live-battle-log-slot>
                <section
                    class="gmrt-battle-log"
                    aria-labelledby="gmrt-chronicle-title"
                    data-table-chronicle
                    data-chronicle-mode="<?php echo $encounter !== null ? 'battle' : 'chamber'; ?>"
                >
                    <div class="gmrt-battle-log__heading">
                        <div>
                            <p class="gmrt-chamber__eyebrow" data-chronicle-eyebrow>
                                <?php echo $encounter !== null ? 'Battle Chronicle' : 'Chamber Chronicle'; ?>
                            </p>
                            <h3 id="gmrt-chronicle-title" data-chronicle-title>
                                <?php echo $encounter !== null ? 'Deeds at the Table' : 'Tales from the Chamber'; ?>
                            </h3>
                        </div>
                        <span>Latest 12</span>
                    </div>

                    <ol data-battle-log data-chronicle-log>
                        <?php foreach ($chronicleLog as $entry) : ?>
                            <li data-battle-log-entry data-chronicle-log-entry style="--gmrt-fellowship-colour: <?php echo esc_attr((string) (($entry['table_colour']['hex'] ?? null) ?: '#8f8779')); ?>">
                                <small>
                                    <?php if ($encounter !== null) : ?>
                                        Round <?php echo esc_html((string) ($entry['round'] ?? 0)); ?>
                                    <?php else : ?>
                                        At the Table
                                    <?php endif; ?>
                                </small>
                                <span><?php echo esc_html((string) ($entry['summary'] ?? '')); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ol>

                    <p
                        class="gmrt-battle-log__empty"
                        data-battle-log-empty
                        data-chronicle-log-empty
                        <?php echo $chronicleLog !== [] ? 'hidden' : ''; ?>
                    >
                        <?php echo $encounter !== null
                            ? 'No deeds have been chronicled yet.'
                            : 'No Chamber rolls have been chronicled yet.'; ?>
                    </p>
                </section>
                </div>
            </aside>
        </div>
    <?php endif; ?>

    <div
        class="gmrt-chamber__status"
        id="gmrt-tabletop-status"
        role="status"
        aria-live="polite"
    ></div>
</main>

