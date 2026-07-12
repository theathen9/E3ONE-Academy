<?php
// ./app/api/v1/address.php
header('Content-Type: application/json');

$data = json_decode(file_get_contents(__DIR__ . '/../../../data/addressCambodia.json'), true);

$type = $_GET['type'] ?? null;

function safe_array_keys($arr) {
    return is_array($arr) ? array_keys($arr) : [];
}

function safe_values($arr) {
    if (!is_array($arr)) return [];

    // If associative → return keys
    if (array_keys($arr) !== range(0, count($arr) - 1)) {
        return array_keys($arr);
    }

    // If indexed → return values
    return array_values($arr);
}

switch ($type) {

    case 'provinces':
        echo json_encode(safe_array_keys($data));
        break;

    case 'districts':
        $province = $_GET['province'] ?? '';
        echo json_encode(
            safe_array_keys($data[$province]['districts'] ?? [])
        );
        break;

    case 'communes':
        $province = $_GET['province'] ?? '';
        $district = $_GET['district'] ?? '';

        echo json_encode(
            safe_array_keys(
                $data[$province]['districts'][$district]['communes'] ?? []
            )
        );
        break;

    case 'villages':
        $province = $_GET['province'] ?? '';
        $district = $_GET['district'] ?? '';
        $commune = $_GET['commune'] ?? '';

        $villages =
            $data[$province]['districts'][$district]['communes'][$commune]['villages']
            ?? [];

        echo json_encode(safe_values($villages));
        break;

    default:
        echo json_encode([
            "error" => "Invalid type"
        ]);
}
?>
