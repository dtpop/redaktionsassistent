<?php

/**
 * yform.
 *
 * @author jan.kristinus[at]redaxo[dot]org Jan Kristinus
 * @author <a href="http://www.yakamara.de">www.yakamara.de</a>
 */

class rex_yform_value_generate_article_button extends rex_yform_value_abstract
{
    public function init()
    {
        $this->params['submit_btn_show'] = false;
    }

    public function enterObject()
    {
        $params = $this->params['value_pool']['email'];
        $labels = $this->getElement('labels');
        if ($labels == '') {
            $labels = [$this->getElement('label')];
        } else {
            $labels = explode(',', $this->getElement('labels'));
        }

        $values = $this->getElement('values');
        if ($values == '') {
            $values = [];
        } else {
            $values = explode(',', $values);
        }
        
        $default_value = '';
        if ($this->getElement('default')) {
            $default_value = $this->getElement('default');
        }

        if (in_array($this->getValue(), $labels)) {
            $key = array_search($this->getValue(), $labels);
            if (isset($values[$key])) {
                $value = $values[$key];
            } else {
                $value = $default_value;
            }
        } else {
            $value = $default_value;
        }

        $this->setValue($value);

        if (count($labels) == 0) {
            $labels = [$value];
        }

        if (count($labels) == 1 && $this->getElement('css_classes') == '') {
            $this->setElement('css_classes', 'btn-primary');
        }
        
        $article = rex_article::get($params['rex_article']);
        

        if ($this->needsOutput()) {
            if ($this->params['main_id'] < 1) {
                $this->params['form_output'][$this->getId()] = '<div class="form-group clearfix" id="vp_create_article_element">
                <button class="btn btn-save" style="float:right" disabled="disabled" name="generate_article" type="submit" id="generate_article" value="0">Artikel anlegen</button>
                </div>';
                $this->params['form_output'][$this->getId()] .= '<p class="help-block text-right">Um den Artikel Anzulegen bitte zuerst auf "übernehmen" klicken</p>';
            } elseif ($params['rex_article'] && $article instanceof rex_article) {
                $this->params['form_output'][$this->getId()] = '<div class="form-group text-right" id="vp_jump_article">
                <a href="/redaxo/index.php?page=content/edit&category_id='.$params['category'].'&article_id='.$params['rex_article'].'&clang=1&mode=edit" class="btn btn-save">Artikel bearbeiten</a>
                </div>';
            } else {
                $this->params['form_output'][$this->getId()] = '<div class="form-group clearfix" id="vp_create_article_element">
                <button class="btn btn-save" style="float:right" name="generate_article" type="submit" id="generate_article" value="1">Artikel anlegen</button>
                </div>';                
            }
        }

        if (!isset($this->params['value_pool']['email'][$this->getName()]) || $this->params['value_pool']['email'][$this->getName()] == '') {
            $this->params['value_pool']['email'][$this->getName()] = $this->getValue();
        }

        if ($this->saveInDb() && $this->getElement(3) != 'no_db') { // BC element[3]
            if (!isset($this->params['value_pool']['sql'][$this->getName()]) || $this->params['value_pool']['sql'][$this->getName()] == '') {
                $this->params['value_pool']['sql'][$this->getName()] = $this->getValue();
            }
        }
    }

    public function getDescription()
    {
        return 'generate_article_button|name|labelvalue1_on_button1,labelvalue2_on_button2| [value_1_to_save_if_clicked,value_2_to_save_if_clicked] | [no_db] | [Default-Wert] | [cssclassname1,cssclassname2]';
    }

    public function getDefinitions()
    {
        return [
            'type' => 'value',
            'name' => 'generate_article_button',
            'values' => [
                'name' => ['type' => 'name',    'label' => rex_i18n::msg('yform_values_defaults_name')],
                'labels' => ['type' => 'text',    'label' => rex_i18n::msg('yform_values_submit_labels')],
                'values' => ['type' => 'text',    'label' => rex_i18n::msg('yform_values_submit_values')],
                'no_db' => ['type' => 'no_db',   'label' => rex_i18n::msg('yform_values_defaults_table'),  'default' => 0],
                'default' => ['type' => 'text',    'label' => rex_i18n::msg('yform_values_submit_default')],
                'css_classes' => ['type' => 'text',    'label' => rex_i18n::msg('yform_values_submit_css_classes'),
                ],
            ],
            'description' => rex_i18n::msg('yform_values_submit_description'),
            'db_type' => ['text'],
            'is_searchable' => false,
            'is_hiddeninlist' => true,
        ];
    }
}
