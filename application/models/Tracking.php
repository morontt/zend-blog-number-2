<?php

class Application_Model_Tracking extends Zend_Db_Table_Abstract
{

    protected $_name = 'tracking';

    /**
     * An interval specification
     * http://php.net/manual/en/dateinterval.construct.php
     */
    const INTERVAL_DAY   = 'P1D';
    const INTERVAL_WEEK  = 'P7D';
    const INTERVAL_MONTH = 'P1M';
    const INTERVAL_YEAR  = 'P1Y';

    const VIEW_TIME    = 3600;
    const COUNT_COLUMN = 14;

    public function checkTrackingEvent($postId, $clientIp)
    {
        $trackingAgent = new Application_Model_TrackingAgent();
        $agentArray    = $trackingAgent->getAgent();

        $now = new \DateTime('now');
        $timestamp = (int)$now->format('U') - self::VIEW_TIME;

        $select = $this->select()
            ->from($this->_name, array('id'))
            ->where('user_agent_id = ?', (int)$agentArray['id'])
            ->where('ip_addr = ?', $clientIp)
            ->where('timestamp_created > ?', $timestamp);

        if (is_null($postId)) {
            $select->where('post_id IS NULL');
        } else {
            $select->where('post_id = ?', (int)$postId);
        }

        $trackingRow = $this->fetchRow($select);

        if (!$trackingRow) {
            $this->saveEnent($postId, $agentArray, $clientIp);
        }
    }

    public function saveEnent($postId, $agentArray, $clientIp)
    {
        if ($agentArray['bot_filter'] && !is_null($postId)) {
            $postsCounts = new Application_Model_PostsCounts();
            $postsCounts->updateViewCount($postId);
        }

        $timeCreated = new \DateTime('now');
        $data = array(
            'user_agent_id'     => (int)$agentArray['id'],
            'ip_addr'           => $clientIp,
            'time_created'      => $timeCreated->format('Y-m-d H:i:s'),
            'timestamp_created' => $timeCreated->format('U'),
        );

        if (!is_null($postId)) {
            $data['post_id'] = $postId;
        }

        $this->insert($data);
    }

    public function getTrackingData($period)
    {
        $startDateTime = new \DateTime("now");
        $intervalObject = new \DateInterval($period);

        $intervalCount = self::COUNT_COLUMN;

        $dateTimeHight = $startDateTime->format('Y-m-d H:i:s');
        $formatTime = 'd M';
        if ($period == self::INTERVAL_MONTH) {
            $formatTime = 'Y-m';
        }
        if ($period == self::INTERVAL_YEAR) {
            $formatTime = 'Y';
        }
        $currentDateTime = $this->initFirstStepTime($startDateTime, $period);
        $dateTimeBottom = $currentDateTime->format('Y-m-d H:i:s');

        $count = 0;
        $resultArray = array();

        do {
            $select = $this->select()
                ->from(array('t' => $this->_name), array('count' => 'COUNT( t.id )'))
                ->joinInner(array('ta' => 'tracking_agent'), 't.user_agent_id = ta.id', array())
                ->where('t.time_created <= ?', $dateTimeHight)
                ->where('t.time_created > ?', $dateTimeBottom)
                ->where('ta.bot_filter = 1');

            $dataArray = $this->fetchAll($select)->toArray();

            if ($count % 2 == 0) {
                $label = $currentDateTime->format($formatTime);
            } else {
                $label = '';
            }

            $resultArray[] = array(
                'value' => (int)$dataArray[0]['count'],
                'label' => $label,
            );

            $dateTimeHight = $currentDateTime->format('Y-m-d H:i:s');
            $currentDateTime->sub($intervalObject);
            $dateTimeBottom = $currentDateTime->format('Y-m-d H:i:s');

            $count++;
        } while ($count < $intervalCount);

        return array_reverse($resultArray);
    }

    public function initFirstStepTime(\DateTime $dateTime, $period)
    {
        $dateTime->setTime(0, 0, 0);
        if ($period == self::INTERVAL_WEEK) {
            $dayWeek = (int)$dateTime->format('N');
            $dayWeek--;
            if ($dayWeek > 0) {
                $dateTime->modify("- {$dayWeek} day");
            }
        }
        if ($period == self::INTERVAL_MONTH) {
            $dayMonth = (int)$dateTime->format('j');
            $dayMonth--;
            if ($dayMonth > 0) {
                $dateTime->modify("- {$dayMonth} day");
            }
        }
        if ($period == self::INTERVAL_YEAR) {
            $dayYear = (int)$dateTime->format('z');
            if ($dayYear > 0) {
                $dateTime->modify("- {$dayYear} day");
            }
        }

        return $dateTime;
    }

    public function getTrackingDataByArticle()
    {
        $currentDateTime = new \DateTime("now");
        $intervalObject = new \DateInterval('P2W');

        $dateTimeHight = $currentDateTime->format('Y-m-d H:i:s');
        $currentDateTime->sub($intervalObject);
        $dateTimeBottom = $currentDateTime->format('Y-m-d H:i:s');

        $select = $this->select()
            ->setIntegrityCheck(false)
            ->from(array('t' => $this->_name), array('count' => 'COUNT( t.id )'))
            ->joinInner(array('ta' => 'tracking_agent'), 't.user_agent_id = ta.id', array())
            ->joinInner(array('p' => 'posts'), 't.post_id = p.id', array(
                'id',
                'title',
                'url',
            ))
            ->where('t.time_created <= ?', $dateTimeHight)
            ->where('t.time_created > ?', $dateTimeBottom)
            ->where('ta.bot_filter = 1')
            ->group('p.id')
            ->order('count DESC')
            ->limit(6);

        $dataArray = $this->fetchAll($select)->toArray();

        return $dataArray;
    }
}
