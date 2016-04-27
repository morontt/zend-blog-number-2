<?php

class Application_View_Helper_CommentsCountString extends Zend_View_Helper_Abstract
{
    public function commentsCountString($count)
    {
        $string = '';

        if ($count > 0) {
            $ost = $count % 10;
            if ($ost == 1) {
                $string = $count . ' комментарий';
            }
            if ($ost > 1 && $ost < 5) {
                $string = $count . ' комментария';
            }
            if ($ost > 4 || $ost == 0) {
                $string = $count . ' комментариев';
            }

            $ostSto = $count % 100;
            if (in_array($ostSto, array(11, 12, 13, 14))) {
                $string = $count . ' комментариев';
            }
        }

        return $string;
    }
}
