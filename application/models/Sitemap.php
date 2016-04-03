<?php

class Application_Model_Sitemap
{
    public static function generateSitemap()
    {
        $topic = new Application_Model_Posts();
        $arrayTopic = $topic->getSitemapTopic();

        $baseUrl = BASE_URL;
        $router = Zend_Controller_Front::getInstance()->getRouter();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<?xml-stylesheet type="text/xsl" href="/sitemap.xsl"?>' . PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

        foreach ($arrayTopic as $value) {
            $url = BASE_URL . $router->assemble(array('url' => $value['url']), 'topic_url', false, true);

            //time format
            list($year, $month, $day, $hour, $min, $sec) = sscanf($value['last_update'], "%d-%d-%d %d:%d:%d");
            $timestamp = mktime($hour, $min, $sec, $month, $day, $year);
            $time = date('c', $timestamp);

            $xml .= '<url>' . PHP_EOL;
            $xml .= '  <loc>' . $url . '</loc>' . PHP_EOL;
            $xml .= '  <lastmod>' . $time . '</lastmod>' . PHP_EOL;
            $xml .= '  <changefreq>monthly</changefreq>' . PHP_EOL;
            $xml .= '  <priority>0.80</priority>' . PHP_EOL;
            $xml .= '</url>' . PHP_EOL;
        }
        $currentDateTime = date('c');

        //static page
        $staticPage = <<<STATICPAGE
<url>
  <loc>$baseUrl/info</loc>
  <lastmod>2013-01-02T13:47:11+02:00</lastmod>
  <changefreq>monthly</changefreq>
  <priority>0.50</priority>
</url>
<url>
  <loc>$baseUrl/statistika</loc>
  <lastmod>$currentDateTime</lastmod>
  <changefreq>monthly</changefreq>
  <priority>0.50</priority>
</url>
<url>
  <loc>$baseUrl/login</loc>
  <lastmod>2011-08-01T21:25:47+00:00</lastmod>
  <changefreq>yearly</changefreq>
  <priority>0.50</priority>
</url>
STATICPAGE;

        $xml .= $staticPage . PHP_EOL;
        $xml .= '</urlset>' . PHP_EOL;

        return $xml;
    }
}
