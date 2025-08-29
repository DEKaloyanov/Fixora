<?php
// php/normalize_professions.php
// Изпълни веднъж от браузър или CLI: php normalize_professions.php
// Ще нормализира jobs.profession и jobs.professions към нашите ключове.

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/professions.php'; // $professions (канонични ключове)
header('Content-Type: text/plain; charset=utf-8');

// ---- 1) Алиаси: английски/разговорни -> наш КЛЮЧ (добавяй спокойно при нужда)
$ALIASES = [
    // Управление / инженери
    'project manager' => 'project_manager',
    'site manager'    => 'foreman',
    'foreman'         => 'foreman',
    'quantity surveyor'=> 'quantity_surveyor',
    'bim modeler'     => 'bim_modeler',
    'bim coordinator' => 'bim_modeler',
    'architect'       => 'architect',
    'structural engineer' => 'structural_engineer',
    'mep designer'    => 'mep_designer',
    'mep engineer'    => 'mep_designer',
    'surveyor'        => 'surveyor_site',
    'land surveyor'   => 'surveyor_site',
    'geologist'       => 'geologist',
    'qa qc'           => 'qa_qc',
    'hse'             => 'hse',

    // Електро / ОВК / ВиК / PV / BMS
    'electrician'     => 'elektrikar',
    'electrical'      => 'elektrikar',
    'pv electrician'  => 'pv_electric',
    'photovoltaic electrician' => 'pv_electric',
    'pv installer'    => 'pv_installer',
    'solar installer' => 'pv_installer',
    'hvac installer'  => 'hvac_installer',
    'hvac'            => 'hvac_installer',
    'air conditioning'=> 'hvac_installer',
    'ac technician'   => 'hvac_installer',
    'plumber'         => 'vodoprovodchik',
    'plumbing'        => 'vodoprovodchik',
    'bms'             => 'bms_scada',
    'bms scada'       => 'bms_scada',
    'knx'             => 'lighting_control',
    'dali'            => 'lighting_control',
    'lighting control'=> 'lighting_control',
    'sprinklers'      => 'sprinklers',
    'gas suppression' => 'gas_systems',
    'smoke control'   => 'smoke_control',
    'measurements'    => 'measurements',

    // Сухо строителство / шпакловка / боя
    'drywall'         => 'gypsum_board',
    'drywall installer'=> 'gypsum_board',
    'gypsum board'    => 'gypsum_board',
    'plasterer'       => 'mazach',
    'render'          => 'mazach',
    'putty'           => 'shpaklovchik',
    'spackle'         => 'shpaklovchik',
    'drywall finisher'=> 'shpaklovchik',
    'painter'         => 'boqjdiq',
    'painting'        => 'boqjdiq',
    'decorator'       => 'boqjdiq',
    'decorative plaster'=> 'decorative_plasters',
    'acoustic'        => 'acoustic_systems',

    // Зидарии / бетон / кофраж / арматура
    'mason'           => 'zidar',
    'bricklayer'      => 'zidar',
    'masonry'         => 'zidar',
    'concrete worker' => 'betondjiq',
    'concrete'        => 'betondjiq',
    'formwork'        => 'kofraj',
    'shuttering'      => 'kofraj',
    'rebar'           => 'armat',
    'steel fixer'     => 'armat',
    'prestressing'    => 'prestressing',
    'industrial floor'=> 'industrial_floors',

    // Метал / заварка / скеле
    'welder'          => 'welder',
    'mig welder'      => 'welder',
    'tig welder'      => 'welder',
    'steel erector'   => 'steel_erector',
    'rigger'          => 'rigger',
    'scaffolder'      => 'scaffolder',
    'scaffolding'     => 'scaffolder',
    'surface treatment'=> 'surface_treatment',

    // Покриви / хидро / тенекеджии
    'roofer'          => 'roofer',
    'roofing'         => 'roofer',
    'hydroinsulation' => 'hydroinsulator',
    'waterproofing'   => 'hydroinsulator',
    'tinsmith'        => 'tinsmith',
    'green roofs'     => 'green_roofs',
    'roof windows'    => 'roof_windows',
    'skylights'       => 'roof_windows',

    // Фасади / изолации / облицовки / алпинисти
    'insulation'           => 'insulation',
    'thermal insulation'   => 'insulation',
    'ventilated facade'    => 'ventilated_facade',
    'facade'               => 'ventilated_facade',
    'stone cladding'       => 'stone_cladding',
    'rope access'          => 'rope_access',

    // Прозорци/врати/остъкляване/дограма
    'window installer'     => 'window_installer',
    'door installer'       => 'window_installer',
    'windows and doors'    => 'window_installer',
    'pvc windows'          => 'dograma',
    'aluminium windows'    => 'dograma',
    'aluminum windows'     => 'dograma',
    'glazier'              => 'glazier',
    'roller shutters'      => 'roller_shutters',
    'shutters'             => 'roller_shutters',

    // Подови / замазки
    'screed'          => 'screed',
    'floor screed'    => 'screed',
    'tiler'           => 'tiler',
    'tile setter'     => 'tiler',
    'tile installer'  => 'tiler',
    'parquet'         => 'parquet',
    'laminate'        => 'parquet',
    'vinyl flooring'  => 'vinyl',
    'epoxy floors'    => 'epoxy_floors',
    'epoxy'           => 'epoxy_floors',
    'stone paving'    => 'stone_paving',
    'paving'          => 'stone_paving',

    // Земни / демонтаж / пробиване
    'demolition'      => 'demolition',
    'diamond drilling'=> 'diamond_cutting',
    'core drilling'   => 'diamond_cutting',
    'asbestos'        => 'asbestos',
    'excavation'      => 'excavation_worker',
    'drainage'        => 'drainage',
    'piling'          => 'piling',
    'micro piles'     => 'micro_piles',
    'anchors'         => 'anchors',
    'jet grouting'    => 'jet_grouting',

    // Пътно/ландшафт/др.
    'asphalt'         => 'asphalt',
    'curbs'           => 'curbs_paving',
    'curb'            => 'curbs_paving',
    'road marking'    => 'road_marking',
    'sewer networks'  => 'sewer_networks',
    'geosynthetics'   => 'geosynthetics',
    'landscaping'     => 'landscaping',
    'fencing'         => 'fencing',
    'playgrounds'     => 'playgrounds_sports',

    // Транспорт / склад
    'crane operator'  => 'crane_operator',
    'forklift'        => 'forklift',
    'telehandler'     => 'telehandler',
    'warehouse'       => 'warehouse',
    'storekeeper'     => 'warehouse',
    'supply'          => 'supply',

    // Поддръжка
    'building maintenance' => 'building_maintenance',
    'facility management'  => 'facility_mgmt',
    'boiler room'          => 'boiler_rooms',
    'emergency team'       => 'emergency_teams',

    // Дизайн / енергийни
    'energy audit'         => 'energy_audit',
    'landscape architect'  => 'landscape_arch',
    'road designer'        => 'road_designer',

    // Други
    'doors hardware'       => 'doors_hardware',
    'hardware'             => 'doors_hardware',
    'silicone'             => 'silicone',
    'final cleaning'       => 'final_cleaning',
    'elevator installer'   => 'elevator_installer_service',
    'escalator installer'  => 'elevator_installer_service',
];

// ---- нормализатор
function norm(string $s): string {
    $s = trim(mb_strtolower($s, 'UTF-8'));
    $s = preg_replace('~\([^)]*\)~u', ' ', $s);
    $s = str_replace(['/', '\\', '-', '–', '—', ',', ';', '|', '+', '.'], ' ', $s);
    $s = preg_replace('~\s+~u', ' ', $s);
    return trim($s);
}
function to_key(?string $raw, array $professions, array $ALIASES): ?string {
    if ($raw === null) return null;
    $raw = trim($raw);
    if ($raw === '') return null;

    // ако вече е ключ
    if (isset($professions[$raw])) return $raw;

    // ако е български етикет
    $flip = array_change_key_case(array_flip($professions), CASE_LOWER);
    $low  = mb_strtolower($raw,'UTF-8');
    if (isset($flip[$low])) return $flip[$low];

    // опитай по алиаси
    $n = norm($raw);
    if (isset($ALIASES[$n])) return $ALIASES[$n];
    foreach ($ALIASES as $needle => $key) {
        if ($needle !== '' && mb_strpos($n, $needle, 0, 'UTF-8') !== false) return $key;
    }
    return null;
}

// ---- обхождане
$total = 0; $changed1 = 0; $changedN = 0;

$stmt = $conn->query("SELECT id, profession, professions FROM jobs");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($rows as $r) {
    $total++;
    $id   = (int)$r['id'];
    $one  = $r['profession'];
    $many = $r['professions'];

    $newOne = to_key($one, $professions, $ALIASES) ?? $one;

    $newMany = $many;
    if ($many) {
        $arr = json_decode($many, true);
        if (is_array($arr)) {
            $mapped = [];
            foreach ($arr as $x) {
                $k = to_key((string)$x, $professions, $ALIASES) ?? (string)$x;
                $mapped[] = $k;
            }
            $newMany = json_encode($mapped, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        }
    }

    if ($newOne !== $one || $newMany !== $many) {
        $u = $conn->prepare("UPDATE jobs SET profession = :p, professions = :ps WHERE id = :id");
        $u->execute([':p'=>$newOne, ':ps'=>$newMany, ':id'=>$id]);
        if ($newOne !== $one) $changed1++;
        if ($newMany !== $many) $changedN++;
        echo "Updated job #$id → profession='$newOne', professions=$newMany\n";
    }
}

echo "\nDone. Total: $total, changed profession: $changed1, changed professions JSON: $changedN\n";
