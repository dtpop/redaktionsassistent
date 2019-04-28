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
$_GET['type'] = 'articles';
@rex_api_metainfo_default_fields_create::execute();
