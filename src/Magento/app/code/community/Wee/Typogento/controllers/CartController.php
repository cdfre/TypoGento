<?php 
/**
 * TypoGento cart controller
 * 
 * Controller for a simple sidebar cart with Ajax
 *
 */
class Wee_Typogento_CartController extends Mage_Core_Controller_Front_Action {
	
	public function indexAction() {
		$block = Mage::getSingleton('core/layout')
			->createBlock('checkout/cart_sidebar', 'root')
			->setTemplate('checkout/cart/sidebar.phtml');
		echo $block->toHtml(); 
	}
} // Class Wee_Template_IndexController End
