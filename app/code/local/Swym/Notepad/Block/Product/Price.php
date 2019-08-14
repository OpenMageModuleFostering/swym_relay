<?php 
class Swym_Notepad_Block_Product_Price extends Mage_Catalog_Block_Product_Price
{
	 protected function _toHtml()
    {
        if (!$this->getProduct() || $this->getProduct()->getCanShowPrice() === false) {
            return '';
        }
		$image=Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'catalog/product' .$this->getProduct()->getImage();
		$forwishlistHtml='<div ><input type="hidden" id="wishlist-content-price-'.$this->getProduct()->getId().'" value="'.$this->getProduct()->getFinalPrice().'" />
		<input type="hidden" class="wishlist-content-id" id="wishlist-content-id-'.$this->getProduct()->getId().'" value="'.$this->getProduct()->getId().'"/>
		<input type="hidden" id="wishlist-content-name-'.$this->getProduct()->getId().'" value="'.$this->getProduct()->getName().'"/>
		<input type="hidden" id="wishlist-content-url-'.$this->getProduct()->getId().'" value="'.$this->getProduct()->getProductUrl().'"/>
		<input type="hidden" id="wishlist-content-image-'.$this->getProduct()->getId().'" value="'.$image=Mage::helper('notepad')->getImageUrl($this->getProduct()).'"/>
		</div>';
        return parent::_toHtml().$forwishlistHtml;
    }
}