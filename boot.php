<?php

setlocale(LC_TIME,'de');

rex_yform_manager_dataset::setModelClass('rex_redaktionsassistent', ra_data::class);

rex_yform::addTemplatePath($this->getPath('ytemplates'));

if (rex_addon::get('yrewrite')->isAvailable()) {
    rex_yrewrite::setScheme(new ra_rewrite_class());
}    


rex_extension::register('PACKAGES_INCLUDED', function( $ep ) {

    if (rex::isBackend() && rex_request('page') == 'redaktionsassistent/tasks') {
        
        // --bitte auswählen-- in Liste ersetzen
        rex_extension::register('OUTPUT_FILTER', function ($ep) {
            $text = $ep->getSubject();
            return str_replace('<td data-title="Stand Redaktion">--bitte auswählen--','<td data-title="Stand Redaktion">--',$text);
        });
        
        
        rex_extension::register('YFORM_DATA_LIST', function( $ep ) {
            $list = $ep->getSubject();

            $params = $list->getParams();
            $list->removeColumn('löschen');            
            
            $list->setColumnFormat('id', 'custom', function( $params ) {
                $id = $params['list']->getValue('id');
//            dump(get_defined_vars());
                return str_pad($id, 5, '0', STR_PAD_LEFT);
            });


            $list->setColumnFormat('name', 'custom', function ($params ) {
                $name = $params['list']->getValue('name');
                if ($art_id = $params['list']->getValue('rex_article')) {
                    $article = rex_article::get($art_id);
                    if ($article instanceof rex_article) {
                        $cat_id = $article->getCategoryId();
                        if ($cat_id) {
                            $name = '<a href="/redaxo/index.php?page=content/edit&category_id=' . $cat_id . '&article_id=' . $art_id . '&clang=1&mode=edit">' . $name . '</a>';
                        } else {
                            $name .= ' <strong>[Kategoriefehler]</strong>';                            
                        }
                    } else {
                        $name = '<strike>' . $name . '</strike> (gelöscht!)';
                    }
                }
                return $name;
            });
            
            
            



            // Bunte Farbkennzeichnung in der Liste nur wenn structure_plus installiert ist

            if (rex_addon::exists('structure_plus')) {
                /*
                $list->addColumn('rexstatus','',11);
                $list->setColumnLabel('rexstatus','Artikel Status');
                 * 
                 */
//                $list->removeColumn('art_status');
//                $list->addColumn('art_status','###art_status###',11);
                $list->setColumnLabel('art_status', 'Status');
                $list->setColumnFormat('art_status', 'custom', function ($params) {
                    $labels = [
                        'color_offline'=>'offline',
                        'color_gone'=>'abgelaufen',
                        'color_future'=>'freigegeben',
                        'color_online'=>'online',
                        'color_disabled'=>'gesperrt'
                    ];
                    if ($art_id = $params['list']->getValue('rex_article')) {
                        $article = rex_article::get($art_id);
                        if ($article instanceof rex_article) {
                            $class_additional = structure_plus::get_row_class($article->getValue('art_online_from'), $article->getValue('art_online_to'), $article->getValue('status'));
                            return '<span class="' . $class_additional . '">' . $labels[$class_additional] . '</span>';
                        } else {
                            return 'Artikel gelöscht';
                        }
                    }                
                });

                
                // Kategorie mit Hauptkategorie ausgeben
                $list->setColumnFormat('category', 'custom', function ($params ) {
                    $out = '';
                    if (!$params['value']) {
                        return '';
                    }
                    $cat = rex_category::get($params['value']);
                    if ($cat instanceof rex_category) {
                        $out = '['.$cat->getName().']';
                        $main = $cat->getParent();
                        if ($main instanceof rex_category) {
                            $out = '['.$main->getName() . '] ' . $out;
                        }
                    }
                    return $out;
                });
                
                // Klasse für art_online_from setzen
                $list->setColumnLabel('art_online_from', 'VÖ-Datum');
                $list->setColumnFormat('art_online_from', 'custom', function ($params ) {
                    $date = $params['list']->getValue('art_online_from');
                    $class_additional = 'std';
                    if ($art_id = $params['list']->getValue('rex_article')) {
                        $article = rex_article::get($art_id);
                        if ($article instanceof rex_article) {
                            $class_additional = structure_plus::get_row_class($article->getValue('art_online_from'), $article->getValue('art_online_to'), $article->getValue('status'));
                        }
                    }
                    return '<span class="' . $class_additional . '">' . date('d-m-Y',strtotime($date)) . '</span>';
                });

                rex_extension::register('OUTPUT_FILTER', function( $ep ) {
                    $style = '<style>
                   td span { display: block; padding: 4px 6px; margin: -4px 0; border-radius: 4px; }
                   tr span.color_online { background-color: ' . rex_config::get('structure_plus', 'color_online') . ' }
                   tr span.color_future { background-color: ' . rex_config::get('structure_plus', 'color_future') . ' }
                   tr span.color_offline { background-color: ' . rex_config::get('structure_plus', 'color_offline') . ' }
                   tr span.color_gone { background-color: ' . rex_config::get('structure_plus', 'color_gone') . ' }
                   tr span.color_disabled { background-color: ' . rex_config::get('structure_plus', 'color_disabled') . ' }
                </style>';
                    $text = $ep->getSubject();
                    $text = str_replace('</head>', $style . PHP_EOL . '</head>', $text);
                    return $text;
                });
            }
        });


        rex_extension::register('YFORM_DATA_UPDATED', function( $ep ) {
            $params = $ep->getSubject()->objparams['value_pool']['email'];
            
            
            // Rückwärtsaktualiserung in den Artikel + Cache löschen
            
            if ($params['rex_article']) {
                $values = [
                    'rex_article'=>$params['rex_article'],
                    'art_online_from'=>strtotime($params['art_online_from']),
//                    'art_type'=>$params['type'],
//                    'art_hauptteaser'=>'|'.$params['art_hauptteaser'].'|',
//                    'art_slider_override'=>'|'.$params['art_slider_override'].'|',                    
                ];
                ra_article::copy_values_to_article($values);
                rex_article_cache::delete($params['rex_article']);
            }
            
            if (rex_request('generate_article', 'int', 0)) {
                $params['art_raid'] = str_pad($params['ID'], 5, '0', STR_PAD_LEFT);
                $params['art_name'] = $params['name'];
                rex_set_session('ra_generate_article', $params);                
                ra_article::generate_article($ep->getSubject()->objparams['value_pool']['email']);
            }
        });


        rex_extension::register('YFORM_MANAGER_DATA_PAGE', function( $ep ) {
            rex_extension::register('OUTPUT_FILTER', function( $ep ) {
                $data_id = rex_request('data_id', 'int', '');
                $sql = rex_sql::factory()->setTable(rex::getTable('redaktionsassistent'));
                $sql->setWhere('id = :id', ['id' => $data_id]);
                $sql->select();
                $res = $sql->getArray();
                if ($res[0]['rex_article']) {
                    $text = $ep->getSubject();
//                    $text = str_replace('class="locked_element', 'disabled class="locked_element', $text);                    
                }
                return $text;
            });
        });
        
        
    }
    
    if (rex::isBackend()) {
        
        // Task Datei mit Artikeldaten synchronisieren        
        $extension_points = ['ART_STATUS','ART_MOVED','ART_UPDATED','ART_META_UPDATED','ART_DELETED'];
        foreach ($extension_points as $extension_point) {
            rex_extension::register($extension_point, function( $ep ) {
                $article_id = $ep->getParams()['id'];
                ra_article::sync_to_task(['rex_article'=>$article_id]);
            });
        }
        
        // Alle Artikel synchronisieren bei Cache löschen
        rex_extension::register('CACHE_DELETED', function( $ep ) {
            $sql = rex_sql::factory()->setTable(rex::getTable('redaktionsassistent'));
            $sql->select('rex_article,id,id2');
            $result = $sql->getArray();
//            $all_ids = array_column($result,'rex_article');
            foreach ($result as $rec) {
                ra_article::sync_to_task($rec, $cache_deleted = true);
            }
        });
        
        
        // Die Redaktionsassistent-Tabelle wird aktualisiert, der Artikel wird dort eingetragen

        rex_extension::register('ART_ADDED', function ($params) {
            $_params = $params->getParams();

            $ra_generate_article = rex_session('ra_generate_article','array',[]);

            $sql = rex_sql::factory();

            // Art Online Datum setzen, wenn die Session gesetzt ist
            // Property wird über boot.php aus redaktionsassistent gesetzt
            if (isset($ra_generate_article['art_online_from'])) {
                // art_online_from Datum in Artikeltabelle eintragen
                $values = [];
                $values['art_online_from'] = strtotime($ra_generate_article['art_online_from']);
                $values['art_raid'] = $ra_generate_article['art_raid'];
//                $values['art_type'] = $ra_generate_article['type'];
                $sql->setTable(rex::getTable('article'));
                $sql->setValues($values);
                $sql->setWhere('id = :id',['id'=>$_params['id']]);
                $sql->setDebug();
                $sql->update();

                // Session wieder zurücksetzen
                rex_set_session('ra_generate_article', '');

                rex_set_session('ra_generated_article_id', $_params['id']);

                // Redaxo Artikel in Verwaltungstabelle eintragen
                $sql->setTable(rex::getTable('redaktionsassistent'));
                $values = [
                    'rex_article'=>$_params['id']
                ];
                $sql->setValues($values);
                $sql->setWhere('id = :id',['id'=>$ra_generate_article['ID']]);
                $sql->update();



            }


        });
        
        // Wenn ein Artikel kopiert wurde, wird der Artikelname aus dem Redaktionsassistenten gesetzt
        // Die Redaktionsassistent-Tabelle wird aktualisiert, der Artikel wird dort eingetragen
        
        rex_extension::register('ART_COPIED', function ($params) {
            $_params = $params->getParams();

            $ra_generate_article = rex_session('ra_generate_article','array',[]);

            $sql = rex_sql::factory();

            // Art Online Datum setzen, wenn die Session gesetzt ist
            // Property wird über boot.php aus redaktionsassistent gesetzt
            if (isset($ra_generate_article['art_online_from'])) {
                // art_online_from Datum in Artikeltabelle eintragen
                $values = [];
                $values['art_online_from'] = strtotime($ra_generate_article['art_online_from']);
//                $values['art_slider_override'] = $ra_generate_article['art_slider_override'];
//                $values['art_hauptteaser'] = $ra_generate_article['art_hauptteaser'];
                $values['art_raid'] = $ra_generate_article['art_raid'];
//                $values['art_type'] = $ra_generate_article['type'];
                if (isset($ra_generate_article['art_name'])) {
                    $values['name'] = $ra_generate_article['art_name'];
                }
                $sql->setTable(rex::getTable('article'));
                $sql->setValues($values);
                $sql->setWhere('id = :id',['id'=>$_params['id']]);
    //            $sql->setDebug();
                $sql->update();

                // Session wieder zurücksetzen
                rex_set_session('ra_generate_article', '');

                rex_set_session('ra_generated_article_id', $_params['id']);

                // Redaxo Artikel in Verwaltungstabelle eintragen
                $sql->setTable(rex::getTable('redaktionsassistent'));
                $values = [
                    'rex_article'=>$_params['id']
                ];
                $sql->setValues($values);
                $sql->setWhere('id = :id',['id'=>$ra_generate_article['ID']]);
                $sql->update();
                rex_article_cache::delete($_params['id']);
            }        
        });    
        
        
        
    }
    
});


