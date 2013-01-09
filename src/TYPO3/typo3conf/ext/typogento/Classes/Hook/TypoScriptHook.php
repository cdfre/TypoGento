<?php

namespace Tx\Typogento\Hook;

use \Tx\Typogento\Configuration\ConfigurationManager;
use \Tx\Typogento\ViewHelper\PageHeaderViewHelper;
use \Tx\Typogento\Core\Bootstrap;
use \Tx\Typogento\Utility\LogUtility;

/**
 * Frontend hooks
 * 
 * Observes TYPO3 frontend hooks.
 * 
 * @author Artus Kolanowski <artus@ionoi.net>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class TypoScriptHook implements \TYPO3\CMS\Core\SingletonInterface {
	
	/**
	 * @var boolean
	 */
	protected $isCacheable = true;
	
	/**
	 * @var \Tx\Typogento\Configuration\ConfigurationManager
	 */
	protected $configuration = null;
	
	/**
	 * @var \Tx\Typogento\ViewHelper\PageHeaderViewHelper
	 */
	protected $view = null;
	
	/**
	 * @var \Tx\Typogento\Core\Dispatcher
	 */
	protected $dispatcher = null;
	
	/**
	 * @var array
	 */
	protected $registers = null;
	
	/**
	 * Initializes the view helper.
	 * 
	 * @throws Exception
	 */
	protected function initialize() {
		// skip
		if ($this->view != null) {
			return;
		}
		// skip
		if (!(bool)$this->configuration->get('header.enable', false)
			|| (bool)$this->configuration->get('disableAllHeaderCode', false, ConfigurationManager::SYSTEM)) {
			return;
		}
		// interface
		if ($this->dispatcher == null) {
			try {
				// initialize
				$this->dispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Tx\\Typogento\\Core\\Dispatcher');
				$this->dispatcher->dispatch();
			} catch (\Exception $e) {
				// re-throw exception
				// @todo typoscript configuration
				throw $e;
			}
		}
		// skip
		if (\Mage::app()->getResponse()->isRedirect()) {
			$this->isCacheable = false;
			return;
		}
		// header
		if ($this->view == null) {
			try {
				$block = $this->configuration->get('header.block', 'head');
				$this->view = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Tx\\Typogento\\ViewHelper\\PageHeaderViewHelper', $block);
			} catch(\Exception $e) {
				// re-throw exception
				// @todo typoscript configuration
				throw $e;
			}
		}
	}
	
	/**
	 * Initializes the configuration manager.
	 */
	public function __construct() {
		$this->configuration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Tx\\Typogento\\Configuration\\ConfigurationManager');
	}
	
	/**
	 * Renders the page header.
	 *
	 * @param array $params
	 * @param \TYPO3\CMS\Core\Page\PageRenderer $renderer
	 */
	public function renderPreProcess($params, \TYPO3\CMS\Core\Page\PageRenderer &$renderer) {
		// initialize
		$this->initialize();
		// skip
		if ($this->view == null) {
			return;
		}
		// render
		try {
			// configuration
			$configuration = $this->configuration;
			// render flags
			$compress = 0;
			$import = 0;
			// configuration
			if ($configuration->get('header.compressJs', false)) {
				$compress ^= PageHeaderViewHelper::COMPRESS_JS;
			}
			if ($configuration->get('header.compressCss', false)) {
				$compress ^= PageHeaderViewHelper::COMPRESS_CSS;
			}
			if ($configuration->get('header.importJs', false)) {
				$import ^= PageHeaderViewHelper::IMPORT_JS;
			}
			if ($configuration->get('header.importCss', false)) {
				$import ^= PageHeaderViewHelper::IMPORT_CSS;
			}
			// render header
			$this->view->render($renderer, $compress, $import);
		} catch (\Exception $e) {
			// re-throw exception
			// @todo typoscript configuration
			throw $e;
		}
	}
	
	/**
	 * Validates the page cache.
	 * 
	 * @param \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController $controller
	 * @param int $timeOutTime
	 * 
	 * @see _initialize()
	 */
	public function insertPageInCache(\TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController &$controller, $timeOutTime) {
		// invalidate page cache
		try {
			// check
			if (!$this->isCacheable) {
				// log
				LogUtility::notice(sprintf('[Rendering] Clearing cache entry for non-cacheable request "%s".'), \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL'));
				// invalidate
				$controller->clearPageCacheContent();
				// reset
				$this->isCacheable = true;
			}
		} catch (\Exception $e) {
			// skip exception
		}
	}
	
	/**
	 * Prepares page configuration register.
	 * 
	 * @param array $params
	 * @param \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController $controller
	 */
	public function configArrayPostProc($params, \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController &$controller) {
		// skip initialized
		if ($this->dispatcher != null
			|| $this->registers != null) {
			return;
		}
		// providers
		$providers = array(
			'Tx\\Typogento\\Configuration\\TypoScript\\Register\\HeaderFieldsProvider',
			'Tx\\Typogento\\Configuration\\TypoScript\\Register\\ContentFieldsProvider'
		);
		// registers
		$this->registers = array();
		// pre load registers
		foreach ($providers as $provider) {
			$register = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance($provider, $controller);
			$register->preLoad();
			$this->registers[] = $register;
		}
		// search user int plugins
		if ($controller->isINTincScript()) {
			// include scripts
			$scripts = $controller->config['INTincScript'];
			// search
			foreach ($scripts as &$script) {
				if (isset($script['file'])
					&& strpos($script['file'], 'tx_typogento_pi1') !== false) {
					$hasUncachedPlugins = true;
					break;
				}
			}
		}
		// skip cached content
		if (is_array($controller->config)
			&& !isset($hasUncachedPlugins) 
			&& !$controller->forceTemplateParsing) {
			return;
		}
		// initialize interface and header
		$this->initialize();
		// skip uninitialized
		if ($this->dispatcher == null) {
			return;
		}
		// post load registers
		foreach ($this->registers as $register) {
			$register->postLoad();
		}
	}
}
?>
