<?php
class Swym_Notepad_Block_Notepad extends Mage_Core_Block_Template{

	public function __construct()
	{

	}

	public function isNotepadEnabled()
	{
		$controller=$this->getRequest()->getControllerName();
		$action=$this->getRequest()->getActionName();
		$module=$this->getRequest()->getModuleName();
		/*
		if($module=='checkout'&&$controller=='onepage')
		{
			return false;
		}
		if($module=='customer'&&$controller=='account')
		{
			return false;
		}
		*/
		$isEnabled=Mage::getStoreConfig('notepad/general/status', Mage::app()->getStore()->getId());
		return $isEnabled;
	}

   public function canShowNotepad()
   {
      if( $this->isNotepadEnabled() ) {
         $canShow= Mage::getStoreConfig('notepad/general/show', Mage::app()->getStore()->getId());
         return $canShow;
      } else {
         return false;
      }
   }

	public function getRetailerId()
	{
		$retailerId=Mage::getStoreConfig('notepad/general/retailer_id',Mage::app()->getStore()->getId());
		return $retailerId;
	}

   public function getNotepadTitle()
   {
      $title=Mage::getStoreConfig('notepad/relay_ui/title',Mage::app()->getStore()->getId());
      if($title=='') {
         return 'Notepad';
      } else {
         return $title;
      }
   }
   public function getWishlistTitle()
	{
        $wishlistDisplay = Mage::getStoreConfig('notepad/wishlist/wishlistdisplay', Mage::app()->getStore()->getId());
        $wishlistIcon    = Mage::getStoreConfig('notepad/wishlist/wishlisticon', Mage::app()->getStore()->getId());
        $wishlistTitle   = trim(Mage::getStoreConfig('notepad/wishlist/wishlisttitle', Mage::app()->getStore()->getId()));

        if($wishlistDisplay == 'icon' && $wishlistIcon != '') {
            return '<img src="'.Mage::getBaseUrl('media') . 'notepad/' . $wishlistIcon.'" />';
        } else {
            if($wishlistTitle == '')
                return 'Add To my Favorites';
            return $wishlistTitle;
        }
	}
	public function getProductView()
	{
		$controller=$this->getRequest()->getControllerName();;
		$action=$this->getRequest()->getActionName();
		if($controller=='product'&&$action=='view')
		{
			if($id=$this->getRequest()->getParam('id'))
			{
				$product=Mage::getModel('catalog/product')->load($id);
				$image=Mage::helper('notepad')->getImageUrl($product);
				if($product->getTypeId()=='simple')
				{
					$price=$product->getFinalPrice();
					$oprice=$product->getPrice();
				}
				else
				{
					$oprice=$price=Mage::helper('notepad')->getDisplayPrice($product);
				}

				$data=array(
				'du'=>$product->getProductUrl(),
				'dt'=>$product->getName(),
				'pr'=>$price,
				'op'=>$oprice,
				'epi'=>$product->getId(),
				'iu'=>$image,
				);
				return json_encode($data);
			}
		}

		return false;

	}
	public function getCategoryView()
	{

		$controller=$this->getRequest()->getControllerName();;
		$action=$this->getRequest()->getActionName();
		if($controller=='category'&&$action=='view')
		{

			if($id=$this->getRequest()->getParam('id'))
			{

				$category=Mage::getModel('catalog/category')->load($id);
				if($category->getImageUrl())
				{
					$data=array(
						'du'=>$category->getUrl($category),
						'dt'=>$category->getName(),
						'et'=>2,
						'iu'=>$category->getImageUrl(),
					);
				}
				else
				{
					$data=array(
						'du'=>$category->getUrl($category),
						'dt'=>$category->getName(),
						'et'=>2,


					);
				}
				return json_encode($data);
			}
		}

		return false;

	}
	public function getAddToCart()
	{


		if($data=Mage::getSingleton('core/session')->getData('notepadcart'))
		{
			Mage::getSingleton('core/session')->unsNotepadcart();
			Mage::getModel('core/cookie')->delete('notepadcart');
			return $data;
		}

		return false;

	}
	public function getReportPurchase()
	{


		if($data=Mage::getSingleton('core/session')->getData('reportpurchase'))
		{
			Mage::getSingleton('core/session')->unsReportpurchase();
			$order=Mage::getSingleton('core/session')->getData('swym_order');

			Mage::getSingleton('core/session')->unsSwym_order();
			Mage::log('order : '.$order.' '.print_r($data,true),null,'swym_purchase.log',true);
			return $data;
		}

		return false;

	}
	public function getPriceUpdateData()
	{
		if($data=Mage::getSingleton('admin/session')->getData('product_price_update'))
		{
			Mage::getSingleton('admin/session')->unsProduct_price_update();
			return $data;
		}

		return false;
	}
	public function getPageView()
	{
		$controller=$this->getRequest()->getControllerName();
		$action=$this->getRequest()->getActionName();
		$moduleName=$this->getRequest()->getModuleName();
		if($controller=='product'&&$action=='view')
		{
			$data=json_encode(array('pageType'=>1));
		}
		elseif($controller=='checkout'&&$action=='cart')
		{
			$data=json_encode(array('pageType'=>2));
		}
		elseif($controller=='category'&&$action=='view')
		{
			$data=json_encode(array('pageType'=>3));
		}
		elseif($moduleName=='checkout'&&$controller=='onepage')
		{
			$data=json_encode(array('pageType'=>5));
		}
		else
		{
			$data=json_encode(array('pageType'=>99));
		}
		return $data;

	}
	public function getRemovedItems()
	{
		if($data=Mage::getSingleton('core/session')->getData('remove_cart_item'))
		{
			Mage::getSingleton('core/session')->unsRemove_cart_item();
			return $data;
		}
		return false;

	}
	public function getAddToWishlist()
	{
		$controller=$this->getRequest()->getControllerName();;
		$action=$this->getRequest()->getActionName();
		if($controller=='product'&&$action=='view' && strtolower($this->getRequest()->getParam('action'))=='addtofavorites')
		{
			if($id=$this->getRequest()->getParam('id'))
			{
				$product=Mage::getModel('catalog/product')->load($id);
				$image=Mage::helper('notepad')->getImageUrl($product);
				if($product->getTypeId()=='simple')
				{
					$price=$product->getFinalPrice();
					$oprice=$product->getPrice();
				}
				else
				{
					$oprice=$price=Mage::helper('notepad')->getDisplayPrice($product);
				}

				$data=array(
				'du'=>$product->getProductUrl(),
				'dt'=>$product->getName(),
				'pr'=>$price,
				'op'=>$oprice,
				'qty'=>1,
				'epi'=>$product->getId(),
				'iu'=>$image,
				);
				return json_encode($data);
			}
		}

		return false;

	}

	public function getAddCoupon()
	{


		if($data=Mage::getSingleton('core/session')->getData('coupon_code'))
		{
			Mage::getSingleton('core/session')->unsCoupon_code();
			return $data;
		}

		return false;

	}
	/* 	protected function _toHtml()
	  {

		return $html;
	  } */

	public function isMobile()
	{
		$useragent=$_SERVER['HTTP_USER_AGENT'];
		$mobi = FALSE;
		if(preg_match('/android|ipad|avantgo|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|e\-|e\/|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|xda(\-|2|g)|yas\-|your|zeto|zte\-/i',substr($useragent,0,4))){
		$mobi = TRUE;
		}
		return $mobi;
	}

	public function getLoggedInEmail()
	{
		if($email=Mage::getSingleton('core/session')->getData('swym_customer_email'))
		{
			Mage::getSingleton('core/session')->unsSwym_customer_email();
			return $email;
		}

		return false;
	}

}
