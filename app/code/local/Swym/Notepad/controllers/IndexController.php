<?php
class Swym_Notepad_IndexController extends Mage_Core_Controller_Front_Action{
    public function IndexAction() {

	  $this->loadLayout();
	  $this->getLayout()->getBlock("head")->setTitle($this->__("Titlename"));
	        $breadcrumbs = $this->getLayout()->getBlock("breadcrumbs");
      $breadcrumbs->addCrumb("home", array(
                "label" => $this->__("Home Page"),
                "title" => $this->__("Home Page"),
                "link"  => Mage::getBaseUrl()
		   ));

      $breadcrumbs->addCrumb("titlename", array(
                "label" => $this->__("Titlename"),
                "title" => $this->__("Titlename")
		   ));

      $this->renderLayout();

    }
	/*******Function for getting updated price********/
	 public function priceUpdateAction() {
		if($ids=$this->getRequest()->getParam('product_ids'))
		{
			$ids=explode(',',$ids);
		}
		$price=array();
		foreach($ids as $id)
		{
			$product=Mage::getModel('catalog/product')->load($id);
			if($product->getTypeId()=='simple')
			{
				$price[$product->getId()]=$product->getFinalPrice();
			}
			else
			{
				$price[$product->getId()]='none';
			}
		}
		echo json_encode($price);
	 }
	 /********Function for getting js for add to cart by ajax********/
	 public function swymcalljsAction() {
		if(Mage::getSingleton('core/session')->getData('notepad_wishlist')||
		Mage::getSingleton('core/session')->getData('notepadcart'))
		{
			echo $this->getLayout()->createBlock('notepad/notepad')->setTemplate('notepad/swymcall.phtml')->toHtml();
			return;
		}
		echo '';
	 }
	 /*******Function for authenticating loggedin users******/
	public function authenticateAction(){
		$email =$this->getRequest()->getParam('email');
		$retailerId=Mage::getStoreConfig('notepad/general/retailer_id',Mage::app()->getStore()->getId());
	    $pub_key_string=Mage::getStoreConfig('notepad/general/swymkey',Mage::app()->getStore()->getId());
	    openssl_get_publickey($pub_key_string);
	    openssl_public_encrypt($email,$crypttext,$pub_key_string,OPENSSL_PKCS1_OAEP_PADDING);
		//echo 'cryptText length before base64encode:'.strlen($crypttext).'</br>';
		$url=$this->getRequest()->getParam('host').'/provider/validate';
		$regid=$this->getRequest()->getParam('regId');
		$crypttext=base64_encode($crypttext);
		$postFields='pid='.$retailerId.'&e='.$crypttext.'&reg-id='.$regid;
		$postFields='{"pid":"'.$retailerId.'","e":"'.$crypttext.'","reg-id":"'.$regid.'"}';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$postFields);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:application/json"));
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$server_output = curl_exec ($ch);
		$info = curl_getinfo($ch);
		curl_close ($ch);
		if($info['http_code']==200)
		{
			echo json_encode(array('success'=>'1'));
		}
		else
		{
			echo json_encode(array('success'=>'0'));
		}

	 }
	 public function cartAction()
	 {
			if($this->checkProductExistOrNot())
			{
				$this->_redirect('checkout/cart');
				return;
			}

			$cart = Mage::getModel('checkout/cart');
			$cart->init();
			$product= Mage::getModel('catalog/product')->load($this->getRequest()->getParam('product'));
			$params = array(
				'product' => $product->getId(),
				'super_attribute' => $this->getRequest()->getParam('super_attribute'),
				'qty' => 1,
			);
			if($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE)
			{

				$cart->addProduct($product,$params);
			}
			elseif($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE)
			{
				$cart->addProduct($product ,
													array( 'product_id' => $productId,
															 'qty' => 1,
															 'bundle_option' => $this->getRequest()->getParam('bundle_option'),
															));
			}
			elseif($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_GROUPED)
			{
				$cart->addProduct($product ,
													array( 'product_id' => $productId,
															 'qty' => 1,
															 'super_group' => $this->getRequest()->getParam('super_group'),
															));
			}
			else
			{
				$cart->addProduct($product ,
													array( 'product_id' => $productId,
															 'qty' => 1,
															));
			}

			$cart->save();
			$this->_redirect('checkout/cart');
	 }
	 public function checkProductExistOrNot()
	 {
		$product= Mage::getModel('catalog/product')->load($this->getRequest()->getParam('product'));
		$quote = Mage::getSingleton('checkout/session')->getQuote();
		$cartItems = $quote->getAllVisibleItems();

		foreach ($cartItems as $item)
			{
				$options=$item->getOptions();
				foreach($options as $op)
				{
					/* if($item->getParentItem())
					echo $item->getParentItem()->getProduct()->getId().'</br>'; */
					if($item->getProduct()->getId()==$product->getId())
					{
						if($op->getCode()=='info_buyRequest')
						{
							$result=$op->getData('value');
							$var1 = unserialize($result);

							if($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE)
							{
								$super_attribute=$this->getRequest()->getParam('super_attribute');
								if($var1['super_attribute']==$super_attribute)
								{

									return true;
								}
							}
							elseif($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE)
							{
								$bundle_option=$this->getRequest()->getParam('bundle_option');
								if($var1['bundle_option']==$bundle_option)
								{
									return true;
								}

							}
							elseif($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_GROUPED)
							{
								$super_group=$this->getRequest()->getParam('super_group');
								if($var1['super_group']==$super_group)
								{
									return true;
								}

							}
							else
							{
								return true;
							}
						}
					}
				}

			}

			return false;
	 }
}
