<?php 

/**
 * Abstract TypoScript register
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class tx_typogento_register_content extends tx_typogento_register_abstract {
	
	protected function _onPostLoad() {
		
	}
	
	protected function _onPreLoad() {
		// configuration
		$configuration = $this->_configuration;
		// skip disabled
		if (!$configuration->get('content.register', false)) {
			return;
		}
		// cached
		$this->_data = &$configuration->get('content.register.', null, tx_typogento_configuration::CACHE);
		// skip cached
		if ($this->_data != null) {
			return;
		}
		// data
		$this->_data = array();
		// fields
		$fields = (string)$configuration->get('content.register.fields', 'id,route,controller,action,cache');
		$fields = explode(',', $fields);
		// skip empty
		if (count($fields) > 0) {
			// selector
			$column = (int)$configuration->get('content.register.column', 0);
			$position = (int)$configuration->get('content.register.position', 0);
			$type = 'typogento_pi1';
			// select
			$flexform = &tx_typogento_div::getContentFlexForm($this->_frontend, $type, $column, $position);
			// skip empty
			if (count($flexform) > 0) {
				// helper
				$helper = t3lib_div::makeInstance('tx_typogento_pi1_helper');
				// transform
				$this->_data = &$helper->getFlexFormConfiguration($flexform);
				// flatten
				$this->_data = &tx_typogento_div::getFlatArray($this->_data, 'tx_typogento.content.');
			}
		}
		// cache
		$configuration->set('content.register.', $this->_data, tx_typogento_configuration::CACHE);
	}
}

?>