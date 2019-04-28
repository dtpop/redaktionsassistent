<?php

/**
 * yform.
 *
 * @author jan.kristinus[at]redaxo[dot]org Jan Kristinus
 * @author <a href="http://www.yakamara.de">www.yakamara.de</a>
 */

class rex_yform_value_be_select_category_ra extends rex_yform_value_abstract
{
    public function enterObject()
    {
        $params = $this->params['value_pool']['email'];
        
        if (is_array($this->getValue())) {
            $this->setValue(implode(',', $this->getValue()));
        }

        $this->params['value_pool']['email'][$this->getName()] = $this->getValue();
        if ($this->saveInDb()) {
            $this->params['value_pool']['sql'][$this->getName()] = $this->getValue();
        }

        if (!$this->needsOutput()) {
            return;
        }

        $multiple = $this->getElement('multiple') == 1;

        $options = [];
        if ($this->getElement('homepage')) {
            $options[0] = rex_i18n::msg('yform_values_be_select_category_homepage_title');
        }

        $ignoreOfflines = $this->getElement('ignore_offlines');
        $checkPerms = $this->getElement('check_perms');
        $clang = (int) $this->getElement('clang');

        $add = function (rex_category $cat, $level = 0) use (&$add, &$options, $ignoreOfflines, $checkPerms, $clang) {
            if (!$checkPerms || rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($cat->getId())) {
                $cid = $cat->getId();
                $cname = $cat->getName();

                if (rex::getUser()->hasPerm('advancedMode[]')) {
                    $cname .= ' [' . $cid . ']';
                }

                $options[$cid] = str_repeat('&nbsp;&nbsp;&nbsp;', $level) . $cname . '__' . $level;
                $childs = $cat->getChildren($ignoreOfflines);
                if (is_array($childs)) {
                    foreach ($childs as $child) {
                        $add($child, $level + 1);
                    }
                }
            }
        };
        if ($rootId = $this->getElement('category')) {
            if ($rootCat = rex_category::get($rootId, $clang)) {
                $add($rootCat);
            }
        } else {
            if (!$checkPerms || rex::getUser()->isAdmin() || rex::getUser()->hasPerm('csw[0]')) {
                if ($rootCats = rex_category::getRootCategories($ignoreOfflines, $clang)) {
                    foreach ($rootCats as $rootCat) {
                        $add($rootCat);
                    }
                }
            } elseif (rex::getUser()->getComplexPerm('structure')->hasMountpoints()) {
                $mountpoints = rex::getUser()->getComplexPerm('structure')->getMountpoints();
                foreach ($mountpoints as $id) {
                    $cat = rex_category::getCategoryById($id, $clang);
                    if ($cat && !rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($cat->getParentId())) {
                        $add($cat);
                    }
                }
            }
        }

        if ($multiple) {
            $size = (int) $this->getElement('size');
            if ($size < 2) {
                $size = count($options);
            }
        } else {
            $size = 1;
        }

        if (!is_array($this->getValue())) {
            $this->setValue(explode(',', $this->getValue()));
        }
        
        $output = $this->parse('value.selectra.tpl.php', compact('options', 'multiple', 'size'));
        
//        dump($params);
        if ($params['rex_article']) {
            $article = rex_article::get($params['rex_article']);
            if ($article instanceof rex_article) {
                $cat = $article->getCategory();
                $val = $this->getValue();
                if ($cat instanceof rex_category) {
                    $output = '<input type="hidden" name="'.$this->getFieldName().'" value="'.$cat->getId().'">';
                    $output .= '<div class="form-group" id="yform-data_edit-rex_redaktionsassistent-name">
                        <label class="control-label">Kategorie</label>
                        <input class="form-control" type="text" readonly="readonly" name="dummyfeld" value="'.$cat->getName().'">
                        </div>';
                }
            }
        }

        $this->params['form_output'][$this->getId()] = $output;

        $this->setValue(implode(',', $this->getValue()));
    }

    public function getDefinitions()
    {
        return [
            'type' => 'value',
            'name' => 'be_select_category_ra',
            'values' => [
                'name' => ['type' => 'name',   'label' => rex_i18n::msg('yform_values_defaults_name')],
                'label' => ['type' => 'text',    'label' => rex_i18n::msg('yform_values_defaults_label')],
                'ignore_offlines' => ['type' => 'boolean', 'label' => rex_i18n::msg('yform_values_be_select_category_ignore_offlines'), 'default' => 1],
                'check_perms' => ['type' => 'boolean', 'label' => rex_i18n::msg('yform_values_be_select_category_check_perms'), 'default' => 1],
                'homepage' => ['type' => 'boolean', 'label' => rex_i18n::msg('yform_values_be_select_category_homepage'), 'default' => 1],
                'category' => ['type' => 'text',    'label' => rex_i18n::msg('yform_values_be_select_category_category'), 'value' => 0],
                'clang' => ['type' => 'select_sql',    'query' => 'select id, code as name from rex_clang', 'label' => rex_i18n::msg('yform_values_be_select_category_clang'), 'value' => 1],
                'multiple' => ['type' => 'boolean', 'label' => rex_i18n::msg('yform_values_be_select_category_multiple')],
                'size' => ['type' => 'text',    'label' => rex_i18n::msg('yform_values_be_select_category_size')],
                'no_db' => ['type' => 'no_db',   'label' => rex_i18n::msg('yform_values_defaults_table'), 'default' => 0],
                'attributes' => ['type' => 'text',    'label' => rex_i18n::msg('yform_values_defaults_attributes'), 'notice' => rex_i18n::msg('yform_values_defaults_attributes_notice')],
                'notice' => ['type' => 'text',    'label' => rex_i18n::msg('yform_values_defaults_notice')],
            ],
            'description' => rex_i18n::msg('yform_values_be_select_category_description'),
            'formbuilder' => false,
            'db_type' => ['text'],
        ];
    }

    public static function getListValue($params)
    {
        $return = [];

        foreach (explode(',', $params['value']) as $id) {
            if ($cat = rex_category::get($id, (int) $params['params']['field']['clang'])) {
                $return[] = $cat->getName();
            }
        }

        return implode('<br />', $return);
    }
    
    public static function getSearchField($params)
    {

        $options = [];

        $ignoreOfflines = false;
        $checkPerms = false;
        $clang = (int) rex_clang::getCurrentId();

        $add = function (rex_category $cat, $level = 0) use (&$add, &$options, $ignoreOfflines, $checkPerms, $clang) {
            if (!$checkPerms || rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($cat->getId())) {
                $cid = $cat->getId();
                $cname = $cat->getName();

                if (rex::getUser()->hasPerm('advancedMode[]')) {
                    $cname .= ' [' . $cid . ']';
                }

                $options[$cid] = str_repeat('&nbsp;&nbsp;&nbsp;', $level-1) . $cname;
                $childs = $cat->getChildren($ignoreOfflines);
                if (is_array($childs)) {
                    foreach ($childs as $child) {
                        $add($child, $level + 1);
                    }
                }
            }
        };
        
        if ($rootId = 8) {
            if ($rootCat = rex_category::get($rootId, $clang)) {
                $add($rootCat);
            }
        }        
        
        array_shift($options);
        
        $params['searchForm']->setValueField('select', [
            'name' => $params['field']->getName(),
            'label' => $params['field']->getLabel(),
            'options' => $options,
            'multiple' => 1,
            'size' => 5,
            'notice' => rex_i18n::msg('yform_search_defaults_select_notice'),
        ]
        );
    }

    public static function getSearchFilter($params)
    {
        $sql = rex_sql::factory();
        $field = $params['field']->getName();
        $values = (array) $params['value'];

        $multiple = $params['field']->getElement('multiple') == 1;

        $where = [];
        foreach ($values as $value) {
            switch ($value) {
                case '(empty)':
                    $where[] = $sql->escapeIdentifier($field).' = ""';
                    break;
                case '!(empty)':
                    $where[] = $sql->escapeIdentifier($field).' != ""';
                    break;
                default:
                    if ($multiple) {
                        $where[] = ' ( FIND_IN_SET( ' . $sql->escape($value) . ', ' . $sql->escapeIdentifier($field) . ') )';
                    } else {
                        $where[] = ' ( ' . $sql->escape($value) . ' = ' . $sql->escapeIdentifier($field) . ' )';
                    }
                    break;
            }
        }

        if (count($where) > 0) {
            return ' ( ' . implode(' or ', $where) . ' )';
        }
    }
    
    
    
}
