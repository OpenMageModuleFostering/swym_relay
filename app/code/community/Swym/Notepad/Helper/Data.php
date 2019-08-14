<?php
class Swym_Notepad_Helper_Data extends Mage_Core_Helper_Abstract
{
   /**
   * Config paths for using throughout the code
   */
   const XML_PATH_ACCOUNT = 'notepad/general/swym_js';
	const XML_PATH_RETAILER_ID = 'notepad/general/retailer_id';
	const XML_PATH_STATUS = 'notepad/general/status';
    /**
     * Whether GA is ready to use
     *
     * @param mixed $store
     * @return bool
     */
    public function getHtmlCode($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_ACCOUNT, $store);
    }

	public function getImageUrl($product)
	{
		$product=Mage::getModel('catalog/product')->load($product->getId());
		if($product->getImage()!='no_selection')
		{

			$image=Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'catalog/product' .$product->getImage();
		}
		else
		{
			if(Mage::getStoreConfig("catalog/placeholder/image_placeholder")=='')
			{
				$image=Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN).'frontend/base/default/images/catalog/product/placeholder/image.jpg';
			}
			else
			{
				$image=Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'placeholder/' .Mage::getStoreConfig("catalog/placeholder/small_image_placeholder");
			}
		}
		return $image;
	}

  public function getDisplayPrice($product) {
		if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE){

      // Refer - http://stackoverflow.com/a/15225306/1304559*
      // Alternate - http://www.seanbreeden.com/getting-minimum-and-maximum-bundled-product-price-in-magento/

			$_product_id            = $product->getId();

      // highest possible price for this bundle product
      // $return_type            = 'max'; // because I used this in a helper method

      // lowest possible price for this bundle product
      $return_type         = 'min';

      $model_catalog_product  = Mage::getModel('catalog/product'); // getting product model
      $_product               = $model_catalog_product->load( $_product_id );

      $TypeInstance           = $_product->getTypeInstance(true);
      $OptionIds              = $TypeInstance->getOptionsIds($_product);
      $Selections             = $TypeInstance->getSelectionsCollection($OptionIds, $_product );
      $Options                = $TypeInstance->getOptionsByIds($OptionIds, $_product);
      $bundleOptions          = $Options->appendSelections($Selections, true);

      $minmax_pricevalue  = 0; // to sum them up from 0

      foreach ($bundleOptions as $bundleOption) {
          if ($bundleOption->getSelections()) {


              $bundleSelections       = $bundleOption->getSelections();

              $pricevalues_array  = array();
              foreach ($bundleSelections as $bundleSelection) {

                  $pricevalues_array[] = $bundleSelection->getPrice();

              }
                  if ( $return_type == 'max' ) {
                  rsort($pricevalues_array); // high to low
                  } else {
                  sort($pricevalues_array);   // low to high
                  }

              // sum up the highest possible or lowest possible price
              $minmax_pricevalue += $pricevalues_array[0];


          }
      }
      return $minmax_pricevalue;

		}
		elseif ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE){
			return $product->getFinalPrice();
		} elseif($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_GROUPED) {
      // Refer - http://stackoverflow.com/a/4679517/1304559
      $aProductIds = $product->getTypeInstance()->getChildrenIds($product->getId());
      $model_catalog_product  = Mage::getModel('catalog/product'); // getting product model

      $prices = array();
      foreach ($aProductIds as $ids) {
        foreach ($ids as $id) {
            $aProduct = $model_catalog_product->load($id);
            $prices[] = $aProduct->getPriceModel()->getPrice($aProduct);
        }
      }

      krsort($prices);
      return array_shift($prices);
    }
	}

	public function canShowNotepad()
	{


		$isEnabled=Mage::getStoreConfig('notepad/general/status',Mage::app()->getStore()->getId());
		$canShow=Mage::getStoreConfig('notepad/general/show',Mage::app()->getStore()->getId());
		if($isEnabled&&$canShow)
		{
			return true;
		}
		return false;

	}

	public function getSwymValue($key)
	{
		if($data=Mage::getSingleton('core/session')->getData('swym_details'))
		{
			if(isset($data[$key]))
				return $data[$key];
		}
		return false;
	}
	public function getSwymData()
	{
		if($data=Mage::getSingleton('core/session')->getData('swym_details'))
		{
			return $data;
		}
		return false;
	}
}
