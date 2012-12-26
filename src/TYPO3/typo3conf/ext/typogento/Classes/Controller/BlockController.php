<?php

namespace Tx\Typogento\Controller;

use \Tx\Typogento\Configuration\ConfigurationManager;
use \Tx\Typogento\Utility\PluginUtility;

/**
 * Frontend plugin
 * 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class BlockController extends \TYPO3\CMS\Frontend\Plugin\AbstractPlugin {
	
	/**
	 * @var string
	 */
	public $prefixId = 'tx_typogento';
	
	
	/**
	 * @var string
	 */
	public $extKey = 'typogento';
	
	/**
	 * @var \Tx\Typogento\Core\Dispatcher
	 */
	protected $dispatcher = null;
	
	/**
	 * @var \Tx\Typogento\Configuration\ConfigurationManager
	 */
	protected $configuration = null;
	
	/**
	 * @var int
	 */
	protected $section = -1;
	
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
		$this->initialize($typoscript);
		// skip
		if ($this->dispatcher != null) {
			// response
			$response = \Mage::app()->getResponse();
			// check response
			if (!$response->isAvailable()) {
				$GLOBALS['TSFE']->pageNotFoundAndExit();
			} else if (!$response->isRedirect()) {
				// open interface
				$this->dispatcher->open();
				// render content
				try {
					$this->render($content);
				} catch (Exception $e) {
					// close interface
					$this->dispatcher->close();
					// re-throw exception
					// @todo extend connfiguration
					throw $e;
				}
				// close interface
				$this->dispatcher->close();
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
	protected function initialize(array &$typoscript) {
		// skip
		if ($this->dispatcher != null) {
			return;
		}
		// configuration
		$this->configuration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Tx\\Typogento\\Configuration\\ConfigurationManager');
		// merge flexform
		if (isset($this->cObj->data['pi_flexform'])) {
			// reference flexform
			$flexform = &$this->cObj->data['pi_flexform'];
			// transform flexform
			if (!is_array($flexform)) {
				$flexform = \TYPO3\CMS\Core\Utility\GeneralUtility::xml2array($flexform);
				$flexform = PluginUtility::getFlexFormConfiguration($flexform);
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
		$this->section = $this->configuration->merge(
			$typoscript, ConfigurationManager::PLUGIN
		);
		// cache flag
		$cache = (bool)$this->configuration->get(
			'cache', true, $this->section
		);
		// content type
		$type = $this->cObj->getUserObjectType();
		// get/post
		$variables = \TYPO3\CMS\Core\Utility\GeneralUtility::_GPmerged($this->prefixId);
		// check caching
		if ($type == \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::OBJECTTYPE_USER) {
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
			$this->dispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Tx\\Typogento\\Core\\Dispatcher');
		} catch (Exception $e) {
			// re-throw exception
			// @todo typoscript configuration
			throw $e;
		}
	}
	
	/**
	 * Render the plugin
	 * 
	 * @todo http://forge.typo3.org/issues/19809
	 * @param string $content
	 */
	protected function render(&$content) {
		// application
		$application = \Mage::app();
		// response
		$response = $application->getResponse();
		// layout
		$layout = $application->getLayout();
		// configuration
		$configuration = $this->configuration;
		// render block
		if ($configuration->get('mode', 'block', $this->section) == 'block'
			&& !$response->isAjax()) {
			// block name
			$name = $configuration->get(
				'block', 'content', $this->section
			);
			// retrive block
			$block = $layout->getBlock($name);
			// check block
			if (!$block) {
				throw new Exception(sprintf('The block "%s" was not rendered for the requested URL "%s".', $name, $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']), 1357002550);
			} else if (!($block instanceof Mage_Core_Block_Abstract)) {
				throw new Exception(sprintf('The Block type "%s" is not supported.', get_class($name)), 1357002619);
			}
			// render html
			$content .= $block->toHtml();
			// wrap html
			// $content = $this->pi_wrapInBaseClass($content);
		// render response
		} else {
			// render body
			$content .= $response->outputBody();
		}
	}
}
?>
