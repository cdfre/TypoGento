<?php 

/**
 * TypoGento frontend interface
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class tx_typogento_interface implements t3lib_Singleton {
	
	protected $_environment = null;
	
	protected $_url = null;
	
	/**
	 * Constructor
	 * 
	 * Initializes the application and processes the frontend request. 
	 * 
	 * @remarks Requires a fully loaded TypoScript template.
	 */
	public function __construct() {
		// initialize framework
		t3lib_div::makeInstance('tx_typogento_autoloader');
		// target url
		$this->_url = $this->_lookup();
		// target environment
		$this->_environment = $this->_buildEnvironment($this->_url);
		// initialize target environment
		$this->_environment->initialize();
		// try to dispatch the target url
		try {
			// start application
			$this->_initialize();
			// dispatch target url
			$this->_dispatch();
		} catch (Exception $e) {
			// deinitialize target environment
			$this->_environment->deinitialize();
			throw tx_typogento_div::exception('lib_request_dispatching_failed_error',
				array($_SERVER['REQUEST_URI']), $e
			);
		}
		// deinitialize target environment
		$this->_environment->deinitialize();
	}
	
	/**
	 * Lookup for the target URL
	 * 
	 * @throws Exception If routing fails
	 */
	protected function _lookup() {
		// try lookup
		try {
			// router
			$router = t3lib_div::makeInstance('tx_typogento_router');
			// route environment
			$target = t3lib_div::makeInstance('tx_typogento_environment');
			// register variables
			$target->register('getVars', $_GET);
			//$target->register('postVars', $_POST);
			$target->register('queryString', $_SERVER['QUERY_STRING']);
			// overwrite variables
			$target->getVars = isset($_GET['tx_typogento'])?$_GET['tx_typogento']:array();
			//$target->postVars = isset($_POST['tx_typogento'])?$_POST['tx_typogento']:array();
			$target->queryString = t3lib_div::implodeArrayForUrl('', $target->getVars, '', false, true);
			// lookup matching route
			return $router->lookup(tx_typogento_router::ROUTE_SECTION_DISPATCH, null, $target);
		} catch (Exception $e) {
			throw tx_typogento_div::exception('lib_target_url_not_resolved_error',
				array(), $e
			);
		}
	}

	/**
	 * Build the target environment
	 * 
	 * @param string $url
	 */
	protected function _buildEnvironment($url) {
		// url components
		$components = parse_url($url);
		$path = $components['path'];
		$query = array();
		parse_str($components['query'], $query);
		// target environment
		$environment = t3lib_div::makeInstance('tx_typogento_environment');
		// register variables
		$environment->register('getVars', $_GET);
		//$environment->register('postVars', $_POST);
		$environment->register('queryString', $_SERVER['QUERY_STRING']);
		$environment->register('requestUri', $_SERVER['REQUEST_URI']);
		// overwrite variables
		$environment->getVars = isset($query)?$query:array();
		//$environment->postVars = isset($_POST['tx_typogento'])?$_POST['tx_typogento']:array();
		$environment->queryString = t3lib_div::implodeArrayForUrl('', $environment->getVars, '', false, true);
		$environment->requestUri = $path.'?'.trim($environment->queryString, '&');
		// return result
		return $environment;
	}
	/**
	 * Process the frontend request
	 * 
	 * @return boolan
	 */
	protected function _dispatch() {
		// try dispatch
		try {
			// get magento application
			$app = Mage::app();
			// get front controller
			$front = Mage::app()->getFrontController();
			// check response type
			$response = $app->getResponse();
			// get current store code
			$code = tx_typogento_div::getFELangStoreCode();
			try {
				// get store by its code
				$store = $app->getStore($code);
				// activate current store
				$app->setCurrentStore($store);
			} catch (Exception $e) {
				tx_typogento_div::throwException('lib_store_not_resolved_error',
					array($code), $e
				);
			}
			// run dispatch
			$front->dispatch();
		} catch (Exception $e) {
			tx_typogento_div::throwException('lib_request_processing_failed_error',
				array($this->_target), $e
			);
		}
	}
	
	/**
	 * Initialize the frontend application
	 *
	 * @param unknown_type $code
	 * @param unknown_type $type
	 * @param unknown_type $options
	 */
	protected function _initialize($code = '', $type = 'store', $options = array()) {
		try {
			// reset magento if initialized before
			Mage::reset();
			// create magento application
			$app = new Typogento_Core_Model_App();
			// load reflection api for property injection
			$class = new ReflectionClass('Mage');
			// inject typogento application
			$property = $class->getProperty('_app');
			$property->setAccessible(true);
			$property->setValue($app);
			// set magento application root
			Mage::setRoot();
			// inject additional stuff :/
			$events = new Varien_Event_Collection();
			$property = $class->getProperty('_events');
			$property->setAccessible(true);
			$property->setValue($events);
			$config = new Mage_Core_Model_Config($options);
			$property = $class->getProperty('_config');
			$property->setAccessible(true);
			$property->setValue($config);
			// init magento application
			Varien_Profiler::start('self::app::init');
			$app->init($code, $type, $options);
			Varien_Profiler::stop('self::app::init');
			// ...
			$app->loadAreaPart(Mage_Core_Model_App_Area::AREA_GLOBAL, Mage_Core_Model_App_Area::PART_EVENTS);
			//self::$isInitialized = true;
		} catch (Exception $e) {
			tx_typogento_div::throwException('lib_application_initializing_failed_error',
				array(), $e
			);
		}
	}
	
	/**
	 * Open the target environment
	 * 
	 * @return tx_typogento_interface
	 */
	public function open() {
		//
		$this->_environment->initialize();
		return $this;
	}
	
	/**
	 * Close the target environment
	 *
	 * @return tx_typogento_interface
	 */
	public function close() {
		// 
		$this->_environment->deinitialize();
		return $this;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typogento/lib/class.tx_typogento_interface.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typogento/lib/class.tx_typogento_interface.php']);
}

?>
