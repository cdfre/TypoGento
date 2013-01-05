<?php 

namespace Tx\Typogento\Core\Routing;

/**
 * Basic route
 * 
 * @author Artus Kolanowski <artus@ionoi.net>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class Route {
	
	/**
	 * @var RouteFilterInterface
	 */
	protected $filter = null;
	
	/**
	 * @var RouteHandlerInterface
	 */
	protected $handler = null;
	
	/**
	 * 
	 * @var string
	 */
	protected $id = null;
	
	/**
	 * @var int
	 */
	protected $priority = null;
	
	public function __construct($id, RouteFilterInterface $filter, RouteHandlerInterface $handler, $priority = 0) {
		$this->id = $id;
		$this->filter = $filter;
		$this->handler = $handler;
		$this->priority = (int)$priority;
	}
	
	public function __clone() {
		$this->filter = clone $this->filter;
		$this->handler = clone $this->handler;
	}
	
	public function __destruct() {
		unset($this->filter);
		unset($this->handler);
	}
	
	public function getFilter() {
		return $this->filter;
	}
	
	public function getHandler() {
		return $this->handler;
	}
	
	public function getPriority() {
		return $this->priority;
	}
	
	public function getId() {
		return $this->id;
	}
}
?>