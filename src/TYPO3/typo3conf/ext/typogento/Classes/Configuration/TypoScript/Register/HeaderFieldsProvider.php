<?php 

namespace Tx\Typogento\Configuration\TypoScript\Register;

use Mage;

use \Tx\Typogento\Utility\GeneralUtility;
use \Tx\Typogento\Configuration\ConfigurationManager;

/**
 * TypoScript frontend register provider for 'register:tx_typogento.header.<field>'
 *
 * @author Artus Kolanowski <artus@ionoi.net>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class HeaderFieldsProvider extends AbstractProvider {
	
	/**
	 * Loads the requested Magento fields into the TypoScript registers.
	 * 
	 * @see \Tx\Typogento\Configuration\TypoScript\Register\AbstractProvider::onPostLoad()
	 */
	protected function onPostLoad() {
		// configuration
		$configuration = $this->configuration;
		// skip disabled
		if (!(bool)$configuration->get('header.register.enable', false)) {
			return;
		}
		// data
		$this->data = array();
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
				$this->data[$field] = (string)$header->getData($field);
			}
		}
		// flatten
		$this->data = &GeneralUtility::getFlatArray($this->data, 'tx_typogento.header.');
	}
	
	/**
	 * Loads the cached Magento fields into the TypoScript registers.
	 * 
	 * @see \Tx\Typogento\Configuration\TypoScript\Register\AbstractProvider::onPreLoad()
	 */
	protected function onPreLoad() {
		// configuration
		$configuration = $this->configuration;
		// skip disabled
		if (!(bool)$configuration->get('header.register.enable', false)) {
			return;
		}
		// cached
		$this->data = &$configuration->get('header.register.', null, ConfigurationManager::CACHE);
	}
}

?>