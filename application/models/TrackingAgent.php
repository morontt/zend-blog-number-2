<?php

class Application_Model_TrackingAgent extends Zend_Db_Table_Abstract
{
    protected $_name = 'tracking_agent';

    const FILTER_BOT     = 0;
    const FILTER_BROWSER = 1;

    public function getAgent()
    {
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $userAgent = $_SERVER['HTTP_USER_AGENT'];
        } else {
            $userAgent = 'unknown';
        }

        $hash = md5($userAgent);
        $select = $this->select()
            ->where('hash = ?', $hash);

        $agentRow = $this->fetchRow($select);

        if ($agentRow) {
            $result = $agentRow->toArray();
        } else {
            $result = array(
                'user_agent' => $userAgent,
                'hash'       => $hash,
                'bot_filter' => $this->filterBots($userAgent),
                'created_at' => new Zend_Db_Expr('NOW(3)'),
            );
            $result['id'] = $this->insert($result);
        }

        return $result;
    }

    protected function filterBots($userAgent)
    {
        $engines = array(
            'googlebot',
            'aport',
            'msnbot',
            'rambler',
            'yahoo',
            'abachobot',
            'accoona',
            'acoirobot',
            'aspseek',
            'croccrawler',
            'dumbot',
            'fast-webcrawler',
            'geonabot',
            'gigabot',
            'lycos',
            'msrbot',
            'scooter',
            'altavista',
            'webalta',
            'idbot',
            'estyle',
            'mail.ru',
            'scrubby',
            'yandex',
            'yadirectbot',
            'ia_archiver',
            'antabot',
            'baiduspider',
            'eltaindexer',
            'gsa-crawler',
            'mihalismbot',
            'ahrefsbot',
            'ezooms',
            'bingbot',
            'panscient',
            'solomono',
            'mj12bot',
            'exabot',
            'sistrix',
            'unisterbot',
            'turnitinbot',
            'wbsearchbot',
            'larbin',
            'npbot',
            'infohelfer',
            'nutch',
            'careerbot',
            'seznam',
            'mlbot',
            'search.goo',
            'semager',
            'pixray',
            'findlinks',
            'beetlebot',
            'adsbot',
            'job-bot',
            'spider',
            'crawler',
            'java/1.6.0_04',
            'java/1.4.1_04',
            'java/1.6.0_31',
            'java/1.6.0_24',
            'java/1.6.0_29',
            'java/1.7.0_05',
            'java/1.6.0_13',
            'java/1.6.0_23',
            'java/1.6.0_30',
            'java/1.7.0_07',
            'java/1.7.0_02',
            'java/1.6.0_27',
            'java/1.6.0_26',
            'seokicks',
        );

        $result = self::FILTER_BROWSER;
        foreach ($engines as $engine) {
            if (stristr($userAgent, $engine)) {
                $result = self::FILTER_BOT;
                break;
            }
        }

        return $result;
    }
}
