<?php
class Kitnekaa_Shoppinglist_Model_Resource_Shoppinglistfiles_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('shoppinglist/shoppinglistfiles');
    }
}