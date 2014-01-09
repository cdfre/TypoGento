<?php 

/**
 * TypoGento autoloader
 *
 * Overrides the default autoloader.
 *
 * @see \Tx\Typogento\Core\Bootstrap
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class Typogento_Core_Model_Autoload extends Varien_Autoload {
	
	static protected $_inheritance;
	
	/**
	 * Singleton pattern implementation
	 *
	 * @return Typogento_Core_Model_Autoload
	 */
	static public function instance()
	{
		if (!self::$_inheritance) {
			self::$_inheritance = new Typogento_Core_Model_Autoload();
		}
		return self::$_inheritance;
	}
	
	/**
	 * Load class source code
	 *
	 * @param string $class
	 */
	public function autoload($class)
	{
		if ($this->_collectClasses) {
			$this->_arrLoadedClasses[self::$_scope][] = $class;
		}
		if ($this->_isIncludePathDefined) {
			$classFile =  COMPILER_INCLUDE_PATH . DIRECTORY_SEPARATOR . $class;
		} else {
			$classFile = str_replace(' ', DIRECTORY_SEPARATOR, ucwords(str_replace('_', ' ', $class)));
		}
		$classFile.= '.php';
		
		return @include $classFile;
	}
}