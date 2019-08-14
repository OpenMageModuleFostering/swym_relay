<?php
class Swym_Notepad_Block_Product_Bundleprice extends Mage_Catalog_Block_Product_Price
{

    public function isRatesGraterThenZero()
    {
        $_request = Mage::getSingleton('tax/calculation')->getRateRequest(false, false, false);
        $_request->setProductClassId($this->getProduct()->getTaxClassId());
        $defaultTax = Mage::getSingleton('tax/calculation')->getRate($_request);

        $_request = Mage::getSingleton('tax/calculation')->getRateRequest();
        $_request->setProductClassId($this->getProduct()->getTaxClassId());
        $currentTax = Mage::getSingleton('tax/calculation')->getRate($_request);

        return (floatval($defaultTax) > 0 || floatval($currentTax) > 0);
    }

    /**
     * Check if we have display prices including and excluding tax
     * With corrections for Dynamic prices
     *
     * @return bool
     */
    public function displayBothPrices()
    {
        $product = $this->getProduct();
        if ($product->getPriceType() == Mage_Bundle_Model_Product_Price::PRICE_TYPE_DYNAMIC &&
            $product->getPriceModel()->getIsPricesCalculatedByIndex() !== false) {
            return false;
        }
        return $this->helper('tax')->displayBothPrices();
    }

    /**
     * Convert block to html string
     *
     * @return string
     */
    protected function _toHtml()
    {
        $product = $this->getProduct();
        if($product->getTypeId()=='simple')
    		{
    			$price=$product->getFinalPrice();
    			$oprice=$product->getPrice();
    		}
    		else
    		{
    			$oprice=$price=Mage::helper('notepad')->getDisplayPrice($product);
    		}
		$forwishlistHtml='<div ><input type="hidden" id="wishlist-content-price-'.$product->getId().'" value="'.$price.'" />
    <input type="hidden" id="wishlist-content-oprice-'.$product->getId().'" value="'.$oprice.'"/>
		<input type="hidden" class="wishlist-content-id" id="wishlist-content-id-'.$product->getId().'" value="'.$product->getId().'"/>
		<input type="hidden" id="wishlist-content-name-'.$product->getId().'" value="'.$product->getName().'"/>
		<input type="hidden" id="wishlist-content-url-'.$product->getId().'" value="'.$product->getProductUrl().'"/>
		<input type="hidden" id="wishlist-content-image-'.$product->getId().'" value="'.$image=Mage::helper('notepad')->getImageUrl($product).'"/>
		</div>';
        if ($this->getMAPTemplate() && Mage::helper('catalog')->canApplyMsrp($product)
                && $product->getPriceType() != Mage_Bundle_Model_Product_Price::PRICE_TYPE_DYNAMIC
        ) {
            $hiddenPriceHtml = parent::_toHtml();
            if (Mage::helper('catalog')->isShowPriceOnGesture($product)) {
                $this->setWithoutPrice(true);
            }
            $realPriceHtml = parent::_toHtml();
            $this->unsWithoutPrice();
            $addToCartUrl  = $this->getLayout()->getBlock('product.info.bundle')->getAddToCartUrl($product);
            $product->setAddToCartUrl($addToCartUrl);
            $html = $this->getLayout()
                ->createBlock('catalog/product_price')
                ->setTemplate($this->getMAPTemplate())
                ->setRealPriceHtml($hiddenPriceHtml)
                ->setPriceElementIdPrefix('bundle-price-')
                ->setIdSuffix($this->getIdSuffix())
                ->setProduct($product)
                ->toHtml();

            return $realPriceHtml . $html.$forwishlistHtml;
        }

        return parent::_toHtml().$forwishlistHtml;
    }
}
