<?php

class Application_Model_Category extends Zend_Db_Table_Abstract
{

    protected $_name = 'category';
    protected $_categoryArray;

    static function cmp_category($a, $b)
    {
        return strcmp($a['name'], $b['name']);
    }

    public function getNotEmptyCategory()
    {
        $select = $this->select()
            ->setIntegrityCheck(false)
            ->from(array('cat' => $this->_name), array('cat.id', 'cat.parent_id', 'cat.name', 'cat.url'))
            ->join(array('p' => 'posts'), 'p.category_id = cat.id', array());

        if (!Zend_Auth::getInstance()->hasIdentity()) {
            $select->where('p.hide = 0');
        }

        $queryResult = $this->fetchAll($select)
            ->toArray();

        $selectAll = $this->select()
            ->from(array('cat' => $this->_name), array('cat.id', 'cat.parent_id', 'cat.name', 'cat.url'));

        $allCategory = $this->fetchAll($selectAll)
            ->toArray();

        $tempAllArray = array();
        foreach ($allCategory as $category) {
            $tempAllArray[$category['id']] = $category;
        }

        $tempArray = array();
        foreach ($queryResult as $category) {
            $tempArray[$category['id']] = $category;
        }

        $this->_categoryArray = $tempArray;

        do {
            $insert = false;
            foreach ($this->_categoryArray as $category) {
                if (!is_null($category['parent_id']) && !isset($tempArray[$category['parent_id']])) {
                    $tempArray[$category['parent_id']] = $tempAllArray[$category['parent_id']];
                    $insert = true;
                }
            }
            $this->_categoryArray = $tempArray;
        } while ($insert);

        usort($tempArray, array("Application_Model_Category", "cmp_category"));

        return $tempArray;
    }

    public function getCategoryIdArray()
    {
        $select = $this->select()
            ->from($this->_name, array('id', 'parent_id', 'url'));

        return $this->fetchAll($select)
                ->toArray();
    }

    public function getCategoryByUrl($url)
    {
        $select = $this->select()
            ->where('url = ?', $url);

        return $this->fetchRow($select);
    }

    public function getCategoryList()
    {
        $data = $this->fetchAll($this->select()->order('name'));

        $result = array();
        foreach ($data as $item) {
            $result[$item->id] = $item->name;
        }

        return $result;
    }

    public function getAdminCategoryList()
    {
        $select = $this->select()
            ->setIntegrityCheck(false)
            ->from(array('c1' => $this->_name), array(
                'c1.id',
                'c1.name',
                'c1.url',
                'post_count' => new Zend_Db_Expr('COUNT( p.id )'),
            ))
            ->joinLeft(array('c2' => $this->_name), 'c2.id = c1.parent_id', array(
                'parent_name' => 'c2.name',
            ))
            ->joinLeft(array('p' => 'posts'), 'c1.id = p.category_id', array())
            ->group('c1.id')
            ->order('c1.name');

        return Zend_Paginator::factory($select);
    }

    public function createCategory($formData)
    {
        // TODO - uniq url
        $url = Zml_Transform::ruTransform($formData['name']);

        $data = array(
            'name' => $formData['name'],
            'url'  => $url,
        );

        if ($formData['parent_id']) {
            $data['parent_id'] = $formData['parent_id'];
        }

        return $this->insert($data);
    }

    public function editCategory($formData, $id)
    {
        // TODO - uniq url
        $url = Zml_Transform::ruTransform($formData['name']);

        $data = array(
            'name' => $formData['name'],
            'url'  => $url,
        );

        if ($formData['parent_id']) {
            $data['parent_id'] = $formData['parent_id'];
        } else {
            $data['parent_id'] = null;
        }

        return $this->update($data, 'id = ' . $id);
    }

    public function getCategoryById($categoryId)
    {
        $select = $this->select()
            ->where('id = ?', $categoryId);

        return $this->fetchRow($select);
    }

}

