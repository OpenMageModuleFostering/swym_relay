<?php
class Swym_Notepad_Model_System_Wishlist extends Mage_Core_Model_Config_Data
{
   
    protected function _beforeSave()
    {
		$config = new Mage_Core_Model_Config();
        if ($this->getValue()==1) {
			$config->saveConfig('wishlist/general/active', 0, $scope='default', $scopeId=0);
        }
		else
		{
			$config->saveConfig('wishlist/general/active', 1, $scope='default', $scopeId=0);
		}
    }
}