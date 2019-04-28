<?php

/**
 * Description of ra_helper
 * Helper Klassen für Redaktionsassistent
 *
 * @author wolfgang
 */
class ra_helper {
    
   
    /**
     * Holt Metadaten oder Daten aus dem Intro- bzw. Bildslice für die Vorschau
     * 
     * @param type $art_id
     * @return string
     */
    public static function get_article_intro ($article = null) {
        $return = [
            'rex_article_id'=>0,
            'title'=>'',
            'description'=>'',
            'image'=>'',
            'link'=>'',
            'date'=>'',
            'categoryname'=>'',
            'art_siegel'=>''
        ];
        if (!$article) {
            $article = rex_article::getCurrent();
        }
        $art_id = $article->getId();
        $return['description'] = (string) $article->getValue('art_description');
        $return['title'] = $article->getName();
        $return['rex_article_id'] = $article->getId();
        $return['link'] = $article->getUrl();
        $return['date'] = self::formatdate($article->getValue('art_online_from'));
        $return['categoryname'] = $article->getCategory()->getName();
        $return['image'] = $article->getValue('art_teaser_img');
        $return['art_siegel'] = $article->getValue('art_siegel');
        if (!$return['description'] || !$return['title']) {
            $slices = rex_article_slice::getSlicesForArticleOfType($art_id,1);            
            if ($slices[0] instanceof rex_article_slice) {
                // Vorrang hat die Überschrift aus dem Slice
                if ($slices[0]->getValue(3)) {
                    $return['title'] = $slices[0]->getValue(3);
                }
                // Vorrang hat die Beschreibung aus den Metainfos
                if (!$return['description'] && $slices[0]->getValue(4)) {
                    $return['description'] = $slices[0]->getValue(4);
                }
            }
        }
        
        // Vorrang hat das Bild aus den Metainfos
        if (!$return['image']) {
            // Bildslices
            $slices = rex_article_slice::getSlicesForArticleOfType($art_id,2);
            if ($slices[0] instanceof rex_article_slice) {
                $return['image'] = $slices[0]->getMedia(1);
            }            
        }        
        
        return $return;
        
    }
    
    /**
     * 
     * 
     * @param type $date
     * @return type
     */
    public static function formatdate($date) {
        return date('d.m.Y',$date);
    }
    
    /**
     * Findet die neuesten Artikel
     * Datum wird berücksichtigt
     * online Status wird berücksichtigt
     * Standardmäßig wird im ganzen Pfad (Kategorie + Unterkategorie) gesucht
     * 
     * Wird im Megamenü verwendet
     * 
     * @param type $count
     * @param type $category_id
     */
    public static function find_newest_articles ($count = 10, $category_id = 0, $start = 0, $get_rows = false) {
        
        $sql = rex_sql::factory();
        $limit = '';
        if ($count) {
            $limit = ' LIMIT ' . $start.','.$count;
        }
        
        $where = self::get_where_for_online_articles();
        
        $params = [
            'status'=>1,
            'startarticle'=>0,
            'art_online_from'=>(string)time(),
            'art_online_to'=>(string)time()
            ];
        
        if ($category_id) {
            $where .= ' AND FIND_IN_SET(:category_id,REPLACE(path,"|",","))';
            $params['category_id'] = $category_id;
        }
        
        
        
        $qry = 'SELECT id FROM '.rex::getTable('article').' '
                . 'WHERE '.$where.' '
                . 'ORDER BY art_online_from DESC'.$limit;
        $sql->setQuery($qry,$params);
        
        if ($get_rows) {
            return $sql->getRows();
        }
        
        $result = $sql->getArray();
        $result = array_column($result,'id');
        $articles = [];
        foreach ($result as $res) {
            $articles[] = rex_article::get($res);
        }
        
        return $articles;       
        
    }
    
    
    /**
     * Findet die neuesten Teaser.
     * Ids, die im Array $exclude übergeben werden, werden nicht ausgegeben
     * Wird für die Startseite "aktuelle News aus der Versicherungsbranche"
     * genutzt. Die Artikel, die im Slider angezeigt werden, werden nicht in der Liste ausgegeben
     * 
     * @param type $count
     * @param type $exclude
     */
    public static function get_newest_teasers ($count = 10, $exclude = []) {
        $all_teaser = self::find_newest_articles($count+count($exclude));
        $return = [];
        $exclude_ids = [];
        
        foreach ($exclude as $v) {
            $exclude_ids[] = $v['rex_article_id'];
        }
        
        foreach ($all_teaser as $teaser_id) {
            // Wenn der Teaser schon als Slider ausgegeben wird, nicht aufnehmen
            if (in_array($teaser_id,$exclude_ids)) continue;
            $return[] = self::get_article_intro(rex_article::get($teaser_id));
            if (count($return) == $count) break;            
        }
        return $return;        
    }
    
    /**
     * Platzhalter:
     * :status
     * :startarticle
     * :art_online_from
     * :art_online_to
     * 
     * @return type
     */
    public static function get_where_for_online_articles () {
        return 'status = :status AND startarticle = :startarticle '
                . 'AND art_online_from < :art_online_from '
                . 'AND (ISNULL(art_online_to) OR art_online_to = "0" OR art_online_to = "" OR  art_online_to > :art_online_to)';
    }
    
    
    

    
    /**
     * Prüft, ob ein Artikel online ist.
     * 
     * @param type $article
     */
    public static function is_article_online ($article) {
        if (!$article instanceof rex_article) return false;
        if (!$article->isOnline()) return false;
        if ($article->getValue('art_online_from') > time()) return false;
        if ($article->getValue('art_online_to') && $article->getValue('art_online_to') < time()) return false;
        return true;        
    }
    
}
