<?php

class Application_View_Helper_RazCountString extends Zend_View_Helper_Abstract
{
    public function razCountString($count)
    {
        $string = '';

        if ($count > 0) {
            $ost = $count % 10;
            if ($ost == 1) {
                $string = $count . ' раз';
            }
            if ($ost > 1 && $ost < 5) {
                $string = $count . ' раза';
            }
            if ($ost > 4 || $ost == 0) {
                $string = $count . ' раз';
            }

            $ostSto = $count % 100;
            if (in_array($ostSto, array(12, 13, 14))) {
                $string = $count . ' раз';
            }
        }

        return $string;
    }
}
