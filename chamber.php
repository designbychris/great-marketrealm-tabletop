<?php

use GreatMarketrealmTabletop\Tabletop\Models\TabletopChamberState;
use GreatMarketrealmTabletop\Tabletop\Presentation\CompanionTokenImageSource;
use GreatMarketrealmTabletop\Tables\Memberships\Models\TableColourPalette;

defined('ABSPATH') || exit;

/** @var TabletopChamberState|null $state */
/** @var string|null $message */
/** @var bool $canPrepareTestTable */
/** @var array<string,mixed>|null $invitation */
/** @var array<string,mixed>|null $entryDoor */
/** @var array<string,mixed>|null $campaignLobby */

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
$dungeonForge = is_array($integrations['dungeon_forge'] ?? null) ? $integrations['dungeon_forge'] : [];
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

$currentTokenId = (string) ($encounter['current_token_id'] ?? '');
$currentArsenal = $currentTokenId !== ''
    ? ($arsenals[$currentTokenId]['attacks'] ?? [])
    : [];

$sceneSurfaceKind = (string) ($scene['surface_kind'] ?? 'image');
$sceneIsGenerated = $scene !== null && $sceneSurfaceKind === 'generated';
$sceneImage = ($scene !== null && ! $sceneIsGenerated)
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
                <section class="gmrt-satchel__battle" data-satchel-combat-home>
                    <div>
                        <p class="gmrt-chamber__eyebrow">IV.29C.1 · Eyes on the Enemy</p>
                        <div class="gmrt-satchel__battle-heading"><h3>Battle Actions</h3><span class="gmrt-player-turn-badge" data-player-turn-badge hidden>YOUR TURN</span></div>
                        <small data-satchel-turn-hint>Your Attack, Dash, Disengage, Dodge and Help controls appear here when this adventurer has the turn.</small>
                    </div>
                    <div data-satchel-combat-mount></div>
                </section>
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

    <?php if (! is_array($entryDoor) && ! is_array($invitation) && ! is_array($campaignLobby)) : ?>
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

        <?php if ($state !== null && $scene !== null) : ?>
            <div class="gmrt-table-command__scene" aria-label="Current Tabletop Scene">
                <span><?php echo $encounter !== null ? 'Battle' : 'Exploration Mode'; ?></span>
                <strong><?php echo esc_html((string) ($scene['name'] ?? 'Active Scene')); ?></strong>
                <small>
                    <?php echo esc_html((string) ($scene['grid_type'] ?? 'square')); ?>
                    <?php if (($scene['grid_type'] ?? '') === 'square') : ?>
                        · <?php echo esc_html((string) ($scene['grid_size'] ?? '')); ?> px grid
                    <?php endif; ?>
                </small>
            </div>
        <?php endif; ?>

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
    <?php endif; ?>


    <?php if ($state !== null && $state->isDungeonMaster()) : ?>
        <aside class="gmrt-atlas-drawer" data-keepers-atlas data-open="false" aria-label="The Keeper's Atlas">
            <button class="gmrt-atlas-drawer__toggle" type="button" data-atlas-toggle aria-expanded="false" aria-controls="gmrt-keepers-atlas-panel"><span>Atlas</span></button>
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
                            $atlasGenerated = (string) ($atlasScene['surface_kind'] ?? 'image') === 'generated';
                            $atlasImage = $atlasGenerated ? false : wp_get_attachment_image_url(
                                (int) ($atlasScene['map_attachment_id'] ?? 0),
                                'medium'
                            );
                            $atlasActive = ! empty($atlasScene['active']);
                        ?>
                            <article class="gmrt-atlas-card<?php echo $atlasActive ? ' is-active' : ''; ?><?php echo $atlasGenerated ? ' is-forged-world' : ''; ?>" data-atlas-scene="<?php echo esc_attr((string) ($atlasScene['id'] ?? '')); ?>">
                                <div class="gmrt-atlas-card__image">
                                    <?php if (is_string($atlasImage) && $atlasImage !== '') : ?>
                                        <img src="<?php echo esc_url($atlasImage); ?>" alt="" loading="lazy">
                                    <?php elseif ($atlasGenerated) : ?>
                                        <span class="gmrt-atlas-card__forge-sigil" aria-hidden="true">⚒</span>
                                    <?php else : ?>
                                        <span aria-hidden="true">◇</span>
                                    <?php endif; ?>
                                </div>
                                <div class="gmrt-atlas-card__copy">
                                    <strong><?php echo esc_html((string) ($atlasScene['name'] ?? 'Unnamed Scene')); ?></strong>
                                    <small>
                                        <?php echo esc_html((string) ((int) ($atlasScene['width'] ?? 0))); ?> ×
                                        <?php echo esc_html((string) ((int) ($atlasScene['height'] ?? 0))); ?> ·
                                        <?php echo esc_html((string) ($atlasScene['grid_type'] ?? 'gridless')); ?><?php echo $atlasGenerated ? ' · Forged World' : ''; ?>
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

                <!-- Legacy IV.30.2 UI contracts: Generate Dungeon · The Cartographer's Dungeon Forge -->
                <details class="gmrt-atlas-forge" data-atlas-dungeon-forge>
                    <summary><span aria-hidden="true">⚒</span> Generate Scene</summary>
                    <div class="gmrt-atlas-forge__body">
                        <p>Pippin can create a complete playable Scene from nothing. No background image is required. Scene Type chooses the topology; Theme chooses its Great Marketrealm surface treatment. Grid, vision, Fog and Keeper lights remain Tabletop-native.</p>

                        <details class="gmrt-pippin-desk">
                            <summary>Meet the Wandering Cartographer</summary>
                            <div class="gmrt-pippin-desk__portrait">
                                <img src="<?php echo esc_url(GMRT_URL . 'assets/images/pippin-peppercorn-cartographer.png'); ?>" alt="Pippin Peppercorn surveying a dungeon while a Milk Mimic lurks behind him." loading="lazy">
                            </div>
                            <div class="gmrt-pippin-desk__copy">
                                <strong>Pippin Peppercorn</strong>
                                <span>The Wandering Cartographer</span>
                                <p>Surveyor of dungeons, forests, villages and anything else that stays still long enough to be measured. Pippin remains professionally suspicious of grass.</p>
                            </div>
                        </details>

                        <aside class="gmrt-pippin-note" data-pippin-field-note aria-label="Pippin's Field Note">
                            <img class="gmrt-pippin-note__portrait" src="<?php echo esc_url(GMRT_URL . 'assets/images/pippin-peppercorn-pixel.png'); ?>" alt="" aria-hidden="true">
                            <div class="gmrt-pippin-note__bubble">
                                <span class="gmrt-pippin-note__label">Pippin's Field Note</span>
                                <p data-pippin-field-note-copy>Same seed, Scene Type and scale means the same terrain. Change the Theme and I shall redecorate without moving the walls. Cartographical integrity!</p>
                            </div>
                        </aside>

                        <div class="gmrt-atlas-forge__controls">
                            <label>
                                Scene name
                                <input type="text" maxlength="120" value="Pippin's Unexpected Detour" data-atlas-forge-name>
                            </label>
                            <label>
                                Environment
                                <select data-atlas-forge-scene-type>
                                    <option value="dungeon" selected>Dungeon</option>
                                    <option value="forest">Forest</option>
                                    <option value="village">Village</option>
                                </select>
                            </label>
                            <label>
                                Seed
                                <input type="text" maxlength="80" value="Peppercorn-01" data-atlas-forge-seed>
                            </label>
                            <label>
                                Scale
                                <select data-atlas-forge-style>
                                    <option value="compact">Compact</option>
                                    <option value="standard" selected>Standard</option>
                                    <option value="grand">Grand</option>
                                </select>
                            </label>
                            <label>
                                Theme
                                <select data-atlas-forge-theme>
                                    <option value="pantry-stone" selected>Pantry Stone</option>
                                    <option value="butcher-cellar">Butcher Cellar</option>
                                    <option value="rootland-cavern">Rootland Cavern</option>
                                    <option value="frostreem-vault">Frostreem Vault</option>
                                    <option value="bakery-crypt">Bakery Crypt</option>
                                    <option value="mushroom-grotto">Mushroom Grotto</option>
                                </select>
                            </label>
                            <button type="button" data-atlas-forge-reroll>New Seed</button>
                            <button type="button" data-atlas-forge-create>Forge New Scene</button>
                        </div>
                        <small data-atlas-forge-status role="status" aria-live="polite">The Forge will add a new Scene to the Atlas and open it Behind the Curtain for inspection.</small>
                    </div>
                </details>
            </div>
            </div>
        </aside>
    <?php endif; ?>

    <?php if ($state !== null && $state->isDungeonMaster()) : ?>
        <aside class="gmrt-bestiary-drawer" data-keepers-bestiary data-open="false" aria-label="The Keeper's Bestiary">
            <button class="gmrt-bestiary-drawer__toggle" type="button" data-bestiary-toggle aria-expanded="false" aria-controls="gmrt-keepers-bestiary-panel"><span aria-hidden="true">🐲</span><span>Bestiary</span></button>
            <div class="gmrt-bestiary gmrt-bestiary-drawer__panel" id="gmrt-keepers-bestiary-panel">
                <header class="gmrt-bestiary__header">
                    <div><p class="gmrt-chamber__eyebrow">Dungeon Master's Drawer · IV.29D</p><h2>The Keeper's Bestiary</h2><small><?php echo esc_html((string) count($bestiary)); ?> creature record<?php echo count($bestiary) === 1 ? '' : 's'; ?></small></div>
                    <button type="button" data-bestiary-close aria-label="Close the Keeper's Bestiary">×</button>
                </header>
                <div class="gmrt-bestiary__body">
                    <p class="gmrt-bestiary__introduction">Browse the Keeper’s Menagerie: Tabletop training creatures plus published Companion creatures, then summon Scene-owned snapshots onto the live map or a privately prepared Scene.</p>
                    <label class="gmrt-bestiary__search">Search the shelves<input type="search" autocomplete="off" placeholder="Name, kind, attack, damage…" data-bestiary-search></label>
                    <div class="gmrt-bestiary__filters" role="group" aria-label="Filter Bestiary records by deployment">
                        <button type="button" data-bestiary-filter="all" aria-pressed="true">All <span data-bestiary-filter-count="all"><?php echo esc_html((string) count($bestiary)); ?></span></button>
                        <button type="button" data-bestiary-filter="on-map" aria-pressed="false">On This Map <span data-bestiary-filter-count="on-map">0</span></button>
                        <button type="button" data-bestiary-filter="not-on-map" aria-pressed="false">Not On This Map <span data-bestiary-filter-count="not-on-map">0</span></button>
                    </div>
                    <small data-bestiary-results aria-live="polite"><?php echo esc_html((string) count($bestiary)); ?> records shown</small>
                    <div class="gmrt-bestiary__register" data-bestiary-register>
                        <?php foreach ($bestiary as $creature) :
                            if (! is_array($creature)) continue;
                            $attacks = is_array($creature['attacks'] ?? null) ? $creature['attacks'] : [];
                            $resistances = is_array($creature['resistances'] ?? null) ? $creature['resistances'] : [];
                            $immunities = is_array($creature['immunities'] ?? null) ? $creature['immunities'] : [];
                            $weaknesses = is_array($creature['weaknesses'] ?? null) ? $creature['weaknesses'] : [];
                            $traits = is_array($creature['traits'] ?? null) ? $creature['traits'] : [];
                            $creatureId = (string) ($creature['id'] ?? '');
                            $creatureSource = 'gmrt-bestiary:' . $creatureId;
                            $deployedInstances = array_values(array_filter(
                                $tokens,
                                static fn (array $token): bool => (string) ($token['source_reference'] ?? '') === $creatureSource
                            ));
                            $creatureHasTurn = false;
                            foreach ($deployedInstances as $instance) {
                                if ((string) ($instance['id'] ?? '') === $currentTokenId) {
                                    $creatureHasTurn = true;
                                    break;
                                }
                            }
                            $searchParts = [(string) ($creature['name'] ?? ''), (string) ($creature['kind'] ?? ''), (string) ($creature['size'] ?? '')];
                            foreach ($attacks as $attack) {
                                if (! is_array($attack)) continue;
                                $searchParts[] = (string) ($attack['name'] ?? '');
                                $searchParts[] = (string) (($attack['damage']['type'] ?? ''));
                            }
                            $searchParts = array_merge($searchParts, $resistances, $immunities, $weaknesses, $traits);
                        ?>
                            <article class="gmrt-bestiary-card<?php echo $creatureHasTurn ? ' is-active-turn' : ''; ?>" data-bestiary-card data-bestiary-creature-id="<?php echo esc_attr($creatureId); ?>" data-bestiary-on-map="<?php echo $deployedInstances !== [] ? '1' : '0'; ?>" data-bestiary-deployed-count="<?php echo esc_attr((string) count($deployedInstances)); ?>" data-bestiary-search-text="<?php echo esc_attr(strtolower(implode(' ', array_map('strval', $searchParts)))); ?>">
                                <header><div class="gmrt-bestiary-card__sigil" aria-hidden="true">◆</div><div><strong><?php echo esc_html((string) ($creature['name'] ?? 'Unknown Creature')); ?></strong><small><?php echo esc_html((string) ($creature['size'] ?? 'Unknown')); ?> · <?php echo esc_html((string) ($creature['kind'] ?? 'creature')); ?></small></div><?php if ($deployedInstances !== []) : ?><span class="gmrt-bestiary-card__map-badge">ON MAP · ×<?php echo esc_html((string) count($deployedInstances)); ?></span><?php endif; ?></header>
                                <dl class="gmrt-bestiary-card__measures"><div><dt>AC</dt><dd><?php echo esc_html((string) ($creature['armor_class'] ?? '—')); ?></dd></div><div><dt>HP</dt><dd><?php echo esc_html((string) ($creature['hit_points'] ?? '—')); ?></dd></div><div><dt>Speed</dt><dd><?php echo esc_html((string) ($creature['speed_feet'] ?? '—')); ?> ft</dd></div></dl>
                                <details class="gmrt-bestiary-card__record">
                                    <summary>Inspect creature record</summary>
                                    <?php if ($attacks !== []) : ?><section><h3>Actions</h3><ul><?php foreach ($attacks as $attack) : if (! is_array($attack)) continue; $damage = is_array($attack['damage'] ?? null) ? $attack['damage'] : []; ?><li><strong><?php echo esc_html((string) ($attack['name'] ?? 'Attack')); ?></strong><span>+<?php echo esc_html((string) ($attack['attack_modifier'] ?? 0)); ?> · <?php echo esc_html((string) ($attack['range_feet'] ?? 5)); ?><?php if ((int) ($attack['long_range_feet'] ?? 5) > (int) ($attack['range_feet'] ?? 5)) : ?>/<?php echo esc_html((string) ($attack['long_range_feet'] ?? 5)); ?><?php endif; ?> ft · <?php echo esc_html((string) ($damage['dice_count'] ?? 1)); ?>d<?php echo esc_html((string) ($damage['die_sides'] ?? 4)); ?><?php $mod = (int) ($damage['modifier'] ?? 0); if ($mod !== 0) echo esc_html(($mod > 0 ? '+' : '') . (string) $mod); ?> <?php echo esc_html((string) ($damage['type'] ?? 'damage')); ?></span></li><?php endforeach; ?></ul></section><?php endif; ?>
                                    <?php if ($resistances !== [] || $immunities !== [] || $weaknesses !== []) : ?><section><h3>Defences</h3><?php if ($resistances !== []) : ?><p><strong>Resists:</strong> <?php echo esc_html(implode(', ', $resistances)); ?></p><?php endif; ?><?php if ($immunities !== []) : ?><p><strong>Immune:</strong> <?php echo esc_html(implode(', ', $immunities)); ?></p><?php endif; ?><?php if ($weaknesses !== []) : ?><p><strong>Weak:</strong> <?php echo esc_html(implode(', ', $weaknesses)); ?></p><?php endif; ?></section><?php endif; ?>
                                    <?php if ($traits !== []) : ?><section><h3>Traits</h3><ul><?php foreach ($traits as $trait) : ?><li><?php echo esc_html((string) $trait); ?></li><?php endforeach; ?></ul></section><?php endif; ?>
                                    <small class="gmrt-bestiary-card__source">Definition: <?php echo esc_html((string) ($creature['source'] ?? 'gmrt-bestiary')); ?></small>
                                </details>
                                <?php if ($deployedInstances !== []) : ?>
                                    <section class="gmrt-bestiary-card__instances" aria-label="Deployed instances">
                                        <h3>At the Table</h3>
                                        <?php foreach ($deployedInstances as $instance) :
                                            $instanceId = (string) ($instance['id'] ?? '');
                                            $instanceVitality = is_array($vitality[$instanceId] ?? null) ? $vitality[$instanceId] : [];
                                            $instanceConditions = is_array($conditions[$instanceId] ?? null) ? $conditions[$instanceId] : [];
                                            $instanceHasTurn = $instanceId !== '' && $instanceId === $currentTokenId;
                                        ?>
                                            <div class="gmrt-bestiary-instance<?php echo $instanceHasTurn ? ' is-active-turn' : ''; ?>" data-bestiary-instance-id="<?php echo esc_attr($instanceId); ?>">
                                                <div class="gmrt-bestiary-instance__summary">
                                                    <strong><?php echo esc_html((string) ($instance['label'] ?? $creature['name'] ?? 'Creature')); ?></strong>
                                                    <?php if ($instanceVitality !== []) : ?><small><span data-bestiary-instance-hp><?php echo esc_html((string) ($instanceVitality['current_hp'] ?? '—')); ?>/<?php echo esc_html((string) ($instanceVitality['maximum_hp'] ?? '—')); ?></span> HP</small><?php endif; ?>
                                                    <small data-bestiary-instance-conditions><?php echo esc_html($instanceConditions === [] ? 'No conditions' : implode(', ', array_map(static fn (array $condition): string => (string) ($condition['condition'] ?? $condition['type'] ?? ''), $instanceConditions))); ?></small>
                                                </div>
                                                <span class="gmrt-bestiary-instance__turn"<?php echo $instanceHasTurn ? '' : ' hidden'; ?> data-bestiary-turn-badge>ACTIVE TURN</span>
                                                <div data-bestiary-combat-mount></div>
                                            </div>
                                        <?php endforeach; ?>
                                    </section>
                                <?php endif; ?>
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
                    <p class="gmrt-bestiary__empty" data-bestiary-empty hidden>No creature records match the current search and map filter.</p>
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

    <div
                class="gmrt-deeds gmrt-combat-dock"
                aria-label="Current combatant actions"
                data-combat-dock
                data-current-token="<?php echo esc_attr($currentTokenId); ?>"
                hidden
            >
                <label class="gmrt-deeds__attack">
                    <span>Attack</span>
                    <select data-arsenal-attack<?php echo $currentArsenal === [] ? ' disabled' : ''; ?>>
                        <?php if ($currentArsenal === []) : ?>
                            <option value="">No attack readied</option>
                        <?php else : ?>
                            <?php foreach ($currentArsenal as $arsenalAttack) :
                                $combat = $arsenalAttack['combat'] ?? [];
                                $damage = $arsenalAttack['damage'] ?? [];
                                $normal = (int) ($combat['attack_range_feet'] ?? 5);
                                $long = (int) ($combat['long_range_feet'] ?? $normal);
                                $rangeLabel = $long > $normal ? $normal . '/' . $long . ' ft' : $normal . ' ft';
                                $diceLabel = (int) ($damage['dice_count'] ?? 1) . 'd' . (int) ($damage['die_sides'] ?? 6);
                                $damageModifier = (int) ($damage['modifier'] ?? 0);
                                if ($damageModifier > 0) $diceLabel .= '+' . $damageModifier;
                                elseif ($damageModifier < 0) $diceLabel .= (string) $damageModifier;
                            ?>
                                <option value="<?php echo esc_attr((string) ($arsenalAttack['id'] ?? '')); ?>">
                                    <?php echo esc_html((string) ($arsenalAttack['name'] ?? 'Attack') . ' · ' . $diceLabel . ' ' . strtoupper((string) ($damage['damage_type'] ?? '')) . ' · ' . $rangeLabel); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </label>
                <label class="gmrt-deeds__target">
                    <span>Target</span>
                    <select data-attack-target>
                        <option value="">Choose target…</option>
                        <?php foreach ($tokens as $targetToken) :
                            if ((string) ($targetToken['id'] ?? '') === $currentTokenId) continue;
                        ?>
                            <option value="<?php echo esc_attr((string) ($targetToken['id'] ?? '')); ?>"><?php echo esc_html((string) ($targetToken['label'] ?? 'Token')); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <div class="gmrt-combat-dock__deeds">
                    <?php foreach (
                        ['attack' => 'Attack', 'dash' => 'Dash', 'disengage' => 'Disengage', 'dodge' => 'Dodge', 'help' => 'Help']
                        as $deedKey => $deedLabel
                    ) : ?>
                        <button type="button" class="gmrt-deed" data-battle-deed="<?php echo esc_attr($deedKey); ?>"><?php echo esc_html($deedLabel); ?></button>
                    <?php endforeach; ?>
                </div>
                <span class="gmrt-target-range" data-target-range-status role="status" aria-live="polite" aria-label="Target status">NO TARGET SELECTED</span>
                <small class="gmrt-combat-dock__hint">Choose a visible battlefield target for attacks. Range and legality are reported in Turn of Battle.</small>
            </div>

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
            data-encounter-round="<?php echo esc_attr(
                (string) ($encounter['round'] ?? 0)
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

            <div class="gmrt-combat-guidance" data-combat-guidance>
                <span class="gmrt-combat-turn-badge" data-combat-turn-badge hidden>YOUR TURN</span>
                <span>Battle actions live with the combatant.</span>
                <strong data-combat-guidance-copy>
                    <?php echo $state !== null && $state->isDungeonMaster()
                        ? 'Use the Keeper\'s Bestiary for creature actions; End Turn remains here.'
                        : 'Use the Adventurer\'s Satchel when your turn arrives.'; ?>
                </strong>
                <div data-combat-status-mount></div>
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
                    <button
                        type="button"
                        class="gmrt-diceworks__damage-roll"
                        data-roll-attack-damage
                        hidden
                    >
                        Roll Damage
                    </button>
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

    <?php if (is_array($entryDoor)) : ?>
        <section class="gmrt-table-door" aria-labelledby="gmrt-table-door-title">
            <div class="gmrt-table-door__scene" aria-hidden="true">
                <img
                    src="<?php echo esc_url((string) ($entryDoor['art_url'] ?? '')); ?>"
                    alt=""
                    class="gmrt-table-door__art"
                >
            </div>
            <div class="gmrt-table-door__panel">
                <p class="gmrt-chamber__eyebrow">Great Marketrealm Tabletop</p>
                <h1 id="gmrt-table-door-title">Beyond This Door, the Table Awaits</h1>
                <p class="gmrt-table-door__lede">
                    Sign in with your Great Marketrealm Companion account. The Tabletop uses the same account and the same trusted WordPress session beneath the Door.
                </p>
                <?php if (! empty($entryDoor['error'])) : ?>
                    <div class="gmrt-table-door__error" role="alert">
                        <?php echo esc_html((string) $entryDoor['error']); ?>
                    </div>
                <?php endif; ?>
                <form class="gmrt-table-door__form" method="post" action="<?php echo esc_url((string) ($entryDoor['action_url'] ?? '')); ?>">
                    <input type="hidden" name="gmrt_tabletop_login" value="1">
                    <input type="hidden" name="gmrt_tabletop_login_nonce" value="<?php echo esc_attr((string) ($entryDoor['nonce'] ?? '')); ?>">
                    <label>
                        <span>Username or email</span>
                        <input type="text" name="log" autocomplete="username" required autofocus>
                    </label>
                    <label>
                        <span>Password</span>
                        <input type="password" name="pwd" autocomplete="current-password" required>
                    </label>
                    <label class="gmrt-table-door__remember">
                        <input type="checkbox" name="rememberme" value="1">
                        <span>Keep me signed in on this device</span>
                    </label>
                    <button class="gmrt-table-door__enter" type="submit">
                        <span>Enter the Tabletop</span><i aria-hidden="true">▶</i>
                    </button>
                </form>
                <small class="gmrt-table-door__promise">
                    Your requested Table and invitation link stay attached to this Door while you sign in.
                </small>
                <aside class="gmrt-table-door__pippin" aria-label="Pippin's note">
                    <img src="<?php echo esc_url(GMRT_URL . 'assets/images/pippin-peppercorn-pixel.png'); ?>" alt="" aria-hidden="true">
                    <p><strong>Pippin says:</strong> “I checked the hinges. Twice. The door is almost certainly not a Mimic.”</p>
                </aside>
            </div>
        </section>
    <?php elseif (is_array($invitation)) : ?>
        <section class="gmrt-invitation-threshold" aria-labelledby="gmrt-invitation-title">
            <div class="gmrt-invitation-threshold__scene" aria-hidden="true">
                <img src="<?php echo esc_url(GMRT_URL . 'assets/images/pippin-peppercorn-cartographer.png'); ?>" alt="">
            </div>
            <div class="gmrt-gathering-invitation" role="status">
                <p class="gmrt-chamber__eyebrow">The Gathering at the Table</p>
                <h2 id="gmrt-invitation-title">Your chair is waiting</h2>
                <p>
                    The Dungeon Master has invited you to join this Table.
                    Taking your seat creates your persistent Table membership;
                    your character remains a separate choice.
                </p>
                <button type="button" data-accept-table-invitation>
                    Take My Seat
                </button>
                <span data-gathering-status role="status" aria-live="polite"></span>
            </div>
        </section>
    <?php elseif (is_array($campaignLobby)) : ?>
        <section class="gmrt-campaign-lobby gmrt-wayfinder" aria-labelledby="gmrt-campaign-lobby-title">
            <?php $campaignArt = (string) ($campaignLobby['art_url'] ?? ''); ?>
            <?php if ($campaignArt !== '') : ?>
                <div class="gmrt-wayfinder__scene" aria-hidden="true"><img src="<?php echo esc_url($campaignArt); ?>" alt=""></div>
            <?php endif; ?>
            <div class="gmrt-wayfinder__content">
                <header class="gmrt-campaign-lobby__header">
                    <div>
                        <p class="gmrt-chamber__eyebrow">IV.33.3 · Pippin Remembers the Way</p>
                        <h2 id="gmrt-campaign-lobby-title">Pippin's Table Atlas</h2>
                        <p>Every road back to the adventure, carefully recorded. Probably in the correct pocket.</p>
                    </div>
                </header>

                <?php $campaignTables = is_array($campaignLobby['tables'] ?? null) ? $campaignLobby['tables'] : []; ?>
                <?php if ($campaignTables !== []) : ?>
                    <div class="gmrt-campaign-lobby__shelf" aria-label="Your Tabletop campaigns">
                        <?php foreach ($campaignTables as $campaignTable) : ?>
                            <?php $isOwner = ! empty($campaignTable['is_owner']); ?>
                            <?php $membershipStatus = (string) ($campaignTable['membership_status'] ?? ''); ?>
                            <article class="gmrt-campaign-card<?php echo $isOwner ? ' is-keeper' : ' is-adventurer'; ?>">
                                <p class="gmrt-chamber__eyebrow">
                                    <?php echo esc_html($isOwner ? 'Dungeon Master' : ($membershipStatus === 'invited' ? 'Your chair is waiting' : 'Adventurer')); ?>
                                    · <?php echo esc_html(ucfirst((string) ($campaignTable['status'] ?? 'preparing'))); ?>
                                </p>
                                <h3><?php echo esc_html((string) ($campaignTable['name'] ?? 'Untitled Table')); ?></h3>
                                <?php if ((string) ($campaignTable['description'] ?? '') !== '') : ?>
                                    <p><?php echo esc_html((string) $campaignTable['description']); ?></p>
                                <?php endif; ?>
                                <a class="gmrt-campaign-card__open" href="<?php echo esc_url(add_query_arg('table', (string) ($campaignTable['id'] ?? ''))); ?>">
                                    <?php echo esc_html($membershipStatus === 'invited' && ! $isOwner ? 'Take My Seat' : 'Return to Table'); ?>
                                </a>

                                <?php if ($isOwner) : ?>
                                    <?php $campaignRoster = is_array($campaignTable['roster'] ?? null) ? $campaignTable['roster'] : []; ?>
                                    <?php $campaignPlayers = array_values(array_filter($campaignRoster, static fn ($member): bool => ($member['role'] ?? '') === 'player' && ($member['status'] ?? '') !== 'left')); ?>
                                    <details class="gmrt-campaign-card__doors">
                                        <summary>Manage Players <span><?php echo esc_html((string) count($campaignPlayers)); ?> player<?php echo count($campaignPlayers) === 1 ? '' : 's'; ?></span></summary>
                                        <div class="gmrt-campaign-card__roster">
                                            <strong>The Gathering</strong>
                                            <?php if ($campaignRoster !== []) : ?>
                                                <ul>
                                                    <?php foreach ($campaignRoster as $campaignMember) : ?>
                                                        <li><span><?php echo esc_html((string) ($campaignMember['display_name'] ?? ('User #' . (string) ($campaignMember['user_id'] ?? '')))); ?><small><?php echo esc_html(ucwords(str_replace('-', ' ', (string) ($campaignMember['role'] ?? 'player')))); ?> · <?php echo esc_html(ucfirst((string) ($campaignMember['status'] ?? 'unknown'))); ?></small></span><?php if (($campaignMember['role'] ?? '') === 'player' && ($campaignMember['status'] ?? '') !== 'left') : ?><button type="button" data-campaign-remove-player data-table-id="<?php echo esc_attr((string) ($campaignTable['id'] ?? '')); ?>" data-user-id="<?php echo esc_attr((string) ($campaignMember['user_id'] ?? '')); ?>">Remove</button><?php endif; ?></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php endif; ?>
                                            <form data-campaign-invite-form data-table-id="<?php echo esc_attr((string) ($campaignTable['id'] ?? '')); ?>"><label><span>Summon a player</span><input type="text" name="player" autocomplete="off" required placeholder="username or email"></label><button type="submit">Send Summons</button></form>
                                            <span data-campaign-gathering-status role="status" aria-live="polite"></span>
                                        </div>
                                    </details>
                                    <details class="gmrt-campaign-card__remove">
                                        <summary>Remove this Tabletop</summary>
                                        <p>This permanently removes the campaign from the Table Atlas and closes its saved route. This cannot be undone.</p>
                                        <button type="button" data-remove-tabletop data-table-id="<?php echo esc_attr((string) ($campaignTable['id'] ?? '')); ?>" data-table-name="<?php echo esc_attr((string) ($campaignTable['name'] ?? 'this Tabletop')); ?>">Remove Tabletop</button>
                                        <span data-remove-tabletop-status role="status" aria-live="polite"></span>
                                    </details>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <p class="gmrt-campaign-lobby__empty">Pippin has no saved roads for you yet. A Keeper can set a new Table, or an Adventurer can return after receiving a Summons.</p>
                <?php endif; ?>

                <?php if (! empty($campaignLobby['may_create'])) : ?>
                    <?php $atlasScenes = is_array($campaignLobby['atlas_scenes'] ?? null) ? $campaignLobby['atlas_scenes'] : []; ?>
                    <form class="gmrt-create-tabletop" data-create-tabletop>
                        <div>
                            <p class="gmrt-chamber__eyebrow">IV.33.4 · The First Map on the Table</p>
                            <h3>Create a New Tabletop</h3>
                            <p>Name the campaign, then tell Pippin how its first map should arrive.</p>
                        </div>
                        <label><span>Campaign name</span><input type="text" name="name" maxlength="120" required placeholder="e.g. The Mystery of the Missing Marmalade"></label>
                        <label><span>Campaign description <small>(optional)</small></span><textarea name="description" maxlength="500" rows="3" placeholder="A short note about the adventure waiting at this Table…"></textarea></label>

                        <fieldset class="gmrt-first-map" data-first-map-choices>
                            <legend>The first map on the Table</legend>
                            <label class="gmrt-first-map__choice">
                                <input type="radio" name="first_map" value="blank" checked>
                                <span><strong>Begin with a Blank Table</strong><small>Open a clean generated Scene and furnish it whenever you are ready.</small></span>
                            </label>
                            <label class="gmrt-first-map__choice<?php echo $atlasScenes === [] ? ' is-unavailable' : ''; ?>">
                                <input type="radio" name="first_map" value="atlas"<?php echo $atlasScenes === [] ? ' disabled' : ''; ?>>
                                <span><strong>Choose from the Atlas</strong><small><?php echo $atlasScenes === [] ? 'No reusable uploaded Atlas maps are available yet.' : 'Copy one of your saved uploaded maps and its grid calibration into the new campaign.'; ?></small></span>
                            </label>
                            <label class="gmrt-first-map__choice">
                                <input type="radio" name="first_map" value="forge">
                                <span><strong>Ask Pippin to Forge a Scene</strong><small>Enter the new campaign at Pippin's Scene Forge with a fresh workbench waiting.</small></span>
                            </label>
                        </fieldset>

                        <?php if ($atlasScenes !== []) : ?>
                            <label class="gmrt-first-map__atlas" data-first-map-atlas hidden>
                                <span>Saved Atlas map</span>
                                <select name="atlas_source">
                                    <option value="">Choose a map…</option>
                                    <?php foreach ($atlasScenes as $atlasScene) : ?>
                                        <option value="<?php echo esc_attr((string) ($atlasScene['table_id'] ?? '') . '::' . (string) ($atlasScene['scene_id'] ?? '')); ?>">
                                            <?php echo esc_html((string) ($atlasScene['table_name'] ?? 'Table') . ' — ' . (string) ($atlasScene['scene_name'] ?? 'Scene')); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small>The original Scene stays exactly where it is; this creates an independent opening Scene using the same map surface and grid.</small>
                            </label>
                        <?php endif; ?>

                        <button type="submit">Set the Table</button><span data-create-tabletop-status role="status" aria-live="polite"></span>
                    </form>
                <?php endif; ?>

                <?php if (! empty($canPrepareTestTable)) : ?><details class="gmrt-testing-grounds"><summary>Development tools</summary><button type="button" class="gmrt-test-table-button" data-prepare-test-table>Prepare Test Table</button><small class="gmrt-test-table-note">Creates Sage's Combat Testing Grounds for regression and screen testing.</small></details><?php endif; ?>
            </div>
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

                <?php if ($state->isDungeonMaster()) : ?>
                    <details class="gmrt-keeper-controls" data-keeper-controls>
                        <summary>Dungeon Master Controls</summary>
                        <div class="gmrt-keeper-controls__body">
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

                    <div class="gmrt-lantern-rack" data-lantern-rack>
                        <strong>The Keeper's Lantern Rack</strong>
                        <span>Choose a light, then click the battlemap to place it. Scene-owned lights may be prepared Behind the Curtain.</span>
                        <div class="gmrt-lantern-rack__tools">
                            <button type="button" data-keeper-light-kind="torch">🔥 Torch</button>
                            <button type="button" data-keeper-light-kind="lantern">🏮 Lantern</button>
                            <button type="button" data-keeper-light-kind="brazier">♨ Brazier</button>
                            <button type="button" data-keeper-light-kind="candle">🕯 Candle</button>
                            <button type="button" data-keeper-light-kind="magical">✦ Magical Light</button>
                            <button type="button" data-keeper-light-cancel disabled>Finish / Cancel</button>
                        </div>
                        <span data-keeper-light-status role="status" aria-live="polite">Choose a light source to begin.</span>
                        <div class="gmrt-lantern-rack__roster" data-keeper-light-roster></div>
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

                        <details class="gmrt-cartography-assistant" data-cartography-assistant>
                            <summary>Keeper's Cartography Assistant</summary>
                            <div class="gmrt-cartography-assistant__body">
                                <p>
                                    Let the Assistant inspect this Scene's artwork against the calibrated square grid.
                                    Suggestions remain a private draft until you review and apply them. Structural tracing follows thick inked dungeon walls, including diagonals and curved/organic boundaries approximated with short connected segments. Living Contour instead classifies quiet playable floor against hatched/solid rock, traces their shared boundary continuously, and simplifies cave corners into playable line-of-sight segments. The Cartographer's Judgement can combine both readers locally: constructed regions favour repeated structural linework, organic regions keep Living Contour paths, and ambiguous overlaps are left conservative rather than bridged.
                                </p>
                                <div class="gmrt-cartography-assistant__controls">
                                    <label>
                                        Detail
                                        <select data-cartography-assistant-detail>
                                            <option value="strong">Strong boundaries</option>
                                            <option value="balanced" selected>Balanced</option>
                                            <option value="fine">Fine detail</option>
                                            <option value="structural">Structural tracing</option>
                                            <option value="contour">Living Contour · caves</option>
                                            <option value="hybrid">Judgement · hybrid map</option>
                                        </select>
                                    </label>
                                    <button type="button" data-cartography-assistant-analyse>Analyse Map</button>
                                    <button type="button" data-cartography-assistant-select-all disabled>Select All</button>
                                    <button type="button" data-cartography-assistant-apply disabled>Apply Selected</button>
                                    <button type="button" data-cartography-assistant-clear disabled>Clear Draft</button>
                                </div>
                                <span data-cartography-assistant-status role="status" aria-live="polite">No draft suggestions yet.</span>
                                <div class="gmrt-cartography-assistant__review" data-cartography-assistant-review></div>
                            </div>
                        </details>

                        <details class="gmrt-dungeon-forge" data-dungeon-forge>
                            <summary>The Cartographer's Scene Forge</summary>
                            <div class="gmrt-dungeon-forge__body">
                                <p>
                                    Forge a deterministic, Tabletop-native environment directly onto this Scene. Pippin chooses topology from Environment, presentation from Theme, then derives authoritative vision, Keeper lights and a fresh Veil. Nothing becomes authoritative until Build Scene. <!-- Legacy IV.30.2 contract: Nothing becomes authoritative until Build Dungeon -->
                                </p>
                                <div class="gmrt-dungeon-forge__controls">
                                    <label>
                                        Environment
                                        <select data-dungeon-forge-scene-type>
                                            <option value="dungeon" selected>Dungeon</option>
                                            <option value="forest">Forest</option>
                                            <option value="village">Village</option>
                                        </select>
                                    </label>
                                    <label>
                                        Seed
                                        <input type="text" maxlength="80" value="Peppercorn-01" data-dungeon-forge-seed>
                                    </label>
                                    <label>
                                        Scale
                                        <select data-dungeon-forge-style>
                                            <option value="compact">Compact</option>
                                            <option value="standard" selected>Standard</option>
                                            <option value="grand">Grand</option>
                                        </select>
                                    </label>
                                    <label>
                                        Theme
                                        <select data-dungeon-forge-theme>
                                            <option value="pantry-stone" selected>Pantry Stone</option>
                                            <option value="butcher-cellar">Butcher Cellar</option>
                                            <option value="rootland-cavern">Rootland Cavern</option>
                                            <option value="frostreem-vault">Frostreem Vault</option>
                                            <option value="bakery-crypt">Bakery Crypt</option>
                                            <option value="mushroom-grotto">Mushroom Grotto</option>
                                        </select>
                                    </label>
                                    <button type="button" data-dungeon-forge-generate>Forge Draft</button>
                                    <button type="button" data-dungeon-forge-reroll>New Seed</button>
                                    <button type="button" data-dungeon-forge-build disabled>Build Scene</button>
                                    <button type="button" data-dungeon-forge-clear disabled>Clear Draft</button>
                                </div>
                                <span data-dungeon-forge-status role="status" aria-live="polite">
                                    <?php echo $dungeonForge !== []
                                        ? 'This Scene contains a forged ' . esc_html((string) ($dungeonForge['scene_type'] ?? 'dungeon')) . '.'
                                        : 'The Forge is cold. Prepare a draft when ready.'; ?>
                                </span>
                                <div class="gmrt-dungeon-forge__legend" aria-label="Scene Forge draft legend">
                                    <span><i class="is-floor"></i> Playable floor</span>
                                    <span><i class="is-wall"></i> Vision wall</span>
                                    <span><i class="is-door"></i> Door</span>
                                    <span><i class="is-light"></i> Suggested light</span>
                                </div>
                            </div>
                        </details>
                    </div>

                <?php if (($scene['grid_type'] ?? '') === 'square') : ?>
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
                            <button type="button" data-detect-grid>Find Printed Grid</button>
                            <button type="button" data-save-grid>Save Grid</button>
                            <button type="button" data-reset-grid>Reset Preview</button>
                            <span class="gmrt-grid-calibrator__registration" data-grid-registration-status role="status" aria-live="polite">Printed-grid detection is idle.</span>
                        </div>
                    </details>
                <?php endif; ?>

                    <p
                        class="gmrt-cartographer-status"
                        data-cartographer-status
                        role="status"
                        aria-live="polite"
                    >
                        Battlemap artwork may be changed without moving tokens
                        or changing the rules grid.
                    </p>
                        </div>
                    </details>
                <?php endif; ?>

                <div class="gmrt-board__lens-stage" data-lens-stage>
                    <nav
                        class="gmrt-board__lens-controls"
                        data-cartographers-lens
                        data-lens-controls
                        aria-label="Battlemap view controls"
                    >
                        <button type="button" data-lens-zoom-in aria-label="Zoom battlefield in" title="Zoom in">+</button>
                        <button type="button" data-lens-zoom-out aria-label="Zoom battlefield out" title="Zoom out">−</button>
                        <output data-lens-zoom aria-live="polite" aria-label="Battlemap zoom level">100%</output>
                        <button type="button" data-lens-fit aria-label="Fit battlefield to view" title="Fit map to view">Fit</button>
                        <button type="button" data-lens-reset aria-label="Reset battlefield view to 100 percent" title="Reset view to 100%">Reset</button>
                    </nav>
                    <div
                    class="gmrt-board__viewport<?php echo $sceneIsGenerated ? ' is-generated-surface' : ''; ?>"
                    data-grid-type="<?php echo esc_attr(
                        (string) $scene['grid_type']
                    ); ?>"
                    data-grid-reference-width="<?php echo esc_attr(
                        (string) ((int) (
                            $scene['grid_reference_width']
                            ?? 0
                        ))
                    ); ?>"
                    style="--gmrt-grid-size: <?php echo $sceneIsGenerated && ! empty($dungeonForge['cols'])
                        ? 'calc(100% / ' . esc_attr((string) max(1, (int) $dungeonForge['cols'])) . ')'
                        : esc_attr((string) max(1, (int) ($scene['grid_size'] ?? 1))) . 'px'; ?>; --gmrt-grid-offset-x: <?php echo esc_attr((string) ((int) ($scene['grid_offset_x'] ?? 0))); ?>px; --gmrt-grid-offset-y: <?php echo esc_attr((string) ((int) ($scene['grid_offset_y'] ?? 0))); ?>px; --gmrt-grid-opacity: <?php echo esc_attr((string) ((int) ($scene['grid_opacity'] ?? 13) / 100)); ?>; --gmrt-grid-display: <?php echo ! array_key_exists('grid_visible', $scene) || ! empty($scene['grid_visible']) ? 'block' : 'none'; ?>; --gmrt-generated-aspect: <?php echo esc_attr((string) max(1, (int) ($scene['width'] ?? 1))); ?> / <?php echo esc_attr((string) max(1, (int) ($scene['height'] ?? 1))); ?>;"
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
                    <?php elseif (! $sceneIsGenerated) : ?>
                        <div
                            class="gmrt-board__missing-map"
                            role="status"
                        >
                            Battlemap artwork is unavailable.
                        </div>
                    <?php endif; ?>

                    <svg
                        class="gmrt-dungeon-forge-layer<?php echo $dungeonForge !== [] ? ' is-built' : ''; ?>"
                        data-dungeon-forge-layer
                        data-dungeon-forge-plan="<?php echo esc_attr(wp_json_encode($dungeonForge)); ?>"
                        data-forge-theme="<?php echo esc_attr((string) ($dungeonForge['theme'] ?? 'pantry-stone')); ?>"
                        aria-hidden="true"
                    ></svg>

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
                        <svg
                            class="gmrt-cartography-suggestion-layer"
                            data-cartography-suggestion-layer
                            aria-label="Cartography Assistant draft suggestions"
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
                                data-token-source="<?php echo esc_attr(
                                    (string) ($token['source_reference'] ?? '')
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
                        <?php
                        $memberSeatCharacterId = (string) ($member['companion_character_id'] ?? '');
                        $memberHasTurn = false;
                        if ($memberSeatCharacterId !== '' && $currentTokenId !== '') {
                            foreach ($tokens as $memberTurnToken) {
                                if (
                                    (string) ($memberTurnToken['source_reference'] ?? '') === $memberSeatCharacterId
                                    && (string) ($memberTurnToken['id'] ?? '') === $currentTokenId
                                ) {
                                    $memberHasTurn = true;
                                    break;
                                }
                            }
                        }
                        ?>
                        <li
                            style="--gmrt-fellowship-colour: <?php echo esc_attr((string) ($member['table_colour_hex'] ?? '#65b9ae')); ?>"
                            class="gmrt-party__member gmrt-party__member--<?php echo esc_attr((string) ($member['role'] ?? 'player')); ?> gmrt-party__member--<?php echo esc_attr((string) ($member['status'] ?? 'unknown')); ?><?php echo $memberHasTurn ? ' is-active-turn' : ''; ?>"
                            data-party-character-id="<?php echo esc_attr($memberSeatCharacterId); ?>"
                        >
                            <?php $avatarUrl = (string) ($member['avatar_url'] ?? ''); ?>
                            <span class="gmrt-party__avatar" aria-hidden="true">
                                <?php if ($avatarUrl !== '') : ?>
                                    <img src="<?php echo esc_url($avatarUrl); ?>" alt="">
                                <?php else : ?>
                                    <?php echo esc_html(substr((string) ($member['display_name'] ?? '?'), 0, 1)); ?>
                                <?php endif; ?>
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
                            $memberCompanionToken = is_array($memberCompanionCharacter['token'] ?? null)
                                ? $memberCompanionCharacter['token']
                                : [];
                            $memberCharacterImage = (string) ($memberCompanionToken['image_url'] ?? '');
                            $memberCharacterName = (string) ($memberCompanionCharacter['name'] ?? 'Selected character');

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
                            <span
                                class="gmrt-party__role gmrt-party__seat<?php echo $memberCharacterImage !== '' ? ' has-character' : ''; ?>"
                                title="<?php echo esc_attr(($member['role'] ?? '') === 'dungeon-master' ? 'Dungeon Master' : ($memberCharacterImage !== '' ? 'Playing: ' . $memberCharacterName : 'Player — no character selected')); ?>"
                                aria-label="<?php echo esc_attr(($member['role'] ?? '') === 'dungeon-master' ? 'Dungeon Master' : ($memberCharacterImage !== '' ? 'Playing: ' . $memberCharacterName : 'Player — no character selected')); ?>"
                            >
                                <?php if (($member['role'] ?? '') === 'dungeon-master') : ?>
                                    <span aria-hidden="true">DM</span>
                                <?php elseif ($memberCharacterImage !== '') : ?>
                                    <img
                                        src="<?php echo CompanionTokenImageSource::escaped($memberCharacterImage); ?>"
                                        alt=""
                                        aria-hidden="true"
                                        style="--gmrt-token-focus-x: <?php echo esc_attr((string) ($memberCompanionToken['focus_x'] ?? 50)); ?>%; --gmrt-token-focus-y: <?php echo esc_attr((string) ($memberCompanionToken['focus_y'] ?? 50)); ?>%; --gmrt-token-zoom: <?php echo esc_attr((string) ($memberCompanionToken['zoom'] ?? 100)); ?>%;"
                                    >
                                <?php else : ?>
                                    <span aria-hidden="true">P</span>
                                <?php endif; ?>
                            </span>

                            <?php if (
                                $state->isDungeonMaster()
                                && ($member['role'] ?? '') === 'dungeon-master'
                                && ($member['status'] ?? '') !== 'left'
                            ) : ?>
                                <div class="gmrt-party__secret-roll" data-keeper-secret-roll-zone>
                                    <button
                                        type="button"
                                        data-keeper-secret-d20
                                        aria-describedby="gmrt-keeper-secret-roll-help"
                                    >Secret d20</button>
                                    <span
                                        class="gmrt-party__secret-result"
                                        data-keeper-secret-d20-result
                                        role="status"
                                        aria-live="polite"
                                    ></span>
                                    <span id="gmrt-keeper-secret-roll-help" class="screen-reader-text">
                                        Roll a private d20 visible only to the Dungeon Master. It is not written to the Chronicle.
                                    </span>
                                </div>
                            <?php endif; ?>

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
                        <?php if ($state->isDungeonMaster()) : ?>
                            <details class="gmrt-character-gate-disclosure">
                                <summary>Choose a Test Adventurer</summary>
                                <div class="gmrt-character-gate-disclosure__body">
                        <?php endif; ?>
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
                        <?php if ($state->isDungeonMaster()) : ?>
                                </div>
                            </details>
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

