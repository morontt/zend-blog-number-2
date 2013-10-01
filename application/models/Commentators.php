<?php

class Application_Model_Commentators extends Zend_Db_Table_Abstract
{
    const HASH_SALT = 'be94aaec6ac6c931';

    protected $_name = 'commentators';

    /**
     * get commentator id
     *
     * @param string $name
     * @param string $mail
     * @param string $website
     * @param string|null $emailHash
     * @param integer|null $disqusId
     * @return integer
     */
    public function getCommentatorId($name, $mail, $website, $emailHash = null, $disqusId = null)
    {
        $select = $this->select()
            ->where('name = ?', $name);

        if ($mail && trim($mail)) {
            $select->where('mail = ?', $mail);
        } else {
            $select->where('mail IS NULL');
        }

        if ($website && trim($website)) {
            $select->where('website = ?', $website);
        } else {
            $select->where('website IS NULL');
        }

        $commentator = $this->fetchRow($select);
        if ($commentator) {
            $result = $commentator->id;
        } else {
            $data = array(
                'name' => $name,
            );
            if ($mail && trim($mail)) {
                $data['mail'] = $mail;
            }
            if ($website && trim($website)) {
                $data['website'] = $website;
            }
            if ($disqusId) {
                $data['disqus_id'] = $disqusId;
            }

            if ($emailHash) {
                $data['email_hash'] = $emailHash;
            } else {
                $data['email_hash'] = self::getAvatarHash($name, $mail, $website);
            }

            $result = $this->insert($data);
        }

        return $result;
    }

    /**
     * get commentator by hash
     *
     * @param string $hash
     * @return Zend_Db_Row|null
     */
    public function getByHash($hash)
    {
        $select = $this->select()
            ->where("MD5(CONCAT('" . self::HASH_SALT . "', id)) = ?", $hash);

        return $this->fetchRow($select);
    }

    /**
     *
     * @param string $name
     * @param string $mail
     * @param string $website
     * @return string
     */
    public static function getAvatarHash($name, $mail, $website)
    {
        if ($mail) {
            $hash = md5(strtolower(trim($mail)));
        } else {
            $hash = md5(strtolower(trim($name)));
            if ($website) {
                $hash = md5($hash . strtolower(trim($website)));
            }
        }

        return $hash;
    }

    /**
     * get commentators array for statistic
     *
     * @param int $limit
     * @return array
     */
    public function getCommentatorsForStatistic($limit)
    {
        $select = $this->select()
            ->from(array('ctt' => $this->_name), array(
                'commentator_id' => 'id',
                'name',
                'mail',
                'website',
                'count'     => new Zend_Db_Expr('COUNT( c.id )'),
                'last_time' => new Zend_Db_Expr('MAX( c.time_created )'),
            ))
            ->joinInner(array('c' => 'comments'), 'ctt.id = c.commentator_id', array())
            ->where('c.deleted = 0')
            ->group('ctt.id')
            ->order('count DESC')
            ->order('last_time DESC')
            ->limit($limit);

        return $this->fetchAll($select)->toArray();
    }

    /**
     * @param array $author
     * @return integer
     */
    public function getDisqusAuthor(array $author)
    {
        $select = $this->select()
            ->where('disqus_id = ?', $author['id']);

        $commentator = $this->fetchRow($select);
        if ($commentator) {
            $result = $commentator->id;
        } else {
            $result = $this->getCommentatorId(
                $author['name'],
                null,
                $author['website'],
                $author['emailHash'],
                $author['id']
            );
        }

        return $result;
    }
}
