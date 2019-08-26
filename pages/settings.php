<?php
$form = rex_config_form::factory('redaktionsassistent');
$form->addFieldset('Redaktionsassistent - Einstellungen');

$field = $form->addCheckboxField('regenerate_paths');
$field->setLabel('Path Felder in Artikeln regenerieren');
$field->addOption('Path Felder in Artikeln regenerieren', "1");
$field->setNotice('Wenn der Cache gelöscht wird und die Checkbox gesetzt ist, werden alle <code>path</code> Einträge in der Artikeldatenbank neu geschrieben. Die Artikelstruktur bleibt erhalten. Dies sollte nur im Ausnahmefall notwendig sein.');

$content = $form->get();

$fragment = new rex_fragment();
$fragment->setVar('title', 'Einstellungen');
$fragment->setVar('body', $content, false);
$content = $fragment->parse('core/page/section.php');

echo $content;



