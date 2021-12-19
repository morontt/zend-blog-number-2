<?php

class Application_View_Helper_GetAvatar extends Zend_View_Helper_Abstract
{
    public function getAvatar($comment)
    {
        if (isset($comment['deleted']) && $comment['deleted']) {
            $options = Zend_Registry::get('options');
            $cdn = $options['cdn_host'];

            return $cdn . '/img/clown.png';
        }

        $defaultsArray = array(
            'wavatar',
            'monsterid',
        );

        $countArray = count($defaultsArray);

        if (isset($comment['user_id']) && $comment['user_id']) {
            $hash         = $comment['user_email_hash'];
            $indexDefault = ($comment['user_id']) % $countArray;
        } else {
            if ($comment['email_hash']) {
                $hash = $comment['email_hash'];
            } else {
                $hash = Application_Model_Commentators::getAvatarHash(
                    $comment['name'],
                    $comment['mail'],
                    $comment['website']
                );
            }
            $indexDefault = ($comment['commentator_id']) % $countArray;
        }

        return '//www.gravatar.com/avatar/' . $hash . '?d=' . $defaultsArray[$indexDefault];
    }
}
