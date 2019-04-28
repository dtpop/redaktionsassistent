<?php

/*

$table = rex_sql_table::get(rex::getTable('redaktionsassistent'));
$table
    ->ensurePrimaryIdColumn()
    ->ensureColumn(new rex_sql_column('id', 'int(11)'))
    ->ensureColumn(new rex_sql_column('rex_article', 'int(11)'))
    ->ensureColumn(new rex_sql_column('id2', 'varchar(191)'))
    ->ensureColumn(new rex_sql_column('name', 'varchar(191)'))
    ->ensureColumn(new rex_sql_column('type', 'int(11)'))
    ->ensureColumn(new rex_sql_column('category', 'int(11)'))
    ->ensureColumn(new rex_sql_column('art_online_from', 'date'))
    ->ensureColumn(new rex_sql_column('notice', 'text'))
    ->ensureColumn(new rex_sql_column('task_status', 'int(11)'))
    ->ensureColumn(new rex_sql_column('art_status', 'int(11)'))
    ->ensureColumn(new rex_sql_column('editor', 'int(11)'))
//    ->ensureColumn(new rex_sql_column('art_slider_override', 'int(11)'))
//    ->ensureColumn(new rex_sql_column('art_hauptteaser', 'int(11)'))
    ->ensureColumn(new rex_sql_column('generate_article_button', 'text'))
    ->ensure();

*/


$sql = rex_sql::factory();
$tables = $sql->getTables();
// Tabellen nur erstellen, wenn nicht vorhanden.
if (!in_array(rex::getTable('redaktionsassistent'),$tables)) {
    $yform_export_file = rtrim(rex_path::addon('redaktionsassistent'),'/').'/yform_tables.json';
    $content = file_get_contents($yform_export_file);
    rex_yform_manager_table_api::importTablesets($content);
}

// Metainfo Felder anlegen.
$_GET['type'] = 'articles';
@rex_api_metainfo_default_fields_create::execute();
