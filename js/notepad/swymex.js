var customerIsLoggedIn = false;
var customerEmail = '';
var wishlistTitle = '';
var wishlistStatus='';
try {
   function sQuery(query) {
      return document.querySelector(query);
   }

   function _authenticateLoggedInUser() {
      var sw = window._swat;
      var regid = sw.getSwymRegistrationId();
      var hosturi = sw.getSwymHostUri();

      if(!regid || !hosturi) {
         console.log('skipping auth call due to invalid regId and/or hosturi');
         return;
      }
      if(customerIsLoggedIn == false || customerEmail == '')  {
         console.log('skipping auth calls');
         return;
      }

      var email = customerEmail;
      var url=sQuery('#authenticateloggedinuser').value;
      var xmlhttp = window.XMLHttpRequest ? new XMLHttpRequest() :
                                            new ActiveXObject("Microsoft.XMLHTTP");
      var response='';
      xmlhttp.onreadystatechange = function() {
         if (xmlhttp.readyState == 4) {
            if (xmlhttp.status == 200) {
               response=JSON.parse(xmlhttp.responseText);
               sw.refreshViaProvider();
            } else {
               console.log('Err HTTP POST query to url ' + url + ' returned ' + xmlhttp.status);
            }
         }
      }

      xmlhttp.open("POST", url, true /*isAsync*/);
      xmlhttp.setRequestHeader( "Content-type", "application/x-www-form-urlencoded");
      xmlhttp.send("regId=" + regid + '&host=' + hosturi + '&email=' + email);
      return response;
   }

   function tryAuth() {
      var sw = window._swat;
      if(!sw) {
         console.log('sw not initialized!');
         return;
      }

      if(!isSwymAuthn()) {
         _authenticateLoggedInUser();
      }
   }

  function getWishlistElements(content)
  {

	var wishlistProducts=new Object();
	for(var index in content)
	{
		if(typeof content[index]=='object')
		{
			if(typeof content[index]['et']!='undefined')
			{
				if(typeof content[index]['epi']!='undefined' && content[index]['epi']!=null)
				{
					 if(content[index]['et']==4) {
						wishlistProducts[content[index]['epi']]=content[index]['epi'];
					}

				}

			}

		}
	}

	return wishlistProducts;
  }
   /*******Inject wishlist links*******/
	function injectWishlistLinks()
	{

		 if(wishlistStatus=='1'){

			$wishlistElements=new Object;
			window._swat.fetch(
				function(r) {

					$wishlistElements=getWishlistElements(r);
					$linkContainers=document.getElementsByClassName('add-to-links');

					for(var index in $linkContainers)
					{

						if(typeof $linkContainers[index]=='object')
						{

							$li=findParentElement($linkContainers[index],'LI');
							if($li==null)
							{

								$form=findParentElement($linkContainers[index],'FORM');
								if($form==null)
									continue;
								productId=$form.getElementsByClassName('wishlist-content-id')[0].value;
							}
							else
							{
								if($li.getElementsByClassName('wishlist-content-id')[0]!=null)
								{
									productId=$li.getElementsByClassName('wishlist-content-id')[0].value;
								}
								else
								{
									continue;
								}
							}

							$wishlistElement=createWishlistElement(productId,$wishlistElements)
							$linkContainers[index].appendChild($wishlistElement);
						}

					}
				}
			);

		}

	}
	/*********Function for creating wishlist links ************/
	function createWishlistElement(product,wishlistElements){

		var li=createElement('li','','','');
		var anchor=createElement('a','link-wishlist','','');
		anchor.innerHTML=wishlistTitle;
		anchor.href='javascript:void(0);';
		anchor.setAttribute('product',product);
		if(typeof wishlistElements[product]!='undefined')
			{
				anchor.className='link-wishlist disabled';

			}
			else
			{

				anchor.onclick=function(){

					window._swat.addToWishList({ 'du' : sQuery('#wishlist-content-url-'+product).value,
												'dt' : sQuery('#wishlist-content-name-'+product).value,
												'pr' :sQuery('#wishlist-content-price-'+product).value,
												'op' :sQuery('#wishlist-content-price-'+product).value,
												'qty' : '1',
												'epi' : sQuery('#wishlist-content-id-'+product).value,
												'iu' :sQuery('#wishlist-content-image-'+product).value,

												},
													  function(r) { console.log('add to wish done'); },
													  {});
						this.className=this.className+' disabled';
						this.onclick=function(){};

					};
			}
			li.appendChild(anchor);
			return li;
	}
	/**********function for creating element*************/
	function createElement(ele,clas,id,text)
	{
		var element=document.createElement(ele);
		element.className=clas;
		if(id!='')
		{
			element.id=id;
		}
		if(text!='')
		{
			var t = document.createTextNode(text);
			element.appendChild(t);
		}
		return element;
	}
	function findParentElement(elem,tag){
		var parent = elem.parentNode;
		if(parent && parent.tagName != tag){
			parent = findParentElement(parent,tag);
		}
		return parent;
	}
	Event.observe(window, 'load', injectWishlistLinks);


} catch(e) {
   console.log(e.message);
}
