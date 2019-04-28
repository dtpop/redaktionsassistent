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
        if ($art->isStartArticle() && $domain->getMountId() != $art->getId()) {
            return $path . $this->suffix;
        }
        $name = $this->normalize($art->getName(), $art->getClang());
        if ($art->getValue('art_raid')) {
            $name .= '_'.$art->getValue('art_raid');
        }        
        return $path . '/' . $name . $this->suffix;
    }
    
}
