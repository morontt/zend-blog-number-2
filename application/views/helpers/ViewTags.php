<?php

class Application_View_Helper_ViewTags extends Zend_View_Helper_Abstract
{
    public function viewTags($tags)
    {
        $tagsArray = array();
        foreach ($tags as $tag) {
            $tagsArray[] = '<a href="' . $this->view->url(
                    array('url' => $tag->url), 'tag-page', true
                ) . '">' . $tag->name . '</a>';
        }
        
        return implode(' ', $tagsArray);
    }
}