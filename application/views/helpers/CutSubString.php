<?php

class Application_View_Helper_CutSubString extends Zend_View_Helper_Abstract
{
    public function cutSubString($text, $length)
	{
        if (mb_strlen($text, 'UTF-8') > $length) {
            $text = mb_substr($text, 0, $length - 3, 'UTF-8') . '...';
        }

        return $text;
    }
}
