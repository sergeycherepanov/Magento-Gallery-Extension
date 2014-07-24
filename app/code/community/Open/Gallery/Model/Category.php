<?php

class Open_Gallery_Model_Category
    extends Mage_Core_Model_Abstract
{
    protected $_depth    = 0;
    protected $_children = array();

    /**
     * @param int $value
     * @return $this
     */
    public function setDepth($value)
    {
        $this->_depth = intval($value);

        return $this;
    }

    /**
     * @return int
     */
    public function getDepth()
    {
        return $this->_depth;
    }

    protected function _construct()
    {
        $this->_init('open_gallery/category');
    }

    /**
     * @param array $output
     * @return bool
     */
    public function validate(&$output = array())
    {
        /** @var $helper Open_Gallery_Helper_Data */
        $helper    = Mage::helper('open_gallery');
        $result    = true;
        $validator = new Zend_Validate_NotEmpty();
        if (!$validator->isValid($this->getData('title'))) {
            $output[] = $helper->__("Category title can't be empty.");
            $result   = false;
        }

        return $result;
    }

    /**
     * @return $this
     * @throws Open_Gallery_Exception
     */
    protected function _beforeSave()
    {
        $validationOutput = array();
        if (!$this->validate($validationOutput)) {
            /** @var $helper Open_Gallery_Helper_Data */
            $helper    = Mage::helper('open_gallery');
            $exception = new Open_Gallery_Exception($helper->__('Invalid category data.'));
            foreach ($validationOutput as $message) {
                $exception->addMessage($message);
            }
            throw $exception;
        }

        return parent::_beforeSave();
    }

    /**
     * @return $this
     */
    protected function _afterDelete()
    {
        try {
            $this->deleteThumbnail();
        } catch (Open_Gallery_Exception $e) {
            Mage::logException($e);
        }

        return parent::_afterDelete();
    }

    /**
     * @param Open_Gallery_Model_Category $category
     * @return $this
     */
    public function assignChild(Open_Gallery_Model_Category &$category)
    {
        $this->_children[$category->getId()] = $category;

        return $this;
    }

    /**
     * @return array
     */
    public function getChildren()
    {
        return $this->_children;
    }

    /**
     * @return $this
     */
    public function deleteThumbnail()
    {
        return $this->_deleteFile('thumbnail');
    }

    /**
     * @param string $fieldName
     * @return $this
     * @throws Open_Gallery_Exception
     */
    protected function _deleteFile($fieldName)
    {
        $path = Mage::getBaseDir('media') . DS . $this->getData($fieldName);
        if (is_file($path) && is_writeable($path)) {
            unlink($path);
            $this->setData($fieldName, '');
        } else {
            /** @var $helper Open_Gallery_Helper_Data */
            $helper = Mage::helper('open_gallery');
            throw new Open_Gallery_Exception($helper->__("Can't delete file '%s'", $path));
        }
        return $this;
    }
}