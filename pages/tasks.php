<?php
/*
 * Verwaltungsseite für Artikel-Tasks
 */


$table_name = 'rex_redaktionsassistent';
$table = rex_yform_manager_table::get($table_name);

// $table->getFields();

if ($table && rex::getUser() && (rex::getUser()->isAdmin() || rex::getUser()->getComplexPerm('yform_manager_table')->hasPerm($table->getTableName()))) {
    try {
        $page = new rex_yform_manager();
        $page->setTable($table);
        $page->setLinkVars(['page' => rex_request('page', 'string'), 'table_name' => $table->getTableName()]);
        echo $page->getDataPage();
    } catch (Exception $e) {
        $message = nl2br($e->getMessage()."\n".$e->getTraceAsString());
        echo rex_view::warning($message);
    }
} else {
    if (!$table) {
        echo rex_view::warning(rex_i18n::msg('yform_table_not_found'));
    }
}