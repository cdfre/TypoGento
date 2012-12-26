<?php 

namespace Tx\Typogento\Configuration\TypoScript\Register;

use \Tx\Typogento\Configuration\ConfigurationManager;

/**
 * Abstract TypoScript frontend register provider
 *
 * @author Artus Kolanowski <artus@ionoi.net>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 * @see \Tx\Typogento\Hook\FrontendHook::configArrayPostProc()
 */
abstract class AbstractProvider {

	/**
	 * @var \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
	 */
	protected $frontend = null;

	/**
	 * @var ConfigurationManager
	 */
	protected $configuration = null;

	/**
	 * @var array
	 */
	protected $data = null;

	/**
	 * Constructor
	 *
	 * @param \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController $frontend
	 * @param unknown_type $key
	 */
	public function __construct(\TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController $frontend) {
		// member
		$this->configuration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Tx\\Typogento\\Configuration\\ConfigurationManager');
		$this->frontend = $frontend;
	}

	/**
	 * Gets called before Magento starts.
	 */
	public function preLoad() {
		// skip finished
		if ($this->data !== null) {
			return;
		}
		// pre load data
		$this->onPreLoad();
		// register data
		$this->register();
	}

	/**
	 * Gets called after Magento starts.
	 */
	public function postLoad() {
		// skip finished
		if ($this->data !== null) {
			return;
		}
		// post load data
		$this->onPostLoad();
		// register data
		$this->register();
	}

	/**
	 * Registers data in the frontend.
	 */
	protected function register() {
		// skip unfinished
		if ($this->data === null) {
			return;
		}
		// register data
		$this->frontend->register += $this->data;
	}

	/**
	 * @see AbstractRegister::preLoad()
	 */
	protected abstract function onPreLoad();

	/**
	 * @see AbstractRegister::postLoad()
	 */
	protected abstract function onPostLoad();

}
?>