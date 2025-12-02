<?php
$form = rex_config_form::factory('redaktionsassistent');
$form->addFieldset('Redaktionsassistent - Einstellungen');

$field = $form->addCheckboxField('regenerate_paths');
$field->setLabel('Path Felder in Artikeln regenerieren');
$field->addOption('Path Felder in Artikeln regenerieren', "1");
$field->setNotice('Wenn der Cache gelöscht wird und die Checkbox gesetzt ist, werden alle <code>path</code> Einträge in der Artikeldatenbank neu geschrieben. Die Artikelstruktur bleibt erhalten. Dies sollte nur im Ausnahmefall notwendig sein.');

$field = $form->addCheckboxField('multicategory_mode');
$field->setLabel('Arbeitsmodus einstellen');
$field->addOption('Multikategorie Modus verwenden', "1");
$field->setNotice('Dieser Mode ermöglicht für einen Artikel mehrere Kategorien auszuwählen. <code>[multicategory_mode]</code>');

$field = $form->addCheckboxField('hide_online_to');
$field->setLabel('Feld Online bis ausblenden');
$field->addOption('Feld online bis ausblenden', "1");
$field->setNotice('Wenn die Checkbox angehakt ist, wird das Feld "Online bis" nicht im Backend angezeigt. <code>[hide_online_to]</code>');

$field = $form->addCheckboxField('hide_id');
$field->setLabel('Datensatz Id des Redaktionsassistenten ausblenden');
$field->addOption('Datensatz Id des Redaktionsassistenten ausblenden', "1");
$field->setNotice('Wenn die Checkbox angehakt ist, wird das mit der Datensatz Id des Redaktionsassistenten im Backend nicht angezeigt. <code>[hide_id]</code>');

$field = $form->addLinkmapField('fixed_target_category');
$field->setLabel('Zielkategorie für Artikel festlegen (optional)');
$field->setNotice('Wenn hier eine Kategorie ausgewählt wird, so werden alle über den Redaktionsassistenten angelegten Artikel in diese Kategorie gespeichert. Wenn dieses Feld nicht ausgefüllt wird, so kann die Zielkategorie für jeden Artikel ausgewählt werden. <code>[fixed_target_category]</code>');

$field = $form->addTextField('url_scheme');
$field->setLabel('Url Schema');
$field->setNotice('Folgende Werte können codiert werden:<br>
    {path} = Pfad<br>
    {Y} = Jahr (4stellig)<br>
    {m} = Monat (2stellig)<br>
    {name} = Artikelname<br>
    {raid} = Datensatz Id des Redaktionsassistenten<br>
    Beispiel: /{Y}/{m}/{name}_{raid}/<br><code>[url_scheme]</code>');

$content = $form->get();

$fragment = new rex_fragment();
$fragment->setVar('title', 'Einstellungen');
$fragment->setVar('body', $content, false);
$content = $fragment->parse('core/page/section.php');

echo $content;



