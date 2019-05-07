<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ra_list
 *
 * @author wolfgang
 */
class ra_list {
    //put your code here
    public static function edit_column ($params) {
        return '<input class="cbx_ra_assign_article" type="checkbox" name="assign[]" value="'.$params['params']['id'].'">';
    }
    
    public static function assign_articles () {
        $ids = rex_escape(rex_request('assign','array'));
        
        $sql = rex_sql::factory();
        
        foreach ($ids as $art_id) {
            
            // Werte aus rex_aricle auslesen
            $sql->setTable(rex::getTable('article'));
            $sql->setWhere('id = :id AND clang_id = 1',['id'=>$art_id]);
            $sql->select();
            $res = $sql->getArray();
            if (count($res) != 1) {
                continue;
            }
            $res = $res[0];
            
            
            // Werte für Redaktionsasssitent vorbereiten
            $values = [
                'rex_article'=>$art_id,
                'name'=>$res['name'],
                'art_online_from'=>date('Y-m-d',$res['art_online_from']),
                'category'=>$res['parent_id'],
                'art_status'=>$res['status'],
            ];            
            
            // neuen Datensatz im Redaktionsassistent anlegen
            $sql->setTable(rex::getTable('redaktionsassistent'));
            $sql->setValues($values);
            $sql->insert();
            $ra_id = $sql->getLastId();
            $sql->setTable(rex::getTable('redaktionsassistent'));
            $sql->setWhere('id = :id',['id'=>$ra_id]);
            $sql->setValue('id2',str_pad($ra_id, 5, '0', STR_PAD_LEFT));
            $sql->update();            
            
            // Nummer in art_raid schreiben (alle Sprachen)
            $sql->setTable(rex::getTable('article'));
            $sql->setWhere('id = :id',['id'=>$art_id]);
            $sql->setValue('art_raid', str_pad($ra_id, 5, '0', STR_PAD_LEFT));
            $sql->update();            
        }
        
        // alle id2 in Redaktionsassistent setzen
        
        return true;
    }
    
}
