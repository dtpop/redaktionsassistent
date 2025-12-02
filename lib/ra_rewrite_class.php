<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ra_rewrite_class
 *
 * @author wolfgang
 */
class ra_rewrite_class extends rex_yrewrite_scheme {
    protected $suffix = '/';
    

    public function appendArticle($path, rex_article $art, rex_yrewrite_domain $domain)
    {


//        rex_logger::factory()->log('info',$path,[],__FILE__,__LINE__);

        if ($art->isStartArticle() && $domain->getMountId() != $art->getId()) {
            return $path . $this->suffix;
        }

        if (rex_config::get('redaktionsassistent','url_scheme')) {
            return $this->get_from_scheme($path, $art , $domain);
        }


        $name = $this->normalize($art->getName(), $art->getClangId());
        if ($art->getValue('art_raid')) {
            $name .= '_'.$art->getValue('art_raid');
        }        
        return $path . '/' . $name . $this->suffix;
    }

    /**
     *     
     * 
    {path} = Pfad
    {Y} = Jahr (4stellig)
    {m} = Monat (2stellig)
    {name} = Artikelname
    {raid} = Datensatz Id des Redaktionsassistenten<br>

     */

    public function get_from_scheme($path, $art , $domain) {
        $url = '';
        $scheme = rex_config::get('redaktionsassistent','url_scheme');
        $Y = date('Y',$art->getValue('art_online_from'));
        $m = date('m',$art->getValue('art_online_from'));
        $name = $this->normalize($art->getName(), $art->getClang());
        $url = str_replace('{Y}',$Y,$scheme);
        $url = str_replace('{m}',$m,$url);
        $url = str_replace('{path}',$path,$url);
        $url = str_replace('{name}',$name,$url);

        return $url;




    }

}
