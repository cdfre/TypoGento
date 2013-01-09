<?php 

namespace Tx\Typogento\Core;

/**
 * Overrides (global) variables without loosing their original values.
 * 
 * @author Artus Kolanowski <artus@ionoi.net>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class Environment {
	
	/**
	 * The section name for the overriden values.
	 * 
	 * @var string
	 */
	const ENVIRONMENT_SECTION_CURRENT = 'current';
	
	/**
	 * The section name for the original values.
	 * 
	 * @var string
	 */
	const ENVIRONMENT_SECTION_PRESERVED = 'preserved';
	
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
	 * Gets the overriden or original value of a registered variable.
	 * 
	 * @param string $name
	 * @param string $section
	 * @return mixed
	 * @throws Exception
	 */
	public function &get($name, $section = self::ENVIRONMENT_SECTION_CURRENT) {
		if (!isset($this->current[$name])) {
			throw new Exception(sprintf('Variable "%s" is not registered.', $name), 1357694792);
		}
		switch ($section) {
			case self::ENVIRONMENT_SECTION_CURRENT:
				return $this->current[$name];
			case self::ENVIRONMENT_SECTION_PRESERVED:
				return $this->preserved[$name];
			default:
				throw new Exception(sprintf('Unknown section %s', $section), 1357693289);
		}
	}
	
	/**
	 * Sets the overriden value of a registered variable.
	 * 
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 * @throws Exception
	 */
	public function set($name, $value) {
		if (!isset($this->current[$name])) {
			throw new Exception(sprintf('Variable "%s" is not registered.', $name), 1357694806);
		}
		$this->current[$name] = $value;
	}
	
	/**
	 * Registers a variable.
	 * 
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 * @throws Exception
	 */
	public function register($name, &$value) {
		if (isset($this->current[$name])) {
			throw new Exception(sprintf('Variable "%s" is already registered.', $name), 1357694657);
		}
		$this->references[$name] = &$value;
		$this->current[$name] = $value;
		$this->preserved[$name] = $value;
	}
	
	/**
	 * Overrides the variables to their new values.
	 * 
	 * @return void
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