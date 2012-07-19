<?php

/**
 * Frontend observer
 * 
 * Observes TYPO3 frontend hooks.
 * 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class tx_typogento_observer implements t3lib_Singleton {
	
	/**
	 * @var bool
	 */
	protected $_hasFailed = false;
	
	/**
	 * @var tx_typogento_configuration
	 */
	protected $_configuration = null;
	
	/**
	 * @var tx_typogento_header
	 */
	protected $_header = null;
	
	/**
	 * @var tx_typogento_interface
	 */
	protected $_interface = null;
	
	/**
	 * @var array
	 */
	protected $_registers = null;
	
	
	/**
	 * Constructor
	 * 
	 */
	public function __construct() {
		$this->_configuration = t3lib_div::makeInstance('tx_typogento_configuration');
	}
	
	/**
	 * Start autoloader
	 *
	 * @deprecated
	 * @see http://forge.typo3.org/projects/typo3v4-core/repository/revisions/3e18ab8726e5586d9ef8888ffce49a6cf7e03b53
	 */
	public function preprocessRequest($params, &$pObj) {
		// init magento
		//t3lib_div::makeInstance('tx_typogento_autoloader');
	}
	
	/**
	 * Render page header
	 *
	 * @param array $params
	 * @param t3lib_pagerenderer $pObj
	 */
	public function renderPreProcess($params, t3lib_PageRenderer &$renderer) {
		// initialize
		$this->_initialize();
		// skip
		if ($this->_header == null) {
			return;
		}
		// render
		try {
			// configuration
			$configuration = $this->_configuration;
			// header
			$header = $this->_header;
			// render flags
			$compress = 0;
			$import = 0;
			// configuration
			if ($configuration->get('header.compressJs', false)) {
				$compress ^= tx_typogento_header::COMPRESS_JS;
			}
			if ($configuration->get('header.compressCss', false)) {
				$compress ^= tx_typogento_header::COMPRESS_CSS;
			}
			if ($configuration->get('header.importJs', false)) {
				$import ^= tx_typogento_header::IMPORT_JS;
			}
			if ($configuration->get('header.importCss', false)) {
				$import ^= tx_typogento_header::IMPORT_CSS;
			}
			// render header
			$header->render($renderer, $compress, $import);
		} catch (Exception $e) {
			// re-throw exception
			// @todo typoscript configuration
			throw $e;
		}
	}
	
	/**
	 * Validate page cache
	 * 
	 * @param tslib_fe $frontend
	 * @param int $timeOutTime
	 * 
	 * @see _initialize()
	 */
	public function insertPageIncache(tslib_fe &$frontend, $timeOutTime) {
		// invalidate page cache
		try {
			// check
			if ($this->_hasFailed) {
				// log
				t3lib_div::sysLog('Page rendering has failed for the request "'
					. t3lib_div::getIndpEnv('TYPO3_REQUEST_URL')
					. '" Clearing the cache content.', 'typogento',
					t3lib_div::SYSLOG_SEVERITY_WARNING
				);
				// invalidate
				$frontend->clearPageCacheContent();
				// reset
				$this->_hasFailed = false;
			}
		} catch (Exception $e) {
			// skip exception
		}
	}
	
	/**
	 * Prepare page configuration register
	 * 
	 * @param array $params
	 * @param tslib_fe $frontend
	 */
	public function configArrayPostProc($params, tslib_fe &$frontend) {
		// skip initialized
		if ($this->_interface != null
			|| $this->_registers != null) {
			return;
		}
		// providers
		$providers = array(
			'tx_typogento_register_header',
			'tx_typogento_register_content'
		);
		// registers
		$this->_registers = array();
		// pre load registers
		foreach ($providers as $provider) {
			$register = t3lib_div::makeInstance($provider, $frontend);
			$register->preLoad();
			$this->_registers[] = $register;
		}
		// search user int plugins
		if ($frontend->isINTincScript()) {
			// include scripts
			$scripts = $frontend->config['INTincScript'];
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
		if (is_array($frontend->config)
			&& !isset($hasUncachedPlugins) 
			&& !$frontend->forceTemplateParsing) {
			return;
		}
		// initialize interface and header
		$this->_initialize();
		// skip uninitialized
		if ($this->_interface == null) {
			return;
		}
		// post load registers
		foreach ($this->_registers as $register) {
			$register->postLoad();
		}
	}
	
	/**
	 * Logout customer session
	 *
	 * @param array $params
	 * @param t3lib_userAuth $pObj
	 */
	public function logoffPreProcessing($params, &$pObj) {
		// skip if not logout
		if (t3lib_div::_GP('logintype') != 'logout'
			|| $pObj->loginType != 'FE') {
			return;
		}
		// init magento
		t3lib_div::makeInstance('tx_typogento_autoloader');
		//
		Mage::app();
		// load the frontend session
		Mage::getSingleton('core/session', array('name' => 'frontend'));
		// get session
		$session = Mage::getModel('customer/session');
		// logout if session is logged in
		$session->logout();
	}
	
	protected function _initialize() {
		// skip
		if ($this->_header != null) {
			return;
		}
		// skip
		if (!(bool)$this->_configuration->get('header', false)
			|| (bool)$this->_configuration->get('disableAllHeaderCode', false, tx_typogento_configuration::SYSTEM)) {
			return;
		}
		// interface
		if ($this->_interface == null) {
			try {
				// initialize
				$this->_interface = t3lib_div::makeInstance('tx_typogento_interface');
			} catch (Exception $e) {
				// re-throw exception
				// @todo typoscript configuration
				throw $e;
			}
		}
		// skip
		if (Mage::app()->getResponse()->isRedirect()) {
			// invalidate output
			$this->_hasFailed = true;
			return;
		}
		// header
		if ($this->_header == null) {
			try {
				$block = $this->_configuration->get('header.block', 'head');
				$this->_header = t3lib_div::makeInstance('tx_typogento_header', $block);
			} catch (Exception $e) {
				// re-throw exception
				// @todo typoscript configuration
				throw $e;
			}
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONFVARS[TYPO3_MODE]['XCLASS']['ext/typogento/lib/class.tx_typogento_observer.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typogento/lib/class.tx_typogento_observer.php']);
}

?>
