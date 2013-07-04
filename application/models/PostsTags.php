<?php

class Application_Model_PostsTags extends Zend_Db_Table_Abstract
{
    protected $_name = 'relation_topictag';

    protected $_referenceMap
        = array(
            'Post' => array(
                'columns'       => array('post_id'),
                'refTableClass' => 'Application_Model_Posts',
                'refColumns'    => array('id'),
            ),
            'Tag'  => array(
                'columns'       => array('tag_id'),
                'refTableClass' => 'Application_Model_Tags',
                'refColumns'    => array('id'),
            )
        );

    public function addRelation($tagString, $topicId)
    {
        $tags = new Application_Model_Tags();

        $func = function ($value) {
            return mb_strtolower(trim($value), 'UTF-8');
        };

        $tagsArray = array_unique(array_map($func, explode(',', $tagString)));

        $data = array();
        foreach ($tagsArray as $item) {

            $value = trim($item);
            if ($value) {
                $value = mb_strtolower($value, 'UTF-8');

                $tag = $tags->getTagByName($value);
                if ($tag) {
                    $data[] = $tag->id;
                } else {
                    $newId = $tags->createNewTag($value);
                    $data[] = $newId;
                }
            }
        }

        foreach ($data as $value) {
            $row = array(
                'post_id' => $topicId,
                'tag_id'  => $value,
            );

            $this->insert($row);
        }
    }

    public function deleteRelation($topicId)
    {
        $this->delete('post_id = ' . $topicId);
    }

    public function getStringTags($topicId)
    {
        $select = $this->select()
            ->setIntegrityCheck(false)
            ->from(array('pt' => $this->_name), array())
            ->joinInner(array('t' => 'tags'), 'pt.tag_id = t.id', array('t.name'))
            ->where('pt.post_id = ?', $topicId)
            ->order('t.name');

        $data = $this->fetchAll($select)->toArray();
        if (count($data)) {
            $temp = array();
            foreach ($data as $item) {
                $temp[] = $item['name'];
            }
            $result = implode(', ', $temp);
        } else {
            $result = '';
        }

        return $result;
    }

}

