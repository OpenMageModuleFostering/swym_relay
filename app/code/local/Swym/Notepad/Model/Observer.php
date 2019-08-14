<?php
class Swym_Notepad_Model_Observer
{

	public function addToCartAfter($observer)
	{
		 /* print_r(Mage::app()->getRequest()->getParams());die;  */

		$product=$observer->getEvent()->getProduct();
		$qty=Mage::app()->getRequest()->getParam('qty');
		$variant=array();
		$finalPrice=0;
		$url='?';

		if($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_GROUPED)
		{
			$finalPrice=0;
			$oprice=0;
			$options=Mage::app()->getRequest()->getParam('super_group');
			foreach($options as $key=>$val)
			{
				$pro=Mage::getModel('catalog/product')->load($key);
				$finalPrice+=$pro->getFinalPrice()*$val;
				$oprice+=$pro->getPrice()*$val;
				$url.='&super_group['.$key.']='.$val;
			}

		}
		elseif($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE)
		{
			 $oprice=$finalPrice=$product->getFinalPrice();
			$options=Mage::app()->getRequest()->getParam('bundle_option');
			foreach($options as $key=>$val)
			{
				$url.='&bundle_option['.$key.']='.$val;
			}
			$bundle_option_qty=Mage::app()->getRequest()->getParam('bundle_option_qty');
			foreach($bundle_option_qty as $key=>$val)
			{
				$url.='&bundle_option_qty['.$key.']='.$val;
			}
		}
		elseif($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE)
		{
			$produto_cor_options = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);
			$configOptions=array();
			foreach($produto_cor_options as $options){
				$atributo_cor = $options['values'];
				foreach ($atributo_cor as $options2){
					$configOptions[$options['attribute_id']][$options2['value_index']]=array(
																					'attribute_label'=>$options['frontend_label'],
																					'option_label'=>$options2['label'],
																					);
				}
			}
			$super_attributes=Mage::app()->getRequest()->getParam('super_attribute');

			foreach($super_attributes as $key=>$val)
			{
				$url.='&super_attribute['.$key.']='.$val;
				$variant[$key]=array(
									'option_id'=>$val,
									'type'=>$configOptions[$key][$val]['attribute_label'],
									'label'=>$configOptions[$key][$val]['option_label']);

			}
			$finalPrice=$product->getFinalPrice();
			$oprice=$product->getPrice();
		}
		else
		{
			$finalPrice=$product->getFinalPrice();
			$oprice=$product->getPrice();
		}
		$image=Mage::helper('notepad')->getImageUrl($product);
		if($allData=Mage::getSingleton('core/session')->getData('notepadcart'))
		{

		}
		else
		{
			$allData=array();
		}
		$url=Mage::getUrl('notepad/index/cart',array('product'=>$product->getId())).$url;
		$data=json_encode(array(
				'du'=>/* $product->getProductUrl() */$url,
				'dt'=>$product->getName(),
				'pr'=>$finalPrice,
				'op'=>$oprice,
				'variants'=>json_encode($variant),
				'epi'=>$product->getId(),
				'qty'=>$qty,
				'iu'=>$image,
			));
		$allData[]=$data;
		$cookie = Mage::getSingleton('core/cookie');
		$cookie->set('notepadcart','notepadcart' ,time()+86400,'/');
		Mage::getSingleton('core/session')->setData('notepadcart',$allData);

	}
	public function orderPlaceAfter($observer)
	{
		$order = $observer->getEvent()->getOrder();
		if(Mage::registry('notepad_save_observer_executed')){
					return $this; //this method has already been executed once in this request
				}
		$allData=array();
		$items_processed=array();
		foreach($order->getAllItems() as $item)
		{
			if ($item->getParentItem()) continue;
			$product=$item->getProduct();

			if($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE)
			{

				$produto_cor_options = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);
				$configOptions=array();

				foreach($produto_cor_options as $options){
					$atributo_cor = $options['values'];
					foreach ($atributo_cor as $options2){
						$configOptions[$options['attribute_id']][$options2['value_index']]=array(
																						'attribute_label'=>$options['frontend_label'],
																						'option_label'=>$options2['label'],
																						);
					}
				}
				foreach($order->getAllItems() as $item1)
				{
					if($item1->getParentItem()&&!in_array($item1->getId(),$items_processed))
					{

						if ($item1->getParentItem()->getProduct()->getId()==$product->getId())
						{

							foreach($produto_cor_options as $super_attribute)
							{
								//$url.='&super_attribute['.$key.']='.$val;
								/* $variant[$configOptions[$key][$val]['attribute_label']]=$configOptions[$key][$val]['option_label']; */

								$key=$super_attribute['attribute_id'];
								$val=Mage::getModel('catalog/product')->load($item1->getProduct()->getId())->getData($super_attribute['attribute_code']);
								$variant[$key]=array(
													'option_id'=>$val,
													'type'=>$configOptions[$key][$val]['attribute_label'],
													'label'=>$configOptions[$key][$val]['option_label']);

							}
							$items_processed[]=$item1->getId();
							break;
						}
					}
				}

				$finalPrice=$product->getFinalPrice();
				$oprice=$product->getPrice();
				$image=Mage::helper('notepad')->getImageUrl($product);
				$data=json_encode(array(
						'du'=>$product->getProductUrl(),
						'dt'=>$product->getName(),
						'pr'=>$finalPrice,
						'op'=>$oprice,
						'variants'=>json_encode($variant),
						'epi'=>$product->getId(),
						'qty'=>$item->getQtyOrdered(),
						'et'=>6,
						'iu'=>$image,
					));
				$allData[]=$data;
			}
			else
			{
				$finalPrice=$product->getFinalPrice();
				$oprice=$product->getPrice();
				$image=Mage::helper('notepad')->getImageUrl($product);
				$data=json_encode(array(
						'du'=>$product->getProductUrl(),
						'dt'=>$product->getName(),
						'pr'=>$finalPrice,
						'op'=>$oprice,
						'epi'=>$product->getId(),
						'qty'=>$item->getQtyOrdered(),
						'et'=>6,
						'iu'=>$image,
					));
				$allData[]=$data;
			}

		}
		Mage::getSingleton('core/session')->setData('reportpurchase',$allData);
		Mage::register('notepad_save_observer_executed',true);
	}
	public function removeItemFromCart($observer)
	{

		$product=$observer->getQuoteItem()->getProduct();
		if($allData=Mage::getSingleton('core/session')->getData('remove_cart_item'))
		{

		}
		else
		{
			$allData=array();
		}
		if($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE)
		{

			$produto_cor_options = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);
			$configOptions=array();

			foreach($produto_cor_options as $options){
				$atributo_cor = $options['values'];
				foreach ($atributo_cor as $options2){
					$configOptions[$options['attribute_id']][$options2['value_index']]=array(
																					'attribute_label'=>$options['frontend_label'],
																					'option_label'=>$options2['label'],
																					);
				}
			}
			$variant=array();
			foreach($produto_cor_options as $super_attribute)
			{
				$key=$super_attribute['attribute_id'];
				$val='';
				foreach($observer->getQuoteItem()->getChildren() as $item1)
					$val=Mage::getModel('catalog/product')->load($item1->getProduct()->getId())->getData($super_attribute['attribute_code']);

				$variant[$key]=array(
									'option_id'=>$val,
									'type'=>$configOptions[$key][$val]['attribute_label'],
									'label'=>$configOptions[$key][$val]['option_label']);

			}
			$finalPrice=$product->getFinalPrice();
			$oprice=$product->getPrice();
			$image=Mage::helper('notepad')->getImageUrl($product);
			$data=json_encode(array(
					'du'=>$product->getProductUrl(),
					'dt'=>$product->getName(),
					'pr'=>$finalPrice,
					'op'=>$oprice,
					'variants'=>json_encode($variant),
					'epi'=>$product->getId(),
					'et'=>7,
					'iu'=>$image,
				));
			$allData[]=$data;
		}
		else
		{
			$finalPrice=$product->getFinalPrice();
			$oprice=$product->getPrice();
			$image=Mage::helper('notepad')->getImageUrl($product);

			$data=json_encode(array(
					'dt'=>$product->getName(),
					'pr'=>$finalPrice,
					'op'=>$oprice,
					'epi'=>$product->getId(),
					'et'=>7,
					'qty'=>$qty,
					'iu'=>$image,
				));
			$allData[]=$data;
		}
		Mage::getSingleton('core/session')->setData('remove_cart_item',$allData);
	}
	public function addToWishlist($observer)
	{
		$product=$observer->getEvent()->getProduct();

		$image=Mage::helper('notepad')->getImageUrl($product);
		if($allData=Mage::getSingleton('core/session')->getData('notepad_wishlist'))
		{

		}
		else
		{
			$allData=array();
		}
		$data=array(
			'du'=>$product->getProductUrl(),
			'dt'=>$product->getName(),
			'pr'=>$product->getPrice(),
			'epi'=>$product->getId(),
			'qty'=>1,
			'iu'=>$image,
			);
		$allData[]=$data;
		$cookie = Mage::getSingleton('core/cookie');
		$cookie->set('notepad_wishlist','notepad_wishlist' ,time()+86400,'/');
		Mage::getSingleton('core/session')->setData('notepad_wishlist',$allData);

	}
	public function couponPost($observer)
	{
		$coupon=Mage::getModel('checkout/cart')->getQuote()->getCouponCode();
		if($coupon!='')
		{
			if($allData=Mage::getSingleton('core/session')->getData('coupon_code'))
			{

			}
			else
			{
				$allData=array();
			}
			$allData[]=json_encode(array('ccode'=>$coupon,'dt'=>'Coupon'));
			$cookie = Mage::getSingleton('core/cookie');
			$cookie->set('coupon_code','coupon_code' ,time()+86400,'/');
			Mage::getSingleton('core/session')->setData('coupon_code',$allData);
		}
	}
	public function productSaveBefore($observer)
	{
		$product = $observer->getProduct();
		if($allData=Mage::getSingleton('admin/session')->getData('product_price_update'))
		{

		}
		else
		{
			$allData=array();
		}
		if(Mage::getModel('catalog/product')->load($product->getId())->getPrice()!=$product->getPrice())
		{
			$allData[]=array('_id'=>$product->getId(),
							'pr'=>$product->getFinalPrice()
							);
		}
		Mage::getSingleton('admin/session')->setData('product_price_update',json_encode($allData));
	}

}
