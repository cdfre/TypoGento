<?php

/**
 * TypoGento page controller
 * 
 * Just renders the default layout.
 *
 */
class Wee_Typogento_PageController extends Mage_Core_Controller_Front_Action {
	
	public function indexAction() {
		$this->loadLayout();
		$this->renderLayout();
	}
} // Class Wee_Template_IndexController End
