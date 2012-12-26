<?php 

namespace Tx\Typogento\Core;

/**
 * Environment
 *
 * Allows to change and restore (global) variables.
 * 
 * @author Artus Kolanowski <artus@ionoi.net>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class Environment {
	
	/**
	 * 
	 * @var array
	 */
	protected static $stack = array();
	
	/**
	 * 
	 * @var boolean
	 */
	protected $isInitialized = false;
	
	/**
	 * 
	 * @var array
	 */
	protected $references = array();
	
	/**
	 * 
	 * @var array
	 */
	protected $current = array();
	
	/**
	 * 
	 * @var array
	 */
	protected $preserved = array();
	
	/**
	 * Gets the new value of a registered variable.
	 * 
	 * @param string $name
	 * @return multitype:
	 */
	public function &__get($name) {
		return $this->current[$name];
	}
	
	/**
	 * Sets the new value for a registered variable.
	 * 
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set($name, $value) {
		$this->current[$name] = $value;
	}
	
	/**
	 * Register a variable.
	 * 
	 * @param string $name
	 * @param mixed $value
	 */
	public function register($name, &$value) {
		$this->references[$name] = &$value;
		$this->current[$name] = $value;
		$this->preserved[$name] = $value;
	}
	
	/**
	 * Changes the variables to their new values.
	 */
	public function initialize() {
		// skip
		if ($this->isInitialized) {
			return;
		}
		// push stack
		self::$stack[] = $this;
		// 
		foreach ($this->current as $key => &$value) {
			$this->references[$key] = $value;
		}
		// block initialize
		$this->isInitialized = true;
	}
	
	/**
	 * Restores the old values of the registered variables.
	 * 
	 * @throws Exception
	 */
	public function deinitialize() {
		// skip
		if (!$this->isInitialized) {
			return;
		}
		// check order
		if (end(self::$stack) !== $this) {
			throw new Exception('Unknown error', 1356845919);
		}
		// 
		foreach ($this->preserved as $key => &$value) {
			$this->references[$key] = $value;
		}
		// pop stack
		array_pop(self::$stack);
		// block deinitialize
		$this->isInitialized = false;
	}
}
?>