<?php 

namespace Tx\Typogento\Core;

use \Tx\Typogento\Core\Routing\Route;
use \Tx\Typogento\Core\Routing\Router;
use \Tx\Typogento\Utility\GeneralUtility;
use \Tx\Typogento\Utility\LogUtility;
use \Tx\Typogento\Core\Bootstrap;

/**
 * Dispatches the TYPO3 frontend request to Magento, by using the routing configuation.
 *
 * @author Artus Kolanowski <artus@ionoi.net>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class Dispatcher implements \TYPO3\CMS\Core\SingletonInterface {
	
	/**
	 * @var \Tx\Typogento\Core\Environment
	 */
	protected $environment = null;
	
	/**
	 * @var string
	 */
	protected $url = null;
	
	/**
	 * @var \TYPO3\CMS\Core\Log\LogManager
	 */
	protected $logger = null;
	
	/**
	 * Initializes the application. 
	 * 
	 * @return void
	 */
	public function __construct() {
		// create logger
		$this->logger = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Log\\LogManager')->getLogger(__CLASS__);
		// initialize framework
		Bootstrap::initialize();
		// setup target url
		$this->lookup();
		// setup environment
		$this->build();
		// initialize environment
		$this->environment->initialize();
		// try to dispatch the target url
		try {
			// initialize application
			$this->initialize();
		} catch (\Exception $e) {
			// deinitialize environment
			$this->environment->deinitialize();
			// rethrow exception
			throw $e;
		}
		// deinitialize environment
		$this->environment->deinitialize();
	}
	
	/**
	 * Processes the frontend request.
	 *
	 * @return boolan
	 */
	public function dispatch() {
		// try dispatch
		try {
			// get magento application
			$app = \Mage::app();
			// skip if already dispatched
			if(!$app->getRequest()->isDispatched()) {
				// get current store code
				$code = GeneralUtility::getStoreCode();
				// try activate current store
				try {
					// get store by its code
					$store = $app->getStore($code);
					// activate current store
					$app->setCurrentStore($store);
				} catch (\Exception $e) {
					// rethrow the exception
					throw new Exception(sprintf('The store "%s" could not be resolved: %s', $code, $e->getMessage()), 1356845586, $e);
				}
				// initialize environment
				$this->environment->initialize();
				// try dispatch
				try {
					// run dispatch
					$app->getFrontController()->dispatch();
				} catch (\Exception $e) {
					// deinitialize environment
					$this->environment->deinitialize();
					// rethrow the exception
					throw $e;
				}
				// deinitialize environment
				$this->environment->deinitialize();
			}
		} catch (\Exception $e) {
			// rethrow the exception
			throw new Exception(sprintf('Processing the action URL "%s" has failed: %s', $this->url, $e->getMessage()), 1356845643, $e);
		}
	}
	
	/**
	 * Gets the environment for further usage.
	 * 
	 * @return \Tx\Typogento\Core\Environment
	 */
	public function getEnvironment() {
		return $this->environment;
	}
	
	/**
	 * Lookup for the URL.
	 * 
	 * @throws Exception If routing fails
	 */
	protected function lookup() {
		// try lookup
		try {
			// router
			$router = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Tx\\Typogento\\Core\\Routing\\Router');
			// route environment
			$target = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Tx\\Typogento\\Core\\Environment');
			// register variables
			$target->register('_GET', $_GET);
			$target->register('_POST', $_POST);
			$target->register('QUERY_STRING', $_SERVER['QUERY_STRING']);
			// overwrite variables
			$target->set('_GET', ($_GET['tx_typogento'])?$_GET['tx_typogento']:array());
			$target->set('_POST', isset($_POST['tx_typogento'])?$_POST['tx_typogento']:array());
			$target->set('QUERY_STRING', \TYPO3\CMS\Core\Utility\GeneralUtility::implodeArrayForUrl('', $target->get('_GET'), '', false, true));
			// lookup matching route
			$route = $router->lookup(Router::ROUTE_SECTION_DISPATCH, null);
			// set result
			$this->url = $router->process($route, $target);
			// log result
			$this->logger->debug(
				sprintf(
					'Rewrite URL "%s" to "%s" using dispatch route "%s".', 
					urldecode($_SERVER['QUERY_STRING']), urldecode($this->url), $route->getId()
				), 
				array_merge_recursive($_GET, $_POST)
			);
		} catch (\Exception $e) {
			throw new Exception(sprintf('Unable to resolve the action URL: %s', $e->getMessage()), 1356845494, $e);
		}
	}

	/**
	 * Build the environment.
	 * 
	 */
	protected function build() {
		// url components
		$components = parse_url($this->url);
		$path = $components['path'];
		$query = array();
		parse_str($components['query'], $query);
		// target environment
		$environment = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Tx\\Typogento\\Core\\Environment');
		// register variables
		$environment->register('_GET', $_GET);
		$environment->register('_POST', $_POST);
		$environment->register('QUERY_STRING', $_SERVER['QUERY_STRING']);
		$environment->register('REQUEST_URI', $_SERVER['REQUEST_URI']);
		// overwrite variables
		$environment->set('_GET', isset($query)?$query:array());
		$environment->set('_POST', isset($_POST['tx_typogento'])?$_POST['tx_typogento']:array());
		$environment->set('QUERY_STRING', \TYPO3\CMS\Core\Utility\GeneralUtility::implodeArrayForUrl('', $environment->get('_GET'), '', false, true));
		$environment->set('REQUEST_URI', $path.'?'.trim($environment->get('REQUEST_URI'), '&'));
		// set result
		$this->environment = $environment;
	}
	
	/**
	 * Initialize the frontend application.
	 *
	 * @param string $code
	 * @param string $type
	 * @param array $options
	 */
	protected function initialize($code = '', $type = 'store', $options = array()) {
		try {
			// reset magento if initialized before
			\Mage::reset();
			// create magento application
			$app = new \Typogento_Core_Model_App();
			// load reflection api for property injection
			$class = new \ReflectionClass('Mage');
			// inject typogento application
			$property = $class->getProperty('_app');
			$property->setAccessible(true);
			$property->setValue($app);
			// set magento application root
			\Mage::setRoot();
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
		} catch (\Exception $e) {
			throw new Exception(sprintf('Initializing the application has failed: %s', $e->getMessage()), 1356845702, $e);
		}
	}
}
?>
