<?php 

/**
 * TypoScript frontend register
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class tx_typogento_register_header extends tx_typogento_register_abstract {
	
	protected function _onPostLoad() {
		// configuration
		$configuration = $this->_configuration;
		// skip disabled
		if (!$configuration->get('header.register', false)) {
			return;
		}
		// data
		$this->_data = array();
		// fields
		$fields = (string)$configuration->get('header.register.fields', 'title,description,keywords');
		$fields = explode(',', $fields);
		// skip empty
		if (count($fields) < 1) {
			return;
		}
		// header
		$header = $configuration->get('header.block', 'head');
		$header = Mage::app()->getLayout()->getBlock($header);
		// skip empty
		if (!$header) {
			return;
		}
		// collect
		foreach ($fields as $field) {
			if ($header->hasData($field)) {
				$this->_data[$field] = (string)$header->getData($field);
			}
		}
		// flatten
		$this->_data = &tx_typogento_div::getFlatArray($this->_data, 'tx_typogento.header.');
	}
	
	protected function _onPreLoad() {
		// configuration
		$configuration = $this->_configuration;
		// skip disabled
		if ($configuration->get('header.register', false)) {
			return;
		}
		// cached
		$this->_data = &$configuration->get('header.register.', null, tx_typogento_configuration::CACHE);
	}
}

?>