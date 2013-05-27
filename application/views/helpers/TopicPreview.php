<?php

class Application_View_Helper_TopicPreview extends Zend_View_Helper_Abstract
{
    public function topicPreview($topic, $url)
	{
        $preview = explode('<!-- cut -->',$topic);
        $newTopic = $preview[0];

        if (isset($preview[1]))
        {
            $addUrl = '<div class="link-preview"><a href="' . $url . '">Читать далее&crarr;</a></div>';
            $newTopic .= $addUrl;
        }

        return $newTopic;
    }
}
