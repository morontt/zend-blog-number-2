<?php

class Application_View_Helper_ViewDateTime extends Zend_View_Helper_Abstract
{
    public function viewDateTime($time, $format = 'd M Y, H:i')
    {
        $dateTime = new DateTime($time);
        
        return $dateTime->format($format);
    }
}