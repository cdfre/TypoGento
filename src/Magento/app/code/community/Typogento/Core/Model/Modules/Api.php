<?php

/**
 * TypoGento modules API model
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class Typogento_Core_Model_Modules_Api extends Mage_Api_Model_Resource_Abstract {
	
	/**
	 * Retrieve modules list
	 *
	 * @return array
	 */
	public function items() {

		/* @var $routes Mage_Core_Model_Config_Element */
		$routes = Mage::getConfig()->getNode('frontend/routers')->xpath('//frontName');
			
		$res = array();
		foreach ($routes as $item) {
			$res[] = (string) $item;
		}
		return $res;
	}

	/**
	 * Retrieve actions list
	 *
	 * @param string $frontName
	 * @param string $controllerName
	 * @return array
	 */
	public function actions($frontName, $controllerName) {
		 
		$module = $this->getModuleByFrontName($frontName);
		if(!$module) return array();

		$controllerPath = Mage::getModuleDir('controllers', $module);
		$controllerFile = $controllerPath.DS.ucfirst($controllerName).'Controller.php';
		if(!file_exists($controllerFile)) return array();
		 
		require_once($controllerFile);

		$controllerClass = $module.'_'.ucfirst($controllerName).'Controller';
		 
		$methods = (array) get_class_methods($controllerClass);
		$actions = array();
		foreach ($methods as $method){

			if(substr($method, -6) != 'Action') continue;
			$actions[] = strtolower(preg_replace('/([A-Z]+)/','_\\1',substr($method, 0, (strlen($method)-6))));
		}
		return $actions;
	}

	/**
	 * Retrieve controllers list
	 *
	 * @param string $frontName
	 * @return array
	 */
	public function controllers($frontName) {
		if(!$frontName) return array();

		$module = $this->getModuleByFrontName($frontName);
		if(!$module) return array();

		$controllerPath = Mage::getModuleDir('controllers', $module);
		 
		$controllerDirectory = dir($controllerPath);
		$controllers = array();

		while (false !== ($entry = $controllerDirectory->read())) {
			if(!strstr($entry, 'Controller.php')) continue;
			$controllers[] = strtolower(str_replace('Controller.php', '', $entry));
		}
		$controllerDirectory->close();
		 
		return $controllers;
	}

	/**
	 * Get module by front name
	 *
	 * @param string $frontName
	 * @return string
	 */
	protected function getModuleByFrontName($frontName) {
		 
		/* @var $routes Mage_Core_Model_Config_Element */
		$routes = Mage::getConfig()->getNode('frontend/routers');

		foreach($routes->children() as $route){

			if((string) $route->args->frontName == $frontName){
				return (string) $route->args->module;
			}
		}
	}

}