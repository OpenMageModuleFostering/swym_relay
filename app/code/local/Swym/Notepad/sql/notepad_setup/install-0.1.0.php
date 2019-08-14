<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */ //or whatever you configured

$installer->startSetup();

$installer->setConfigData('wishlist/general/active', 0, $scope='default', $scopeId=0);

$installer->endSetup();