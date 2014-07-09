<?php

namespace Tx\Typogento\Controller;

use Tx\Typogento\Configuration\ConfigurationManager;
use Tx\Typogento\Utility\PluginUtility;

/**
 * The default frontend plugin.
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
		// merge settings
		$this->section = $this->configuration->merge($this->settings, ConfigurationManager::PLUGIN);
		// content object
		$content = $this->configurationManager->getContentObject();
		// check caching
		if ($content->getUserObjectType() == \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::OBJECTTYPE_USER 
			&& !(bool)$this->configuration->get('cache', true, $this->section)) {
			// convert to user int
			$content->convertToUserIntObject();
			return null;
		}
		// initialize dispatcher
		try {
			$this->dispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Tx\\Typogento\\Core\\Dispatcher');
			$this->dispatcher->dispatch();
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
				// open dispatcher
				$this->dispatcher->getEnvironment()->initialize();
				// render content
				try {
					// application
					$application = \Mage::app();
					// response
					$response = $application->getResponse();
					// layout
					$layout = $application->getLayout();
					// render block
					if ($this->configuration->get('mode', 'block', $this->section) == 'block'
						&& !$response->isXmlHttpResponse()) {
						// block name
						$name = $this->configuration->get('block', 'content', $this->section);
						// retrive block
						$block = $layout->getBlock($name);
						// check block
						if (!$block) {
							throw new \Exception(sprintf('The block "%s" was not rendered for the requested URL "%s".', $name, $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']), 1357002550);
						} else if (!($block instanceof \Mage_Core_Block_Abstract)) {
							throw new \Exception(sprintf('The Block type "%s" is not supported.', get_class($name)), 1357002619);
						}
						// render raw block
						$this->view->assign('magento', array('block' => $block->toHtml()));
					} else {
						// render raw page
						$this->view->assign('magento', array('page' => $response->outputBody()));
					}
				} catch (\Exception $e) {
					// close dispatcher
					$this->dispatcher->getEnvironment()->deinitialize();
					// re-throw exception
					// @todo extend connfiguration
					throw $e;
				}
				// close dispatcher
				$this->dispatcher->getEnvironment()->deinitialize();
			}
		}
	}
}
?>
