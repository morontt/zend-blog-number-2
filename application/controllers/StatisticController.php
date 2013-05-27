<?php

class StatisticController extends Zend_Controller_Action
{

    public function init()
    {
        $this->view->noHomePage = true;
    }

    public function indexAction()
    {
        $trackingTable = new Application_Model_Proxy_Tracking();
        $topicTable    = new Application_Model_Posts();

        $this->view->trackingData = $trackingTable->getTrackingData(Application_Model_Tracking::INTERVAL_DAY);
        $this->view->weekArticle  = $trackingTable->getTrackingDataByArticle();
        $this->view->allArticle   = $topicTable->getStatisticData();

        $this->view->browsertitle    = 'Статистика';
        $this->view->metaDescription = 'Статистика посещений и комментариев.';
    }

}

