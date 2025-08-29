<?php
// php/label_utils.php
// Единен помощник за етикети на професии на български + агресивна нормализация на входа.

require_once __DIR__ . '/professions.php'; // осигурява $professions (key => BG label)

/**
 * Вътрешно: нормализира суров низ (премахва шум, lower, унифицира разделители).
 */
function _norm(string $s): string {
    $s = trim(mb_strtolower($s, 'UTF-8'));
    // махни съдържание в скоби и излишни символи
    $s = preg_replace('~\([^)]*\)~u', ' ', $s);
    $s = str_replace(['/', '\\', '-', '–', '—', ',', ';', '|', '+', '.'], ' ', $s);
    $s = preg_replace('~\s+~u', ' ', $s);
    return trim($s);
}

/**
 * Карта от често срещани английски/разговорни изрази към нашите КЛЮЧОВЕ.
 * По-дългите/по-специфични фрази са изредени първо.
 */
function _aliases(): array {
    static $ALIASES = null;
    if ($ALIASES !== null) return $ALIASES;

    $ALIASES = [
        // Управление / инженери
        'project manager'                 => 'project_manager',
        'site manager'                    => 'foreman',
        'foreman'                         => 'foreman',
        'quantity surveyor'               => 'quantity_surveyor',
        'qs'                              => 'quantity_surveyor',
        'bim modeler'                     => 'bim_modeler',
        'bim coordinator'                 => 'bim_modeler',
        'architect'                       => 'architect',
        'structural engineer'             => 'structural_engineer',
        'mep designer'                    => 'mep_designer',
        'mep engineer'                    => 'mep_designer',
        'surveyor'                        => 'surveyor_site',
        'land surveyor'                   => 'surveyor_site',
        'geologist'                       => 'geologist',
        'qa qc'                           => 'qa_qc',
        'hse'                             => 'hse',

        // Електро / ОВК / ВиК / PV / BMS
        'electrician'                     => 'elektrikar',
        'electrical'                      => 'elektrikar',
        'pv electrician'                  => 'pv_electric',
        'pv electric'                     => 'pv_electric',
        'photovoltaic electrician'        => 'pv_electric',
        'hvac installer'                  => 'hvac_installer',
        'hvac'                            => 'hvac_installer',
        'air conditioning'                => 'hvac_installer',
        'ac technician'                   => 'hvac_installer',
        'plumber'                         => 'vodoprovodchik',
        'plumbing'                        => 'vodoprovodchik',
        'bms'                             => 'bms_scada',
        'bms scada'                       => 'bms_scada',
        'knx'                             => 'lighting_control',
        'dali'                            => 'lighting_control',
        'lighting control'                => 'lighting_control',
        'sprinklers'                      => 'sprinklers',
        'fire sprinklers'                 => 'sprinklers',
        'gas suppression'                 => 'gas_systems',
        'smoke control'                   => 'smoke_control',
        'measurements'                    => 'measurements',

        // Сухо строителство / шпакловка / мазилка / боя
        'drywall'                         => 'gypsum_board',
        'drywall installer'               => 'gypsum_board',
        'gypsum board'                    => 'gypsum_board',
        'gipsokarton'                     => 'gypsum_board',
        'plasterer'                       => 'mazach',
        'render'                          => 'mazach',
        'putty'                           => 'shpaklovchik',
        'spackle'                         => 'shpaklovchik',
        'drywall finisher'                => 'shpaklovchik',
        'painter'                         => 'boqjdiq',
        'painting'                        => 'boqjdiq',
        'decorator'                       => 'boqjdiq',
        'decorative plaster'              => 'decorative_plasters',
        'acoustic'                        => 'acoustic_systems',

        // Зидарии / бетон / кофраж / арматура
        'mason'                           => 'zidar',
        'bricklayer'                      => 'zidar',
        'masonry'                         => 'zidar',
        'concrete worker'                 => 'betondjiq',
        'concrete'                        => 'betondjiq',
        'formwork'                        => 'kofraj',
        'shuttering'                      => 'kofraj',
        'rebar'                           => 'armat',
        'steel fixer'                     => 'armat',
        'prestressing'                    => 'prestressing',
        'industrial floor'                => 'industrial_floors',

        // Метал / заварка / скеле
        'welder'                          => 'welder',
        'mig welder'                      => 'welder',
        'tig welder'                      => 'welder',
        'steel erector'                   => 'steel_erector',
        'rigger'                          => 'rigger',
        'scaffolder'                      => 'scaffolder',
        'scaffolding'                     => 'scaffolder',
        'surface treatment'               => 'surface_treatment',

        // Покриви / хидро / тенекеджии
        'roofer'                          => 'roofer',
        'roofing'                         => 'roofer',
        'hydroinsulation'                 => 'hydroinsulator',
        'waterproofing'                   => 'hydroinsulator',
        'tinsmith'                        => 'tinsmith',
        'green roofs'                     => 'green_roofs',
        'roof windows'                    => 'roof_windows',
        'skylights'                       => 'roof_windows',
        'pv installer'                    => 'pv_installer',
        'solar installer'                 => 'pv_installer',

        // Фасади / изолации / облицовки / алпинисти
        'insulation'                      => 'insulation',
        'thermal insulation'              => 'insulation',
        'facade'                          => 'ventilated_facade',
        'ventilated facade'               => 'ventilated_facade',
        'stone cladding'                  => 'stone_cladding',
        'rope access'                     => 'rope_access',

        // Прозорци/врати/дограма/остъкляване
        'window installer'                => 'window_installer',
        'door installer'                  => 'window_installer',
        'windows and doors'               => 'window_installer',
        'pvc windows'                     => 'dograma',
        'aluminium windows'               => 'dograma',
        'aluminum windows'                => 'dograma',
        'aluminium joinery'               => 'dograma',
        'aluminum joinery'                => 'dograma',
        'glazier'                         => 'glazier',
        'glass installer'                 => 'glazier',
        'roller shutters'                 => 'roller_shutters',
        'shutters'                        => 'roller_shutters',

        // Подови настилки / замазки
        'screed'                          => 'screed',
        'floor screed'                    => 'screed',
        'tiler'                           => 'tiler',
        'tile setter'                     => 'tiler',
        'tile installer'                  => 'tiler',
        'parquet'                         => 'parquet',
        'laminate'                        => 'parquet',
        'vinyl flooring'                  => 'vinyl',
        'epoxy floors'                    => 'epoxy_floors',
        'epoxy'                           => 'epoxy_floors',
        'stone paving'                    => 'stone_paving',
        'paving'                          => 'stone_paving',

        // Земни / демонтаж / пробиване
        'demolition'                      => 'demolition',
        'diamond drilling'                => 'diamond_cutting',
        'core drilling'                   => 'diamond_cutting',
        'asbestos'                        => 'asbestos',
        'excavation'                      => 'excavation_worker',
        'drainage'                        => 'drainage',
        'piling'                          => 'piling',
        'micro piles'                     => 'micro_piles',
        'anchors'                         => 'anchors',
        'jet grouting'                    => 'jet_grouting',

        // Пътно, благоустройство, ландшафт
        'asphalt'                         => 'asphalt',
        'curbs'                           => 'curbs_paving',
        'curb'                            => 'curbs_paving',
        'road marking'                    => 'road_marking',
        'sewer networks'                  => 'sewer_networks',
        'geosynthetics'                   => 'geosynthetics',
        'landscaping'                     => 'landscaping',
        'fencing'                         => 'fencing',
        'playgrounds'                     => 'playgrounds_sports',

        // Транспорт / склад
        'crane operator'                  => 'crane_operator',
        'forklift'                        => 'forklift',
        'telehandler'                     => 'telehandler',
        'warehouse'                       => 'warehouse',
        'storekeeper'                     => 'warehouse',
        'supply'                          => 'supply',

        // Поддръжка
        'building maintenance'            => 'building_maintenance',
        'facility management'             => 'facility_mgmt',
        'boiler room'                     => 'boiler_rooms',
        'emergency team'                  => 'emergency_teams',

        // Дизайн / енергийни
        'energy audit'                    => 'energy_audit',
        'landscape architect'             => 'landscape_arch',
        'road designer'                   => 'road_designer',

        // Други специфики
        'doors hardware'                  => 'doors_hardware',
        'hardware'                        => 'doors_hardware',
        'silicone'                        => 'silicone',
        'final cleaning'                  => 'final_cleaning',
        'elevator installer'              => 'elevator_installer_service',
        'escalator installer'             => 'elevator_installer_service',
    ];

    // подреди по дължина (най-дългите първи), за да печелят по-специфичните фрази
    uksort($ALIASES, function($a, $b){ return mb_strlen($b,'UTF-8') <=> mb_strlen($a,'UTF-8'); });
    return $ALIASES;
}

/**
 * Опитва да върне НАШ КЛЮЧ (напр. 'elektrikar') от произволен вход (ключ, BG, EN, свободен текст).
 */
function normalize_profession_key(?string $raw): ?string {
    global $professions;
    if ($raw === null) return null;
    $raw = trim($raw);
    if ($raw === '') return null;

    // 1) Ако е точен ключ
    if (isset($professions[$raw])) return $raw;

    // 2) Ако вече е български етикет – върни съответния ключ
    $flip = array_change_key_case(array_flip($professions), CASE_LOWER);
    $low  = mb_strtolower($raw, 'UTF-8');
    if (isset($flip[$low])) return $flip[$low];

    // 3) Алиаси (EN и др.)
    $norm = _norm($raw);
    $ALIASES = _aliases();

    // 3a) точен мач по нормализирано
    if (isset($ALIASES[$norm])) return $ALIASES[$norm];

    // 3b) частичен мач – ако нормализираният вход съдържа фразата
    foreach ($ALIASES as $needle => $key) {
        if ($needle !== '' && mb_strpos($norm, $needle, 0, 'UTF-8') !== false) {
            return $key;
        }
    }

    // 4) Нямаме уверен мач
    return null;
}

/**
 * Връща БЪЛГАРСКИЯ етикет, независимо дали входът е ключ, BG, EN или свободен текст.
 * Ако нищо не разпознаем → връщаме оригинала (за да не скрием данни), но това е вече рядкост.
 */
function job_label(?string $maybeKeyOrText): string {
    global $professions;
    $maybeKeyOrText = $maybeKeyOrText ?? '';

    // опитай да нормализираш към наш КЛЮЧ
    $key = normalize_profession_key($maybeKeyOrText);
    if ($key !== null && isset($professions[$key])) {
        return $professions[$key]; // гарантирано БГ
    }

    // ако входът вече е български етикет (точно), остави го
    if ($maybeKeyOrText !== '') {
        $isBg = in_array($maybeKeyOrText, $professions, true);
        if ($isBg) return $maybeKeyOrText;
    }

    // последна миля – ако входът е английски, но не хванат, опитай да „ucfirst“ (по-добре визуално)
    if ($maybeKeyOrText !== '') {
        return $maybeKeyOrText;
    }

    return 'Обява';
}
