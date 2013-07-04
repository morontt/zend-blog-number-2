<?php

class Application_Model_Tags extends Zend_Db_Table_Abstract
{
    protected $_name = 'tags';

    protected $_dependentTables = array('Application_Model_PostsTags');

    /**
     * @return array
     */
    public function getTagCloudData()
    {
        $select = $this->select()
            ->setIntegrityCheck(false)
            ->from(
                array('t' => $this->_name),
                array(
                    't.id',
                    't.name',
                    't.url',
                )
            )
            ->joinInner(array('rtt' => 'relation_topictag'), 't.id = rtt.tag_id', array())
            ->joinInner(
                array('p' => 'posts'), 'p.id = rtt.post_id', array(
                    'count' => new Zend_Db_Expr('COUNT( p.id )'),
                )
            )
            ->where('p.hide = 0')
            ->order(new Zend_Db_Expr('RAND()'))
            ->group('t.id');

        $tags = $this->fetchAll($select)->toArray();

        return $tags;
    }

    /**
     * @param $url
     *
     * @return null|Zend_Db_Table_Row_Abstract
     */
    public function getTagByUrl($url)
    {
        $select = $this->select()
            ->where('url = ?', $url);

        return $this->fetchRow($select);
    }

    /**
     * @param $name
     *
     * @return null|Zend_Db_Table_Row_Abstract
     */
    public function getTagByName($name)
    {
        $select = $this->select()
            ->where('name = ?', $name);

        return $this->fetchRow($select);
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    public function createNewTag($name)
    {
        // TODO - uniq url
        $url = Zml_Transform::ruTransform($name);

        $data = array(
            'name' => $name,
            'url'  => $url,
        );

        return $this->insert($data);
    }

    /**
     * @return array
     */
    public function getAvailableTags()
    {
        $select = $this->select()
            ->from(
                array('t' => $this->_name),
                array('t.name')
            );

        $tags = $this->fetchAll($select);

        $result = array();
        foreach ($tags as $item) {
            $result[] = $item->name;
        }

        return $result;
    }
}

