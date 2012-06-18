<?php

/**
 * Frontend plugin
 * 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class tx_typogento_pi1 extends tslib_pibase {
	
	/**
	 * @var string
	 */
	public $prefixId = 'tx_typogento';
	
	/**
	 * @var string
	 */
	public $scriptRelPath = 'pi1/class.tx_typogento_pi1.php';
	
	/**
	 * @var string
	 */
	public $extKey = 'typogento';
	
	/**
	 * @var tx_typogento_interface
	 */
	protected $_interface = null;
	
	/**
	 * @var tx_typogento_configuration
	 */
	protected $_configuration = null;
	
	/**
	 * @var int
	 */
	protected $_section = -1;
	
	/**
	 * Constructor
	 * 
	 * Disables the parent constructor and thus skips cache hash checking.
	 * 
	 * @see _initialize()
	 */
	public function __construct() {
	}
	
	/**
	 * Run the plugin
	 * 
	 * @param string $content The plugin content
	 * @param array $conf The plugin configuration
	 * 
	 * @return string The rendered content
	 */
	public function main($content, $typoscript) {
		// initialize
		$this->_initialize($typoscript);
		// skip
		if ($this->_interface != null) {
			// response
			$response = Mage::app()->getResponse();
			// check response
			if (!$response->isAvailable()) {
				$GLOBALS['TSFE']->pageNotFoundAndExit();
			} else if (!$response->isRedirect()) {
				// open interface
				$this->_interface->open();
				// render content
				try {
					$this->_render($content);
				} catch (Exception $e) {
					// close interface
					$this->_interface->close();
					// re-throw exception
					// @todo extend connfiguration
					throw $e;
				}
				// close interface
				$this->_interface->close();
			}
		} else {
			$content = null;
		}
		// return content
		return $content;
	}
	
	/**
	 * Initialize the plugin
	 * 
	 * @param array $typoscript
	 * @throws Exception
	 */
	protected function _initialize(array &$typoscript) {
		// skip
		if ($this->_interface != null) {
			return;
		}
		// configuration
		$this->_configuration = t3lib_div::makeInstance('tx_typogento_configuration');
		// merge flexform
		if (isset($this->cObj->data['pi_flexform'])) {
			// reference flexform
			$flexform = &$this->cObj->data['pi_flexform'];
			// transform flexform
			if (!is_array($flexform)) {
				$flexform = t3lib_div::xml2array($flexform);
				$helper = t3lib_div::makeInstance('tx_typogento_pi1_helper');
				$flexform = $helper->getFlexFormConfiguration($flexform);
			}
			// override typoscript
			if (is_array($flexform)) {
				// cache flag
				if ($flexform['cache'] === false) {
					$typoscript['cache'] = false;
				}
			}
		}
		// merge typoscript
		$this->_section = $this->_configuration->merge(
			$typoscript, tx_typogento_configuration::PLUGIN
		);
		// cache flag
		$cache = (bool)$this->_configuration->get(
			'cache', true, $this->_section
		);
		// content type
		$type = $this->cObj->getUserObjectType();
		// get/post
		$variables = t3lib_div::_GPmerged($this->prefixId);
		// check caching
		if ($type == tslib_cObj::OBJECTTYPE_USER) {
			// check flag
			if (!$cache) {
				// convert to user int
				$this->cObj->convertToUserIntObject();
				return;
			} else if (count($variables) > 0) {
				// to make this clear :P
				$this->pi_checkCHash = true;
				// check hash
				$GLOBALS['TSFE']->reqCHash();
			}
		} else {
			// to make this clear :P
			$this->pi_USER_INT_obj = true;
		}
		// initialize interface
		try {
			$this->_interface = t3lib_div::makeInstance('tx_typogento_interface');
		} catch (Exception $e) {
			// re-throw exception
			// @todo typoscript configuration
			throw $e;
		}
	}
	
	/**
	 * 
	 * @param unknown_type $content
	 */
	protected function _render(&$content) {
		// application
		$application = Mage::app();
		// response
		$response = $application->getResponse();
		// layout
		$layout = $application->getLayout();
		// configuration
		$configuration = $this->_configuration;
		// render block
		if (!$response->isAjax()) {
			// block name
			$name = $configuration->get(
				'block', 'content', $this->_section
			);
			// wrap flag
			$wrap = !(bool)$configuration->get(
				'noWrap', true, $this->_section
			);
			// retrive block
			$block = $layout->getBlock($name);
			// check block
			if (!$block) {
				throw tx_typogento_div::exception('lib_block_not_available_error',
					array($_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'], $name)
				);
			} else if (!($block instanceof Mage_Core_Block_Abstract)) {
				throw tx_typogento_div::exception('lib_block_type_not_supported_error',
					array(get_class($name))
				);
			}
			// render html
			$content .= $block->toHtml();
			// wrap html
			if ($wrap) {
				$content = $this->pi_wrapInBaseClass($content);
			}
		// render response
		} else {
			// render body
			$content .= $response->outputBody();
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typogento/pi1/class.tx_typogento_pi1.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typogento/pi1/class.tx_typogento_pi1.php']);
}

?>
