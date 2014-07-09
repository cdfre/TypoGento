<?php

namespace Tx\Typogento\ViewHelper;

use Tx\Typogento\Configuration\ConfigurationManager;

/**
 * Integrates the Magento html page header into the current TYPO3 page.
 * 
 * @author Artus Kolanowski <artus@ionoi.net>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class PageHeaderViewHelper {
	
	const IMPORT_CSS = 1;
	
	const IMPORT_JS = 2;
	
	const COMPRESS_CSS = 1;
	
	const COMPRESS_JS = 2;

	/**
	 * @var string
	 */
	protected $path = null;
	
	/**
	 * @var \Mage_Core_Model_Design_Package
	 */
	protected $design = null;
	
	/**
	 * @var string
	 */
	protected $url = null;
	
	/**
	 * @var \Mage_Core_Block_Abstract
	 */
	protected $block = null;
	
	/**
	 * @var \Tx\Typogento\Core\Dispatcher
	 */
	protected $dispatcher = null;
	
	protected static $types = array(
		'skin_js'  => array('skin', 'js'),
		'skin_css' => array('skin', 'css'),
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
		$this->dispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Tx\\Typogento\\Core\\Dispatcher');
		// open the interface
		$this->dispatcher->getEnvironment()->initialize();
		// initialize
		try {
			// base locations
			try {
				$this->path = \Mage::getBaseDir();
				$this->url = \Mage::getBaseUrl();
			} catch (\Exception $e) {
				throw new \Exception(sprintf('Unknown error: %s', $e->getMessage()), 1356932123, $e);
			}
			// design package
			try {
				$this->design = \Mage::getDesign();
			} catch (\Exception $e) {
				throw new \Exception(sprintf('Unknown error: %s', $e->getMessage()), 1356932107, $e);
			}
			// header block
			$this->block = \Mage::app()->getLayout()->getBlock($name);
			// check the header block exists
			if (!$this->block) {
				throw new \Exception(sprintf('The block "%s" was not rendered for the requested URL "%s".', $name, $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']), 1356932029);
			}
			// check the header block type
			if (!($this->block instanceof \Mage_Page_Block_Html_Head)) {
				throw new \Exception(sprintf('The Block type "%s" is not supported.', get_class($this->block)), 1356931933);
			}
		} catch (\Exception $e) {
			// close the interface
			$this->dispatcher->getEnvironment()->deinitialize();
			throw $e;
		}
		// close the interface
		$this->dispatcher->getEnvironment()->deinitialize();
	}
	
	/**
	 * Render header
	 * 
	 * @param \TYPO3\CMS\Core\Page\PageRenderer $renderer The page renderer
	 * @param int $compress Bitmask for resource compression @see COMPRESS_JS and COMPRESS_CSS
	 * @param int $import Bitmask for resource import @see IMPORT_JS and IMPORT_CSS
	 * @throws Exception If somthing went wrong
	 */
	public function render(\TYPO3\CMS\Core\Page\PageRenderer $renderer, $compress = 0, $import = 0) {
		try {
			// 
			$block = $this->block;
			// collect items
			$items = $block->getData('items');
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
			$this->dispatcher->getEnvironment()->initialize();
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
				$this->prepareItem($item['name'], $item['type'], $item['params'], $import);
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
			$json = $block->helper('core/js')->getTranslateJson();
			$script = 'var Translator = new Translate('.$json.');';
			if ($import['js']) {
				$script = \TYPO3\CMS\Frontend\Page\PageGenerator::inline2TempFile($script, 'js');
				$renderer->addJsFile($script, 'text/javascript', $compressJs);
			} else {
				$renderer->addJsInlineCode(
					'Magento Translator', $script, $compressJs
				);
			}
			// render children
			$html = $block->getChildHtml();
			if (!empty($html)) {
				$renderer->addHeaderData($html);
			}
			// render includes
			$html = $block->getIncludes();
			if (!empty($html)) {
				$renderer->addHeaderData($html);
			}
		} catch (\Exception $e) {
			// close the interface
			$this->dispatcher->getEnvironment()->deinitialize();
			// re-throw exception
			throw new \Exception(sprintf('The requested URL "%s" could not be retrieved: %s', $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'], $e->getMessage()), 1356931794, $e);
		}
		// close the interface
		$this->dispatcher->getEnvironment()->deinitialize();
	}
	
	/**
	 * Prepare header item
	 * 
	 * @param string $item The item name
	 * @param string $type The item type @see $_types
	 * @param array|string $parameters The item paramters
	 * @param array $import The import flags @see _importItem()
	 */
	protected function &prepareItem(&$item, &$type, &$parameters, &$import) {
		// transform type
		if (!isset(self::$types[$type])) {
			$type = 'default';
		}
		$location = self::$types[$type][0];
		$extension = self::$types[$type][1];
		// prepare type
		$type = $extension;
		// prepare url
		switch ($location) {
			case 'static':
				if ($import[$type]) {
					$item = $this->path . DS . 'js' . DS . $item;
					$this->importItem($item, $extension);
				} else {
					$item = $this->url . DS . 'js' . DS . $item;
				}
				break;
			case 'skin':
				if ($import[$type]) {
					$item = $this->design->getFilename($item, array('_type' => 'skin'));
					$this->importItem($item, $extension);
				} else {
					$item = $this->design->getSkinUrl($item, array());
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
	protected function importItem(&$item, &$extension) {
		//
		$source = &$item;
		// last change
		$time = @filemtime($source);
		//
		if ($time === false) {
			throw new \Exception(sprintf('Can not access file "%s".', $item), 1356931609);
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
				throw new \Exception('Unknown error.', 1356931436);
		}
		//
		$target = PATH_site . $temp;
		//
		if (!@is_file($target)) {
			//
			if (!@copy($item, $target)) {
				throw new \Exception(sprintf('Can not access file "%s".', $item), 1356931527);
			}
			\TYPO3\CMS\Core\Utility\GeneralUtility::fixPermissions($target);
		}
		//
		$item = $GLOBALS['TSFE']->absRefPrefix . $temp;
	}
}
?>
