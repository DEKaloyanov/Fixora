<?php
/**
 * Топ-ниво категории + списък от ключове на под-професии (листата).
 * Листовите ключове трябва да съвпадат с ключовете в flat map-а от professions.php
 * (напр. 'boqjdiq', 'zidar', 'elektrikar', ...).
 */
$PROFESSIONS_TREE = [
    'design' => [
        'label'    => 'Проектиране и подготовка',
        'children' => [
            'architect', 'structural_engineer', 'mep_designer',
            'surveyor_design', 'geologist', 'landscape_arch',
            'road_designer', 'energy_audit', 'bim_modeler', 'quantity_surveyor'
        ],
    ],
    'management' => [
        'label'    => 'Управление и контрол на строителството',
        'children' => ['project_manager', 'foreman', 'planner', 'procurement', 'qa_qc', 'hse', 'surveyor_site'],
    ],
    'earthworks' => [
        'label'    => 'Земни и геотехнически работи',
        'children' => ['excavation_worker', 'drainage', 'piling', 'micro_piles', 'anchors', 'jet_grouting'],
    ],
    'concrete' => [
        'label'    => 'Бетон, арматура и кофраж',
        'children' => ['kofraj', 'armat', 'betondjiq', 'concrete_pump', 'industrial_floors', 'prestressing'],
    ],
    'masonry' => [
        'label'    => 'Зидарии и каменни работи',
        'children' => ['zidar', 'stone_mason', 'partition_walls'],
    ],
    'steel' => [
        'label'    => 'Метални конструкции',
        'children' => ['steel_erector', 'rigger', 'welder', 'cutter', 'surface_treatment'],
    ],
    'wood_joinery' => [
        'label'    => 'Дървени конструкции и дограми',
        'children' => ['carpenter_structural', 'wood_facade_installer', 'furniture_installer', 'dograma'],
    ],
    'roofs' => [
        'label'    => 'Покриви и хидроизолации',
        'children' => ['roofer', 'hydroinsulator', 'tinsmith', 'green_roofs', 'roof_windows', 'pv_installer'],
    ],
    'facades' => [
        'label'    => 'Фасади и топлоизолации',
        'children' => ['insulation', 'ventilated_facade', 'stone_cladding', 'rope_access'],
    ],
    'glazing' => [
        'label'    => 'Прозорци, врати и остъкляване',
        'children' => ['window_installer', 'glazier', 'roller_shutters'],
    ],
    'interior_drywall' => [
        'label'    => 'Сухо строителство и интериор',
        'children' => ['gypsum_board', 'acoustic_systems', 'shpaklovchik', 'decorative_plasters', 'boqjdiq'],
    ],
    'flooring' => [
        'label'    => 'Подови настилки и замазки',
        'children' => ['screed', 'tiler', 'parquet', 'vinyl', 'epoxy_floors', 'stone_paving'],
    ],
    'plumbing' => [
        'label'    => 'ВиК (водоснабдяване и канализация)',
        'children' => ['vodoprovodchik', 'kanaldjiq', 'sanitary', 'irrigation', 'fire_hydrants'],
    ],
    'hvac' => [
        'label'    => 'ОВК (отопление, вентилация, климатизация)',
        'children' => ['hvac_installer', 'duct_fabrication', 'hvac_insulation', 'boilers_heatpumps', 'hvac_commissioning'],
    ],
    'electrical' => [
        'label'    => 'Електро (силноток и слаб ток)',
        'children' => ['elektrikar', 'panel_builder', 'low_current', 'pv_electric', 'measurements'],
    ],
    'fire_safety' => [
        'label'    => 'Пожарогасителни и безопасност',
        'children' => ['sprinklers', 'gas_systems', 'smoke_control'],
    ],
    'automation' => [
        'label'    => 'Автоматизация и BMS',
        'children' => ['bms_scada', 'lighting_control'],
    ],
    'lifts' => [
        'label'    => 'Асансьори и ескалатори',
        'children' => ['elevator_installer_service'],
    ],
    'scaffolding' => [
        'label'    => 'Скелета и временни конструкции',
        'children' => ['scaffolder', 'temporary_works'],
    ],
    'demolition' => [
        'label'    => 'Демонтаж, рязане, пробиване',
        'children' => ['demolition', 'diamond_cutting', 'asbestos'],
    ],
    'road_civil' => [
        'label'    => 'Пътно строителство и благоустройство',
        'children' => ['asphalt', 'curbs_paving', 'road_marking', 'sewer_networks', 'geosynthetics'],
    ],
    'bridges_rail_tunnels_hydro' => [
        'label'    => 'Мостово, жп, тунелно и хидротехническо',
        'children' => ['bridges', 'rail', 'tunnels', 'hydro'],
    ],
    'landscape' => [
        'label'    => 'Озеленяване и външна среда',
        'children' => ['landscaping', 'fencing', 'playgrounds_sports'],
    ],
    'final_fitout' => [
        'label'    => 'Финален монтаж и довършителни',
        'children' => ['doors_hardware', 'silicone', 'final_cleaning'],
    ],
    'transport_logistics' => [
        'label'    => 'Транспорт, повдигане и склад',
        'children' => ['crane_operator', 'forklift', 'telehandler', 'warehouse', 'supply'],
    ],
    'facility' => [
        'label'    => 'Поддръжка и експлоатация (FM)',
        'children' => ['building_maintenance', 'facility_mgmt', 'boiler_rooms', 'emergency_teams'],
    ],
];

/** Върни листовете за дадена категория (или празен масив) */
function get_profession_children(string $mainKey): array {
    global $PROFESSIONS_TREE;
    return $PROFESSIONS_TREE[$mainKey]['children'] ?? [];
}
