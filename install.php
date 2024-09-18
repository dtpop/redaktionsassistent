<?php


$sql = rex_sql::factory();
$tables = $sql->getTables();
// Tabellen nur erstellen, wenn nicht vorhanden.
if (!in_array(rex::getTable('redaktionsassistent'),$tables)) {
    $yform_export_file = rtrim(rex_path::addon('redaktionsassistent'),'/').'/yform_tables.json';
    $content = file_get_contents($yform_export_file);
    rex_yform_manager_table_api::importTablesets($content);
}

rex_metainfo_add_field('Bearbeitungsnummer', 'art_raid', 999, 'readonly="readonly"', 1,'');

// Metainfo Felder anlegen.
$prefix = 'art\_%';
$defaultFields = [
    ['translate:online_from', 'art_online_from', '1', '', '10', ''],
    ['translate:online_to', 'art_online_to', '2', '', '10', ''],
    ['translate:description', 'art_description', '3', '', '2', ''],
];

$existing = rex_sql::factory()->getArray('SELECT name FROM ' . rex::getTable('metainfo_field') . ' WHERE name LIKE ?', [$prefix]);
$existing = array_column($existing, 'name', 'name');

foreach ($defaultFields as $field) {
    if (!isset($existing[$field[1]])) {
        $return = call_user_func_array('rex_metainfo_add_field', $field);
        if (is_string($return)) {
            throw new rex_api_exception($return);
        }
    }
}




/*

$_GET['type'] = 'articles';
$mfcreate = new rex_api_metainfo_default_fields_create();
@$mfcreate->execute();
*/