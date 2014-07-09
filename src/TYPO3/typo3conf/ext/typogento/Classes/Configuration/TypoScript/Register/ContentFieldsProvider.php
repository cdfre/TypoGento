<?php 

namespace Tx\Typogento\Configuration\TypoScript\Register;

use Tx\Typogento\Utility\GeneralUtility;
use Tx\Typogento\Utility\PluginUtility;
use Tx\Typogento\Configuration\ConfigurationManager;

/**
 * TypoScript frontend register provider for 'register:tx_typogento.content.<field>'.
 *
 * @author Artus Kolanowski <artus@ionoi.net>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class ContentFieldsProvider extends AbstractProvider {
	
	protected function onPostLoad() {}
	
	/**
	 * Loads the configuration of the content plugin into the TypoScript registers.
	 * 
	 * @todo Load only requested fields.
	 * @see \Tx\Typogento\Configuration\TypoScript\AbstractRegister::onPreLoad()
	 */
	protected function onPreLoad() {
		// configuration
		$configuration = $this->configuration;
		// skip disabled
		if (!(bool)$configuration->get('content.register.enable', false)) {
			return;
		}
		// cached
		$this->data = &$configuration->get('content.register.', null, ConfigurationManager::CACHE);
		// skip cached
		if ($this->data !== null) {
			return;
		}
		// data
		$this->data = array();
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
			$flexform = &GeneralUtility::getContentFlexForm($this->frontend, $type, $column, $position);
			// skip empty
			if (count($flexform) > 0) {
				// transform
				$this->data = &PluginUtility::getFlexFormConfiguration($flexform);
				// flatten
				$this->data = &GeneralUtility::getFlatArray($this->data, 'tx_typogento.content.');
			}
		}
		// cache
		$configuration->set('content.register.', $this->data, ConfigurationManager::CACHE);
	}
}

?>