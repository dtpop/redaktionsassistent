<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ra_article
 *
 * @author wolfgang
 */

class ra_data extends \rex_yform_manager_dataset {
    
    
}

class ra_article {
    
    
    /**
     * Artikel anlegen über Redaktionsassistent
     * 
     * @param type $params
     */
    public static function generate_article ($params) {
        /*     Relevante params:
         *     "name" => "gfdgsdfgsfdgsfdgsfdg"
                "category" => "15"
                "type" => "8"
                "date" => "2019-03-12"
                "notice" => "dfs fsgd sg sfdg "
                "status" => "1"
                "rex_article" => ""
                "ID" => 2
         * 
         */
        
        $sql = rex_sql::factory()->setTable(rex::getTable('redaktionsassistent_type'));
        $sql->setWhere('id = :id',['id'=>$params['type']]);
        $sql->select();
        $typerecord = $sql->getArray();
        $template_id = 1;
        $article_template_id = 0;
        
        // Wenn ein Artikel als Template definiert ist
        if ($typerecord && $typerecord[0]['article_template_id']) {
            $article_template_id = $typerecord[0]['article_template_id'];            
        } elseif ($typerecord) {
            $template_id = $typerecord[0]['template_id'];
        }
        
        if ($article_template_id) {

            rex_article_service::copyArticle($article_template_id,$params['category']);
            
        } else {        
            $data = [
                'category_id' => $params['category'],
                'priority' => 1,
                'name' => $params['name'],
                'template_id' => 1,
            ];

            rex_article_service::addArticle($data);
        }
        
        if ($art_id = rex_session('ra_generated_article_id','int',0)) {
            rex_response::sendRedirect(trim(rex::getServer(),'/').'/redaxo/index.php?page=content/edit&category_id='.$params['category'].'&article_id='.$art_id.'&clang=1&mode=edit');
            rex_set_session('ra_generated_article_id','');
        }
        

        
//        rex_response::sendRedirect(rex_getUrl($article_id, $clang, $params, '&'));
    }
    
    
    /**
     * 
     * @param type $rec - Array id,id2,rex_article aus rex_redaktionsassistent
     */
    public static function sync_to_task ($rec, $cache_deleted = false) {
        $article_id = $rec['rex_article'];
        $article = rex_article::get($article_id);
        $sql = rex_sql::factory()->setTable(rex::getTable('redaktionsassistent'));
        $sql->setWhere('rex_article = :id',['id'=>$article_id]);
        if ($article instanceof rex_article) {
            $values = [
                'art_online_from' => date('Y-m-d',$article->getValue('art_online_from')),
                'category' => $article->getCategoryId(),
                'art_status' => $article->getValue('status'),
//                'art_hauptteaser' => trim($article->getValue('art_hauptteaser'),'|'),
//                'art_slider_override' => trim($article->getValue('art_slider_override'),'|'),
                'name' => $article->getName()
            ];
            // prüfen, ob Redaktionsassistent-Id in Meta Info eingetragen ist
            // wenn nicht, eintragen
            if ($cache_deleted && !$article->getValue('art_raid') && $rec['id2'] && $rec['rex_article']) {
                self::sync_to_article($rec);
            }
        } else {
            $values = [
                'rex_article' => ''
            ];            
        }
        $sql->setValues($values);
        $sql->update();
        
    }
    
    /**
     * Kopiert die Arbeits Id in den Artikel
     * 
     * @param type $rec
     */
    public static function sync_to_article ($rec) {
        $sql = rex_sql::factory()->setTable(rex::getTable('article'));
        $sql->setWhere('id = :id', ['id'=>$rec['rex_article']]);
        $values = [
            'art_raid' => $rec['id2']
        ];
        $sql->setValues($values);
        $sql->update();
    }
    
    /**
     * Kopiert alle Values in den Artikel, die über $values übergeben wurden
     * id des Artikels steht in $values['rex_article]
     * 
     * @param type $values
     */
    public static function copy_values_to_article ($values) {
        $sql = rex_sql::factory()->setTable(rex::getTable('article'));
        $sql->setWhere('id = :id', ['id'=>$values['rex_article']]);
        unset($values['rex_article']);
        $sql->setValues($values);
        $sql->update();
        
    }



    
}
