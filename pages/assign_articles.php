<?php

if (rex_request('function','string') == 'run') {
    if (ra_list::assign_articles()) {
        echo rex_view::success('Datensätze wurden erstellt.');        
    } else {
        echo rex_view::error('Es ist ein Fehler aufgetreten.');
    }
}

$qry = 'SELECT id, name, createdate, status, art_online_from, catname, path FROM '.rex::getTable('article').' WHERE clang_id = 1 AND startarticle = 0 AND art_raid = "" ORDER BY createdate DESC';

$list = rex_list::factory($qry);

$list->removeColumn('path');

$list->setColumnLabel('art_online_from', 'Online vom ...');
$list->setColumnLabel('createdate', 'Erstellungsdatum');
$list->setColumnLabel('status', 'Status');
$list->setColumnLabel('catname', 'Kategorie');
$list->setColumnLabel('name', 'Name');

$list->setColumnSortable('status');
$list->setColumnSortable('createdate');
$list->setColumnSortable('art_online_from');
$list->setColumnSortable('catname');

$list->setColumnFormat('art_online_from', 'date');

$list->addColumn('Aktivieren','');
$list->setColumnFormat('Aktivieren','custom','ra_list::edit_column',['id' => '###id###']);

$content = $list->get();


$button = '<footer class="panel-footer">'
        . '<button class="btn btn-save" type="submit" name="function" value="run">Markierte Artikel zuordnen</button>'
        . '<button class="btn" style="float:right" type="button" name="function" value="mark_all" id="mark_all_button">Alle markieren</button>'
        . '</footer>';

$content = str_replace('</form>',$button.'</form>',$content);

$fragment = new rex_fragment();
$fragment->setVar('title', 'Artikel hinzufügen');
$fragment->setVar('content', $content, false);

echo rex_view::info('Redaxo Artikel zu Redaktionsassistent hinzufügen. Es werden nur Artikel aufgeführt, die noch nicht über den Redaktionsassistent verwaltet werden.');
echo $fragment->parse('core/page/section.php');
?>
<script>
    $(document).on('click','#mark_all_button',function() {
        $('input.cbx_ra_assign_article').trigger('click');
    });
</script>