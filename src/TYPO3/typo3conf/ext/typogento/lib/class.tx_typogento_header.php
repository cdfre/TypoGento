<?php

/**
 * Page header
 * 
 * Integrate the Magento html page header into the current TYPO3 page.
 * 
 * @uses Mage, Mage_Page_Block_Html_Head, tslib_fe, t3lib_PageRenderer, tx_typogento_interface, tx_typogento_router, tx_typogento_div
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class tx_typogento_header {
	
	const IMPORT_CSS = 1;
	
	const IMPORT_JS = 2;
	
	const COMPRESS_CSS = 1;
	
	const COMPRESS_JS = 2;

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
	 * @var tx_typogento_interface
	 */
	protected $_interface = null;
	
	/**
	 * @var tx_typogento_configuration
	 */
	protected $_configuration = null;
	
	protected static $_types = array(
		'skin_js'  => array('skin', 'js'),
		'skin_css' => array('skin', 'js'),
		'js'       => array('static', 'js'),
		'js_css'   => array('static', 'css'),
		'rss'      => array('other', 'rss'),
		'link_rel' => array('other', 'link'),
		'default'  => array('other', 'other')
	);

	/**
	 * Constructor
	 */
	public function __construct($name = 'head') {
		// cast block name
		$name = (string)$name;
		// get the interface
		$this->_interface = t3lib_div::makeInstance('tx_typogento_interface');
		// open the interface
		$this->_interface->open();
		// initialize
		try {
			// base locations
			try {
				$this->_path = Mage::getBaseDir();
				$this->_url = Mage::getBaseUrl();
			} catch (Exception $e) {
				throw tx_typogento_div::exception('lib_interface_access_failed_error',
					array(), $e
				);
			}
			// design package
			try {
				$this->_design = Mage::getDesign();
			} catch (Exception $e) {
				throw tx_typogento_div::exception('lib_interface_access_failed_error',
					array(), $e
				);
			}
			// header block
			$this->_block = Mage::app()->getLayout()->getBlock($name);
			// check the header block exists
			if (!$this->_block) {
				throw tx_typogento_div::exception('lib_block_not_available_error',
					array($_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'], $name)
				);
			}
			// check the header block type
			if (!($this->_block instanceof Mage_Page_Block_Html_Head)) {
				throw tx_typogento_div::exception('lib_block_type_not_supported_error',
					array(get_class($this->_block))
				);
			}
		} catch (Exception $e) {
			// close the interface
			$this->_interface->close();
			throw $e;
		}
		// close the interface
		$this->_interface->close();
	}
	
	/**
	 * Render header
	 * 
	 * @param t3lib_PageRenderer $renderer The page renderer
	 * @param int $compress Bitmask for resource compression @see COMPRESS_JS and COMPRESS_CSS
	 * @param int $import Bitmask for resource import @see IMPORT_JS and IMPORT_CSS
	 * @throws Exception If somthing went wrong
	 */
	public function render(t3lib_PageRenderer $renderer, $compress = 0, $import = 0) {
		try {
			// 
			$block = $this->_block;
			// collect items
			$items = &$block->getData('items');
			// skip
			if (!is_array($items)) {
				return;
			}
			// get compression settings
			$compress = array(
				'js' => $compress & self::COMPRESS_JS, 
				'css' => $compress & self::COMPRESS_CSS 
			);
			$import = array(
				'js' => $import & self::IMPORT_JS, 
				'css' => $import & self::IMPORT_CSS 
			);
			// open the interface
			$this->_interface->open();
			// render items
			foreach ($items as &$item) {
				// skip
				if (!is_null($item['cond']) && !$$block->getData($item['cond']) || !isset($item['name'])) {
					continue;
				}
				// condition
				if(!empty($item['if'])) {
					// add includes with conditional
					$condition = str_replace('%if%', $item['if'], '<!--[if %if% ]>|<![endif]-->');
				} else {
					$condition = '';
				}
				// prepare
				$this->_prepareItem($item['name'], $item['type'], $item['params'], $import);
				// render
				switch($item['type']) {
					case 'js':
						$renderer->addJsFile($item['name'], 'text/javascript', 
							$compressJs, false, $condition);
						break;
					case 'css':
						$renderer->addCssFile($item['name'], 'stylesheet', 
							$item['params']['media'], $item['params']['title'], $compress['css'], false, $condition);
						break;
					case 'rss':
					case 'link':
						$html = '<link href="' . $item['name'] . '" ' . $item['params'] . '/>';
						$html = &$GLOBALS['TSFE']->cObj->stdWrap($html, $condition);
						$renderer->addHeaderData($html);
						break;
				}
			}
			// render translator
			$json = &$block->helper('core/js')->getTranslateJson();
			$script = 'var Translator = new Translate('.$json.');';
			if ($import['js']) {
				$script = TSpagegen::inline2TempFile($script, 'js');
				$renderer->addJsFile($script, 'text/javascript', $compressJs);
			} else {
				$renderer->addJsInlineCode(
					'Magento Translator', $script, $compressJs
				);
			}
			// render children
			$html = &$block->getChildHtml();
			if (!empty($html)) {
				$renderer->addHeaderData($html);
			}
			// render includes
			$html = &$block->getIncludes();
			if (!empty($html)) {
				$renderer->addHeaderData($html);
			}
		} catch (Exception $e) {
			// close the interface
			$this->_interface->close();
			// re-throw exception
			throw tx_typogento_div::exception('lib_page_head_rendering_failed_error', 
				array($_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']), $e
			);
		}
		// close the interface
		$this->_interface->close();
	}
	
	/**
	 * Prepare header item
	 * 
	 * @param string $item The item name
	 * @param string $type The item type @see $_types
	 * @param array|string $parameters The item paramters
	 * @param array $import The import flags @see _importItem()
	 */
	protected function &_prepareItem(&$item, &$type, &$parameters, &$import) {
		// transform type
		if (!isset(self::$_types[$type])) {
			$type = 'default';
		}
		$location = self::$_types[$type][0];
		$extension = self::$_types[$type][1];
		// prepare type
		$type = $extension;
		// prepare url
		switch ($location) {
			case 'static':
				if ($import[$type]) {
					$item = $this->_path . DS . 'js' . DS . $item;
					$this->_importItem($item, $extension);
				} else {
					$item = $this->_url . DS . 'js' . DS . $item;
				}
				break;
			case 'skin':
				if ($import[$type]) {
					$item = $this->_design->getFilename($item, array('_type' => 'skin'));
					$this->_importItem($item, $extension);
				} else {
					$item = $this->_design->getSkinUrl($item, array());
				}
				break;
		}
		// prepare parameters
		switch ($extension) {
			case 'css':
				if (is_string($parameters)) {
					// map raw format
					$parameters = explode('=', $parameters);
					// set media if found
					if ($i = array_search('media', $parameters) !== false) {
						$parameters['media'] = trim($parameters[$i], ' "\'');
					}
					// set title if found
					if ($i = array_search('title', $parameters) !== false) {
						$parameters['title'] = trim($parameters[$i], ' "\'');
					}
				} else {
					// set default params for css
					$parameters = array('media' => 'all', 'title' => '');
				}
				break;
			case 'rss':
				if (empty($paramters)) {
					$parameters = 'rel="alternate" type="application/rss+xml" ';
				} else {
					$parameters .= ' ';
				}
				break;
			case 'link_rel':
				if (empty($paramters)) {
					$parameters = '';
				} else {
					$parameters .= ' ';
				}
				break;
		}
	}
	
	/**
	 * Import header item
	 *
	 * @param string $item The item name
	 * @param string $extension Extension of the item (js or css)
	 * @throws Exception If the item was not found or the extension is unknown
	 */
	protected function _importItem(&$item, &$extension) {
		//
		$source = &$item;
		// last change
		$time = @filemtime($source);
		//
		if ($time === false) {
			throw tx_typogento_div::exception('lib_file_access_error',
				array($item), $e
			);
		}
		//
		$hash = $source . $time;
		$hash = substr(md5($hash), 0, 10);
		//
		switch ($extension) {
			case 'js' :
				$temp = 'typo3temp/javascript_' . $hash . '.js';
			break;
			case 'css' :
				$temp = 'typo3temp/stylesheet_' . $hash . '.css';
			break;
			default:
				throw tx_typogento_div::exception('lib_unknown_error',
					array(), $e
				);
		}
		//
		$target = PATH_site . $temp;
		//
		if (!@is_file($target)) {
			//
			if (!@copy($item, $target)) {
				throw tx_typogento_div::exception('lib_file_access_error',
					array($item), $e
				);
			}
			t3lib_div::fixPermissions($target);
		}
		//
		$item = $GLOBALS['TSFE']->absRefPrefix . $temp;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typogento/lib/class.tx_typogento_header.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typogento/lib/class.tx_typogento_header.php']);
}

?>
