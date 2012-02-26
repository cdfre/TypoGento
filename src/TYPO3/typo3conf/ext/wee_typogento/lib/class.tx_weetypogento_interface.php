<?php 

/**
 * TypoGento frontend interface
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class tx_weetypogento_interface implements t3lib_Singleton {
	
	protected $_environment = null;
	
	protected $_target = null;
	
	/**
	 * Constructor
	 * 
	 * Initializes the application and processes the frontend request. 
	 * 
	 * @remarks Requires a fully loaded TypoScript template.
	 */
	public function __construct() {
		// init magento if it's not already done
		t3lib_div::makeInstance('tx_weetypogento_autoloader');
		//
		$this->_target = $this->_getTarget();
		//
		$this->_environment = $this->_getEnvironment($this->_target);
		//
		$this->_environment->initialize();
		
		try {
			//
			$this->_initialize();
			//
			$this->_dispatch();
		} catch (Exception $e) {
			$this->_environment->deinitialize();
			tx_weetypogento_div::throwException('lib_dispatching_failed_error',
				array($_SERVER['REQUEST_URI']), $e
			);
		}
		$this->_environment->deinitialize();
	}
	
	/**
	 * Get the Magento target for the current frontend request
	 * 
	 * @throws Exception If routing fails
	 */
	protected function _getTarget() {
		// lookup magento action for the current typo3 page
		try {
			$router = t3lib_div::makeInstance('tx_weetypogento_router');
			$target = t3lib_div::makeInstance('tx_weetypogento_routeEnvironment');
			$target->register('getVars', $_GET);
			//$target->register('postVars', $_POST);
			$target->register('queryString', $_SERVER['QUERY_STRING']);
			$target->getVars = isset($_GET['tx_weetypogento'])?$_GET['tx_weetypogento']:array();
			//$target->postVars = isset($_POST['tx_weetypogento'])?$_POST['tx_weetypogento']:array();
			$target->queryString = t3lib_div::implodeArrayForUrl('', $target->getVars, '', false, true);
			// lookup for matching typogento route
			return $router->lookup(tx_weetypogento_router::ROUTE_SECTION_DISPATCH, null, $target);
		} catch (Exception $e) {
			tx_weetypogento_div::throwException('lib_unresolved_target_url_error',
				array(), $e
			);
		}
	}

	/**
	 * Get environment for the the Magento target
	 * 
	 * @param string $url
	 */
	protected function _getEnvironment($url) {
		// get url components path and query
		$components = parse_url($url);
		$path = $components['path'];
		parse_str($components['query'], $query);
		// 
		$environment = t3lib_div::makeInstance('tx_weetypogento_routeEnvironment');
		$environment->register('getVars', $_GET);
		//$environment->register('postVars', $_POST);
		$environment->register('queryString', $_SERVER['QUERY_STRING']);
		$environment->register('requestUri', $_SERVER['REQUEST_URI']);
		$environment->getVars = isset($query)?$query:array();
		//$environment->postVars = isset($_POST['tx_weetypogento'])?$_POST['tx_weetypogento']:array();
		$environment->queryString = t3lib_div::implodeArrayForUrl('', $environment->getVars, '', false, true);
		$environment->requestUri = $path.'?'.trim($environment->queryString, '&');
		return $environment;
	}
	/**
	 * Process the frontend request
	 * 
	 * @return boolan
	 */
	protected function _dispatch() {
		
		// dispatching current typo3 page
		try {
			// get magento application
			$app = Mage::app();
			// get front controller
			$front = Mage::app()->getFrontController();
			// check response type
			$response = $app->getResponse();
			
			// get current store code
			$code = tx_weetypogento_div::getFELangStoreCode();
			// get store by its code
			$store = $app->getStore($code);
			// activate current store
			$app->setCurrentStore($store);
			// create magento router
			//$router = new Wee_Typogento_Controller_Router();
			// set routes for the magento router
			//$router->collectRoutes('frontend', 'standard');
			// add magento router to the front controller
			//$front->addRouter('standard', $router);
			// run dispatch
			$front->dispatch();
		} catch (Exception $e) {
			tx_weetypogento_div::throwException('lib_processing_failed_error',
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
			$app = new Wee_Typogento_Model_App();
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
			tx_weetypogento_div::throwException('lib_application_initializing_failed_error',
				array(), $e
			);
		}
	}
	
	/**
	 * Get Magento layout block by its name
	 *
	 * @param string $identifier
	 * @return Mage_Core_Block_Abstract
	 */
	public function getBlock($name) {
		//
		$layout = Mage::app()->getLayout();
		
		$block = $layout->getBlock($name);
	
		if ($block instanceof Mage_Core_Block_Abstract) {
			return $block;
		} else {
			return null;
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wee_typogento/lib/class.tx_weetypogento_interface.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wee_typogento/lib/class.tx_weetypogento_interface.php']);
}

?>
