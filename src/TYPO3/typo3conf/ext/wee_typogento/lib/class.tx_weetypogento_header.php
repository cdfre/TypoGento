<?php

/**
 * TypoGento header
 * 
 * Integrates Magento html page header into a TYPO3 page.
 * 
 * @uses Mage, Mage_Page_Block_Html_Head, tslib_fe, t3lib_PageRenderer, tx_weetypogento_interface, tx_weetypogento_router, tx_weetypogento_tools
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class tx_weetypogento_header implements t3lib_Singleton {

	/**
	 * @var string
	 */
	protected $_path = null;
	
	/**
	 * @var Mage_Core_Model_Design_Package
	 */
	protected $_design = null;
	
	/**
	 * @var string
	 */
	protected $_url = null;
	
	/**
	 * @var Mage_Core_Block_Abstract
	 */
	protected $_block = null;
	
	/**
	 * @var string
	 */
	protected $_name = null;
	
	/**
	 * @var tx_weetypogento_interface
	 */
	protected $_magento = null;
	
	/**
	 * @var t3lib_PageRenderer
	 */
	protected $_renderer = null;
	
	/**
	 * @var bool
	 */
	protected $_isInitialized = false;

	/**
	 * Constructor for tx_weetypogento_header
	 * 
	 * @param string $name The name of the Magento html page block. Default is 'head'.
	 */
	public function __construct($name = 'head') {
		// set the header block name if given
		if (isset($name)) {
			$this->name = (string)$name;
		}
	}
	
	/**
	 * Initialize the page html block
	 * 
	 * Dispatch the current request to Magento front controller, 
	 * get the resulting Magento html page block and the TYPO3 
	 * page renderer.
	 * 
	 * @throws Exception
	 */
	protected function _init() {
		// check if init is already done
		if ($this->_isInitialized) {
			return;
		}
		// get the interface
		$this->_magento = t3lib_div::makeInstance('tx_weetypogento_interface');
		// dispatch path for current page
		//$this->_magento->dispatch();
		// get local directories
		try {
			$this->_path = Mage::getBaseDir();
			$this->_url = Mage::getBaseUrl('js');
		} catch (Exception $e) {
			throw new Exception(sprintf('Resolving local directories failed: \'%s\'', $e->getMessage()));
		}
		// get design package for lookup includings
		try {
			$this->_design = Mage::getDesign();
		} catch (Exception $e) {
			throw new Exception(sprintf('Access design package failed: \'%s\'', $e->getMessage()));
		}
		// get the header block
		$this->_block = $this->_magento->getBlock(&$this->name);
		// check the header block exists
		if (!isset($this->_block)) {
			//$source = array('pid' => $GLOBALS['TSFE']->id);
			$router = t3lib_div::makeInstance('tx_weetypogento_router');
			
			try {
				$url = $router->lookup(tx_weetypogento_router::ROUTE_SECTION_DISPATCH);
			} catch(Exception $e) {
				throw new Exception(sprintf('Block \'%s\' was not found. Lookup failed for page \'%s\': \'%s\'',
					$this->name, $GLOBALS['TSFE']->id, $e->getMessage()), 0, $e);
			}
			
			throw new Exception(sprintf('Block \'%s\' was not found. Targeting URL for page \'%s\' is \'%s\'', 
				$this->name, $GLOBALS['TSFE']->id, $url));
		}
		// check the header block type
		if (!($this->_block instanceof Mage_Page_Block_Html_Head)) {
			throw new Exception(sprintf('Unexpected Block type \'%s\'', get_class($this->_block)));
		}
		// get page renderer
		$this->_renderer = $GLOBALS['TSFE']->getPageRenderer();
		// set init flag
		$this->_isInitialized = true;
	}
	
	public function getBlock() {
		// init header
		$this->_init();
		// return block
		return $this->_block;
	}
	
	/**
	 * Render the header
	 * 
	 * Renders the Magento html page block into one appropriate 
	 * section of the TYPO3 page renderer. 
	 * 
	 * @throws Exception
	 */
	public function render() {
		// init the header
		$this->_init();
		
		try {
			// get template configuration
			$config = &tx_weetypogento_tools::getConfig();
			// collect items
			$items = &$this->_block->getData('items');
			// skip if no items exist
			if (!is_array($items)) {
				return array();
			}
			// get compression settings
			$compressJs = (bool)$config['compressJs'] || (bool)$config['minifyJS'];
			$compressCss = (bool)$config['compressCss'] || (bool)$config['minifyCSS'];
			$wrap = '<!--[if %s ]>|<![endif]-->';
			// iter items
			foreach ($items as &$item) {
				if (!is_null($item['cond']) && !$this->_block->getData($item['cond']) || !isset($item['name'])) {
					continue;
				}
				// if conditional is set
				if(!empty($item['if'])) {
					// add includes with conditional
					$conditionWrap = sprintf(&$wrap, &$item['if']);
				} else {
					$conditionWrap = '';
				}
				// prepares item
				$this->_prepareItem($item['name'], $item['type'], $item['params']);

				if (strpos(&$item['name'], '.js') !== false) {
					$this->_renderer->addJsFile(&$item['name'], 'text/javascript', 
						$compressJs, false, &$conditionWrap);
				} elseif (strpos(&$item['name'], '.css') !== false) {
					$this->_renderer->addCssFile(&$item['name'], 'stylesheet', 
						&$item['params']['media'], &$item['params']['title'], $compressCss, false, &$conditionWrap);
				} else {
					if (!empty($conditionWrap)) {
						$html = &$GLOBALS['TSFE']->cObj->stdWrap(&$item['name'], &$conditionWrap);
					} else {
						$html = &$item['name'];
					}
					$this->_renderer->addHeaderData(&$html);
				}
			}
			// render translator script
			$json = &$this->_block->helper('core/js')->getTranslateJson();
			$script = 'var Translator = new Translate('.$json.');';
			$this->_renderer->addJsInlineCode('Magento Translator', &$script, $compressJs);
			// render child html
			$html = &$this->_block->getChildHtml();
			if (!empty($html)) {
				$this->_renderer->addHeaderData(&$html);
			}
			// render includes
			$html = &$this->_block->getIncludes();
			if (!empty($html)) {
				$this->_renderer->addHeaderData(&$html);
			}
		} catch (Exception $e) {
			trigger_error(sprintf('Rendering failed: %s.', $e->getMessage()), E_USER_WARNING);
		}
	}
	
	/**
	 * Prepare a header item
	 * 
	 * Prepares a resource of the Magento page html block for its 
	 * use in TYPO3.
	 * 
	 * @param array $static
	 * @param array $skin
	 * @param bool $local
	 */
	protected function &_prepareItem(&$item, &$type, &$parameters, $local = false) {
		$default = array('_type' => 'skin');
		$empty = array();
		// set location
		if (strpos(&$type, 'skin') !== false) {
			$location = 'skin';
		} else {
			$location = 'static';
		}
		// set type
		if (strpos(&$type, 'js') !== false) {
			$type = 'js';
		} else if (strpos(&$type, 'css') !== false) {
			$type = 'css';
			// check if params where set
			if (!empty($parameters) && is_string($parameters)) {
				// map raw format
				$parameters = &explode('=', &$parameters);
				// set media if found
				if ($i = array_search('media', &$parameters) !== false) {
					$parameters['media'] = &trim(&$parameters[$i], ' "\'');
				}
				// set title if found
				if ($i = array_search('title', &$parameters) !== false) {
					$parameters['title'] = &trim(&$parameters[$i], ' "\'');
				}
			} else {
				// set default params for css
				$parameters = array('media' => 'all', 'title' => '');
			}
		} else if (strpos(&$type, 'rss') !== false){
			$type = 'rss';
		} else {
			$type = 'other';
		}
		// prepare item value
		if ($location == 'static') {
			if ($local) {
				$item = $this->_path . DS . 'js' . DS . $item;
			} else {
				$item = $this->_url . $item;
			}
		} elseif ($location == 'skin') {
			if ($local) {
				$item = &$this->_design->getFilename(&$item, &$default);
			} else {
				$item = &$this->_design->getSkinUrl(&$item, &$empty);
			}
		} else {
			if ($type == 'rss') {
				$item= &sprintf(
					'<link href="%s"%s rel="alternate" type="application/rss+xml" />',
					&$item, &$parameters
				);
			} else {
				$item = &sprintf(
					'<link%s href="%s" />',
					&$item, &$parameters
				);
			}
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wee_typogento/lib/class.tx_weetypogento_header.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wee_typogento/lib/class.tx_weetypogento_header.php']);
}

?>
