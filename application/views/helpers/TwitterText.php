<?php

class Application_View_Helper_TwitterText extends Zend_View_Helper_Abstract
{

    public function twitterText($text)
    {
        $text = preg_replace("/(^|[\n ])([\w]*?)((ht|f)tp(s)?:\/\/[\w]+[^ \,\"\n\r\t<]*)/is", "$1$2<a href=\"$3\" >$3</a>", $text);
        $text = preg_replace("/(^|[\n ])([\w]*?)((www|ftp)\.[^ \,\"\t\n\r<]*)/is", "$1$2<a href=\"http://$3\" >$3</a>", $text);

        $text = preg_replace("(@([A-Za-z0-9_]+))", '<a class="user" href="https://twitter.com/$1">@$1</a>', $text);
        $text = preg_replace("(#([A-Za-z0-9_]+))", '<a class="hash" href="https://twitter.com/search?q=%23$1&amp;src=hash">#$1</a>', $text);

        return $text;
    }

}