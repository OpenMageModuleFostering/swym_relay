<?php
class Swym_Notepad_Block_Product_Price extends Mage_Catalog_Block_Product_Price
{
	 protected function _toHtml()
    {
			$product=$this->getProduct();
      if (!$product || $product->getCanShowPrice() === false) {
          return '';
      }
		if($product->getTypeId()=='simple')
		{
			$price=$product->getFinalPrice();
			$oprice=$product->getPrice();
		}
		else
		{
			$oprice=$price=Mage::helper('notepad')->getDisplayPrice($product);
		}
		$image=Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'catalog/product' .$product->getImage();
		$forwishlistHtml='<div ><input type="hidden" id="wishlist-content-price-'.$product->getId().'" value="'.$price.'" />
		<input type="hidden" id="wishlist-content-oprice-'.$product->getId().'" value="'.$oprice.'"/>
		<input type="hidden" class="wishlist-content-id" id="wishlist-content-id-'.$product->getId().'" value="'.$product->getId().'"/>
		<input type="hidden" id="wishlist-content-name-'.$product->getId().'" value="'.$product->getName().'"/>
		<input type="hidden" id="wishlist-content-url-'.$product->getId().'" value="'.$product->getProductUrl().'"/>
		<input type="hidden" id="wishlist-content-image-'.$product->getId().'" value="'.$image=Mage::helper('notepad')->getImageUrl($product).'"/>
		</div>';
        return parent::_toHtml().$forwishlistHtml;
    }
}
