<?php

/**
 * TypoGento Layout Model
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class Wee_Typogento_Model_Layout extends Mage_Core_Model_Layout {
	
	/**
	 * Class Constuctor
	 *
	 * @param array $data
	 */
	public function __construct($data = array()) {
		parent::__construct ( $data );
	}
	
	/**
	 * Get all blocks marked for output
	 *
	 * @return string
	 */
	public function getOutput() {
		// get typo3 helper
		$typo3 = Mage::helper('typogento/typo3');
		// use default behaviour if typo3 is not enabled
		if (!$typo3->isEnabled()) {
			return parent::getOutput();
		} else {
			$out = '';
			
			if (!empty($this->_output)) {
				foreach ($this->_output as $callback) {
					$out .= $this->getBlock($callback[0])->$callback[1]();
				}
			}
			
			return $out;
		}
	}

}