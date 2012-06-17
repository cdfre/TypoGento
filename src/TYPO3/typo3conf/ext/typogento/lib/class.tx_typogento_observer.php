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
	 * @var bool
	 */
	protected $_isInitialized = false;
	
	/**
	 * @var tx_typogento_configuration
	 */
	protected $_configuration = null;
	
	
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
	public function renderPreProcess($params, t3lib_pagerenderer &$pObj) {
		// configuration
		$configuration = $this->_configuration;
		// skip
		if (!$configuration->get('header', false)) {
			return;
		}
		// start interface
		try {
			t3lib_div::makeInstance('tx_typogento_interface');
		} catch (Exception $e) {
			// re-throw exception
			// @todo typoscript configuration
			throw $e;
		}
		// skip
		if (Mage::app()->getResponse()->isRedirect()) {
			// invalidate output
			$this->_hasFailed = true;
			return;
		} 
		// start
		try {
			// page header
			$block = $configuration->get('header.block', 'head');
			$header = t3lib_div::makeInstance('tx_typogento_header', $block);
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
			$header->render($compress, $import);
			// register fields
			if ($configuration->get('header.register', false)) {
				$this->_registerHeaderFields();
			}
		} catch (Exception $e) {
			// re-throw exception
			// @todo typoscript configuration
			throw $e;
		}
	}
	
	/**
	 * Validate page cache
	 * 
	 * @param tslib_fe $pObj
	 * @param int $timeOutTime
	 * 
	 * @see renderPreProcess()
	 */
	public function insertPageIncache(tslib_fe &$pObj, $timeOutTime) {
		// invalidate page cache
		try {
			if ($this->_hasFailed) {
				$pObj->clearPageCacheContent();
				error_log('Invalidate '.$_SERVER['REQUEST_URI']);
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
	 * @param tslib_fe $pObj
	 */
	public function configArrayPostProc($params, tslib_fe &$pObj) {
		// skip
		if ($this->_isInitialized) {
			return;
		}
		// start
		try {
			// configuration
			$configuration = $this->_configuration;
			// flexform fields
			if ($configuration->get('content.register', false)) {
				// cache
				if (!$configuration->has('content.register.', tx_typogento_configuration::CACHE)) {
					$this->_prefetchContentFields();
				}
				// load
				$register = $configuration->get('content.register.', array(), tx_typogento_configuration::CACHE);
				// register
				if (!empty($register)) {
					$this->_registerContentFields();
				}
			}
			// success
			$this->_isInitialized = true;
		} catch(Exception $e) {
			throw tx_typogento_div::exception('lib_initalizing_routing_system_failed_error', 
				array($_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']), $e
			);
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
	
	/**
	 * Load 'default' content plugin configuration into page configuration
	 * 
	 * @see _registerContentFields()
	 */
	protected function _prefetchContentFields() {
		// configuration
		$configuration = $this->_configuration;
		// flexform fields
		$fields = (string)$configuration->get('content.register.fields', 'id,route,controller,action,cache');
		$fields = explode(',', $fields);
		// skip
		if (count($fields) < 1) {
			return;
		}
		// flexform selectors
		$column = (int)$configuration->get('content.register.column', 0);
		$position = (int)$configuration->get('content.register.position', 0);
		$type = 'typogento_pi1';
		$page = $GLOBALS['TSFE'];
		// select
		$flexform = &tx_typogento_div::getContentFlexForm($page, $type, $column, $position);
		// skip
		if (count($flexform) < 1) {
			$configuration->set('content.register.', array(), tx_typogento_configuration::CACHE);
			return;
		}
		// transform
		$registers = t3lib_div::makeInstance('tx_typogento_pi1_helper')->getFlexFormConfiguration($flexform);
		$registers = &tx_typogento_div::getFlatArray($registers, 'tx_typogento.content.');
		// cache
		$configuration->set('content.register.', $registers, tx_typogento_configuration::CACHE);
	}
	
	/**
	 * Load 'default' content plugin configuration into page register
	 * 
	 */
	protected function _registerHeaderFields() {
		// configuration
		$configuration = $this->_configuration;
		// header fields
		$fields = (string)$configuration->get('header.register.fields', 'title,description,keywords');
		$fields = explode(',', $fields);
		// skip
		if (count($fields) < 1) {
			return;
		}
		// header block
		$block = $configuration->get('header.block', 'head');
		$block = Mage::app()->getLayout()->getBlock($block);
		// result
		$registers = array();
		// collect
		foreach ($fields as $field) {
			if ($block->hasData($field)) {
				$registers[$field] = (string)$block->getData($field);
			}
		}
		// transform
		$registers = &tx_typogento_div::getFlatArray($registers, 'tx_typogento.header');
		// publish
		$GLOBALS['TSFE']->register += $registers;
	}
	
	/**
	 * Load 'default' content plugin configuration into page register
	 * 
	 * @see _prefetchContentFields()
	 */
	protected function _registerContentFields() {
		// configuration
		$configuration = $this->_configuration;
		// collect
		$registers = &$configuration->get('content.register.', array(), tx_typogento_configuration::CACHE);
		// publish
		$GLOBALS['TSFE']->register += $registers;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONFVARS[TYPO3_MODE]['XCLASS']['ext/typogento/lib/class.tx_typogento_observer.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typogento/lib/class.tx_typogento_observer.php']);
}

?>
