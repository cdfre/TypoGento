<?php 

namespace Tx\Typogento\Core;

use \Tx\Typogento\Core\Routing\Route;
use \Tx\Typogento\Core\Routing\Router;
use \Tx\Typogento\Utility\GeneralUtility;
use \Tx\Typogento\Core\Bootstrap;

use Mage;

/**
 * Frontend dispatcher
 * 
 * Dispatches the TYPO3 frontend request to Magento, by using the routing configuation.
 *
 * @author Artus Kolanowski <artus@ionoi.net>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class Dispatcher implements \TYPO3\CMS\Core\SingletonInterface {
	
	/**
	 * 
	 * @var \Tx\Typogento\Core\Environment
	 */
	protected $environment = null;
	
	/**
	 * 
	 * @var string
	 */
	protected $url = null;
	
	/**
	 * Constructor
	 * 
	 * Initializes the application and processes the frontend request. 
	 * 
	 * @remarks Requires a fully loaded TypoScript template.
	 */
	public function __construct() {
		// initialize framework
		Bootstrap::initialize();
		// target url
		$this->url = $this->lookupUrl();
		// target environment
		$this->environment = $this->buildEnvironment($this->url);
		// initialize target environment
		$this->environment->initialize();
		// try to dispatch the target url
		try {
			// start application
			$this->initialize();
			// dispatch target url
			$this->dispatch();
		} catch (\Exception $e) {
			// deinitialize target environment
			$this->environment->deinitialize();
			// rethrow exception
			throw new Exception(sprintf('The requested URL "%s" could not be dispatched: %s', $_SERVER['REQUEST_URI'], $e->getMessage()), 1356845349, $e);
		}
		// deinitialize target environment
		$this->environment->deinitialize();
	}
	
	/**
	 * Lookup for the target URL
	 * 
	 * @throws Exception If routing fails
	 */
	protected function lookupUrl() {
		// try lookup
		try {
			// router
			$router = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Tx\\Typogento\\Core\\Routing\\Router');
			// route environment
			$target = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Tx\\Typogento\\Core\\Environment');
			// register variables
			$target->register('getVars', $_GET);
			//$target->register('postVars', $_POST);
			$target->register('queryString', $_SERVER['QUERY_STRING']);
			// overwrite variables
			$target->getVars = isset($_GET['tx_typogento'])?$_GET['tx_typogento']:array();
			//$target->postVars = isset($_POST['tx_typogento'])?$_POST['tx_typogento']:array();
			$target->queryString = \TYPO3\CMS\Core\Utility\GeneralUtility::implodeArrayForUrl('', $target->getVars, '', false, true);
			// lookup matching route
			return $router->lookup(Router::ROUTE_SECTION_DISPATCH, null, $target);
		} catch (\Exception $e) {
			throw new Exception(sprintf('The routing system is unable to resolve the action URL: %s', $e->getMessage()), 1356845494, $e);
		}
	}

	/**
	 * Build the target environment
	 * 
	 * @param string $url
	 */
	protected function buildEnvironment($url) {
		// url components
		$components = parse_url($url);
		$path = $components['path'];
		$query = array();
		parse_str($components['query'], $query);
		// target environment
		$environment = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Tx\\Typogento\\Core\\Environment');
		// register variables
		$environment->register('getVars', $_GET);
		//$environment->register('postVars', $_POST);
		$environment->register('queryString', $_SERVER['QUERY_STRING']);
		$environment->register('requestUri', $_SERVER['REQUEST_URI']);
		// overwrite variables
		$environment->getVars = isset($query)?$query:array();
		//$environment->postVars = isset($_POST['tx_typogento'])?$_POST['tx_typogento']:array();
		$environment->queryString = \TYPO3\CMS\Core\Utility\GeneralUtility::implodeArrayForUrl('', $environment->getVars, '', false, true);
		$environment->requestUri = $path.'?'.trim($environment->queryString, '&');
		// return result
		return $environment;
	}
	/**
	 * Process the frontend request
	 * 
	 * @return boolan
	 */
	protected function dispatch() {
		// try dispatch
		try {
			// get magento application
			$app = Mage::app();
			// get front controller
			$front = Mage::app()->getFrontController();
			// check response type
			$response = $app->getResponse();
			// get current store code
			$code = GeneralUtility::getFELangStoreCode();
			try {
				// get store by its code
				$store = $app->getStore($code);
				// activate current store
				$app->setCurrentStore($store);
			} catch (\Exception $e) {
				throw new Exception(sprintf('The store "%s" could not be resolved: %s', $code, $e->getMessage()), 1356845586, $e);
			}
			// run dispatch
			$front->dispatch();
		} catch (\Exception $e) {
			throw new Exception(sprintf('An error has occurred during processing the action URL "%s": %s', $this->target, $e->getMessage()), 1356845643, $e);
		}
	}
	
	/**
	 * Initialize the frontend application
	 *
	 * @param string $code
	 * @param string $type
	 * @param array $options
	 */
	protected function initialize($code = '', $type = 'store', $options = array()) {
		try {
			// reset magento if initialized before
			Mage::reset();
			// create magento application
			$app = new \Typogento_Core_Model_App();
			// load reflection api for property injection
			$class = new \ReflectionClass('Mage');
			// inject typogento application
			$property = $class->getProperty('_app');
			$property->setAccessible(true);
			$property->setValue($app);
			// set magento application root
			Mage::setRoot();
			// inject additional stuff :/
			$events = new \Varien_Event_Collection();
			$property = $class->getProperty('_events');
			$property->setAccessible(true);
			$property->setValue($events);
			$config = new \Mage_Core_Model_Config($options);
			$property = $class->getProperty('_config');
			$property->setAccessible(true);
			$property->setValue($config);
			// init magento application
			\Varien_Profiler::start('self::app::init');
			$app->init($code, $type, $options);
			\Varien_Profiler::stop('self::app::init');
			// ...
			$app->loadAreaPart(\Mage_Core_Model_App_Area::AREA_GLOBAL, \Mage_Core_Model_App_Area::PART_EVENTS);
			//self::$isInitialized = true;
		} catch (\Exception $e) {
			throw new Exception(sprintf('The following error has occurred during initializing the application: %s', $e->getMessage()), 1356845702, $e);
		}
	}
	
	/**
	 * Open the target environment
	 * 
	 * @return tx_typogento_interface
	 */
	public function open() {
		//
		$this->environment->initialize();
		return $this;
	}
	
	/**
	 * Close the target environment
	 *
	 * @return tx_typogento_interface
	 */
	public function close() {
		// 
		$this->environment->deinitialize();
		return $this;
	}
}
?>
