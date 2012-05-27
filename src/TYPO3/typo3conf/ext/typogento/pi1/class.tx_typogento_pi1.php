<?php

/**
 * Frontend plugin
 *
 * @todo Check caching configuration (config.no_cache, $this->pi_USER_INT_obj, $this->pi_checkCHash and tslib_cObj::convertToUserIntObject())
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class tx_typogento_pi1 extends tslib_pibase {
	
	/**
	 * @var bool
	 */
	public $pi_checkCHash = false;
	
	/**
	 * @var bool
	 */
	public $pi_USER_INT_obj = true;
	
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
	 * @var array
	 */
	public $emConf = null;
	
	/**
	 * @var tx_typogento_interface
	 */
	protected $_magento = null;
	
	/**
	 * Constructor
	 * 
	 * Skips always cache hash checking. This will be done later.
	 * @see $pi_checkCHash, _init()
	 */
	public function __construct() {
		// call parent
		parent::__construct();
	} 
	
	/**
	 * Main method of the plugin
	 * 
	 * @param string $content The plugin content
	 * @param array $conf The plugin configuration
	 * @return The rendered content
	 */
	public function main($content, $conf) {
		// get configuration helper
		$helper = t3lib_div::makeInstance('tx_typogento_configurationHelper');
		// get plugin setup
		$setup = $helper->getSection(tx_typogento_configurationHelper::TYPOSCRIPT_SETUP);
		// 
		$type = $this->cObj->getUserObjectType();
		// convert content type if possible and no cache flag is set
		if ($conf['noCache'] && $type == tslib_cObj::OBJECTTYPE_USER) {
			$this->cObj->convertToUserIntObject();
			return '';
		// only check cache hash if content type is user and additional flag is not disabled
		} elseif ($type == tslib_cObj::OBJECTTYPE_USER && count($this->piVars) 
		&& (!isset($setup['checkCacheHash']) || $setup['checkCacheHash'])) {
			$GLOBALS['TSFE']->reqCHash();
		}
		// init the plugin
		$this->_init($conf);
		// render content
		$this->_render($content);
		// return content
		return $content;
	}
	
	/**
	 * Initialize the plugin
	 * 
	 * @param array $conf
	 */
	protected function _init(array &$conf) {
		// set plugin configuration
		$this->conf = &$conf;
		// 
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_initPIflexForm();
		// get the singleton instance
		$this->_magento = t3lib_div::makeInstance('tx_typogento_interface');
		// if Magento reports 404 error use TYPO3 page not found behavior
		if (isset($this->conf['useTYPO3pageNotFound'])
		&& $this->conf['useTYPO3pageNotFound']
		&& strpos(serialize((array) Mage::app()->getResponse()->getHeaders()), '404 File not found')
		) {
			$GLOBALS['TSFE']->pageNotFoundAndExit();
		}
	}
	
	protected function _render(&$content) {
		// skip if this is a redirect
		if (Mage::app()->getResponse()->isRedirect()) {
			return;
		}
		// get current page id
		$pid = $GLOBALS['TSFE']->id;
		// render block specified by typoscript
		if (isset($this->conf['block'])) {
			// get block name
			$name = $this->conf['block'];
			// check specified block
			switch($name) {
				case '__responseBody':
					// get body data
					$content .= $this->_magento->getBodyData();
					break;
				default:
					// get the specified block
					$block = $this->_magento->getBlock($name);
					// throw if default page head is not set
					if (!isset($block)) {
						tx_typogento_div::throwException('lib_block_not_available_error',
							array($_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'], $name)
						);
					}
					// if Mage_Core_Block_Text
					if ($block instanceof Mage_Core_Block_Text) {
						$block->setText('');
					}
					// get block html
					$content .= $block->toHtml();
					break;
			}
			// wrap content in base class if set
			if (!isset($this->conf['noWrap']) || !$this->conf['noWrap']) {
				$content = $this->pi_wrapInBaseClass($content);
			}
		// render content block otherwise
		} else {
			// get content block html
			if ($this->_magento->getBlock('content') !== null) {
				$content .= $this->_magento->getBlock('content')->toHtml();
			}
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typogento/pi1/class.tx_typogento_pi1.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typogento/pi1/class.tx_typogento_pi1.php']);
}

?>
