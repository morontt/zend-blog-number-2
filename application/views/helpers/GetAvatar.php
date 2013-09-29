<?php

class Application_View_Helper_GetAvatar extends Zend_View_Helper_Abstract
{

    public function getAvatar($comment)
    {
        $defaultsArray = array(
            'wavatar',
            'monsterid',
        );

        $countArray = count($defaultsArray);

        if (isset($comment['user_id']) && $comment['user_id']) {
            $hash         = md5(strtolower(trim($comment['user_mail'])));
            $indexDefault = ($comment['user_id']) % $countArray;
        } else {
            $hash = Application_Model_Commentators::getAvatarHash(
                $comment['name'],
                $comment['mail'],
                $comment['website']
            );
            $indexDefault = ($comment['commentator_id']) % $countArray;
        }

        $avatarObject = new Application_Model_Proxy_Avatar();
        $avatarArray = $avatarObject->getByHash($hash, $indexDefault);

        $src = 'http://www.gravatar.com/avatar/' . $hash . '?d=' . $defaultsArray[$indexDefault];
        if ($avatarArray && $avatarArray['src']) {
            $fileName = realpath(APPLICATION_PATH . '/../img/avatar') . DIRECTORY_SEPARATOR . $avatarArray['src'];
            if (file_exists($fileName)) {
                $src = IMG_DOMAIN . '/avatar/' . $avatarArray['src'];
            }
        }

        return $src;
    }

}