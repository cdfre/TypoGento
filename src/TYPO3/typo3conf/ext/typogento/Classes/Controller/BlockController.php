<?php

namespace Tx\Typogento\Controller;

use \Tx\Typogento\Configuration\ConfigurationManager;
use \Tx\Typogento\Utility\PluginUtility;

/**
 * Frontend plugin
 * 
 * @author Artus Kolanowski <artus@ionoi.net>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class BlockController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {
	
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
	 * Initializes the controller before invoking an action method.
	 *
	 * @return void
	 */
	protected function initializeAction() {
		// skip
		if ($this->dispatcher != null) {
			return;
		}
		// configuration
		$this->configuration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Tx\\Typogento\\Configuration\\ConfigurationManager');
		// merge typoscript
		$this->section = $this->configuration->merge(
			$this->settings['display'], ConfigurationManager::PLUGIN
		);
		// cache flag
		$cache = $this->settings['cache']['disable'] ? false : (bool)$this->configuration->get(
			'cache', true, $this->section
		);
		// content object
		$content = $this->configurationManager->getContentObject();
		// content type
		$type = $content->getUserObjectType();
		// check caching
		if ($type == \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::OBJECTTYPE_USER) {
			// check flag
			if (!$cache) {
				// convert to user int
				$content->convertToUserIntObject();
				return null;
			}
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
	 * Renders the Block.
	 * 
	 * @return void
	 */
	public function indexAction() {
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
						} else if (!($block instanceof \Mage_Core_Block_Abstract)) {
							throw new Exception(sprintf('The Block type "%s" is not supported.', get_class($name)), 1357002619);
						}
						// render html
						$this->view->assign('block', $block->toHtml());
					} else {
						// render body
						$this->view->assign('body', $response->outputBody());
					}
				} catch (\Exception $e) {
					// close interface
					$this->dispatcher->close();
					// re-throw exception
					// @todo extend connfiguration
					throw $e;
				}
				// close interface
				$this->dispatcher->close();
			}
		}
	}
}
?>
