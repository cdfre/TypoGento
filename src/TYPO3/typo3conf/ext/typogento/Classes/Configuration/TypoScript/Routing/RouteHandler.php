<?php 

namespace Tx\Typogento\Configuration\TypoScript\Routing;

use \Tx\Typogento\Utility\GeneralUtility;

/**
 * Default TypoScript route handler
 *
 * Default route handler implementation using the TypoScript function 'typolink'.
 * 
 * @author Artus Kolanowski <artus@ionoi.net>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class RouteHandler implements \Tx\Typogento\Core\Routing\RouteHandlerInterface {

	/**
	 * @var array
	 */
	protected $typoscript = null;

	/**
	 * @var array
	 */
	protected $postProcessing = null;

	/**
	 * @var array
	 */
	protected $preservedHooks = null;

	/**
	 * Constructor
	 * 
	 * @param array $typoscript The TypoScript configuration.
	 */
	public function __construct(array $typoscript) {
		$this->typoscript = $typoscript;
		
		$this->postProcessing = array('obj' => $this, 'method' => 'postProcess');
	}

	/**
	 * Generates an URL using the TypoScript function 'typolink'.
	 * 
	 * @see \Tx\Typogento\Core\Routing\RouteHandlerInterface::process()
	 * @return string The generated URL.
	 */
	public function process() {
		$renderer = GeneralUtility::getContentObject();
		$hooks = &$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_content.php']['typoLink_PostProc'];
		$persistent = &$GLOBALS['T3_VAR']['callUserFunction'];
		$exception = null;
		// replace typolink post processing hooks
		$this->preservedHooks = $this->replace($hooks, array(__CLASS__.'::postProcess'));
		$this->replace($persistent[__CLASS__.'::postProcess'], $this->postProcessing);
		// render typolink
		try {
			$renderer->typolink('', $this->typoscript);
		} catch (\Exception $e) {
			// restore typolink post processing hooks
			$this->replace($hooks, $this->preservedHooks);
			$this->replace($persistent[__CLASS__.'::postProcess'], null);
			// rethrow the exception
			throw $e;
		}
		// restore typolink post processing hooks
		$this->replace($hooks, $this->preservedHooks);
		$this->replace($persistent[__CLASS__.'::postProcess'], null);
		// get rendered url
		$url = $renderer->lastTypoLinkUrl;
		// return result
		return $url;
	}

	/**
	 * Hook for the TypoScript function 'typolink' (typoLink_PostProc)
	 * 
	 * Provides the property 'additionalParameters' for external URLs.
	 * 
	 * @param array $params
	 * @param \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $renderer
	 * @throws Exception
	 * @internal
	 */
	public function postProcess(array &$params, \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $renderer) {
		$type = $params['finalTagParts']['TYPE'];

		if ($type === 'url') { // typo3 request -> magento action
			// get last url components
			$components = parse_url($renderer->lastTypoLinkUrl);
			// provides .addQueryString and .additionalParams conf
			$components['query'] .= $this->typoscript['addQueryString'] 
				? $renderer->getQueryArguments($this->typoscript['addQueryString.']) 
				: '';
			$components['query'] .= isset($this->typoscript['additionalParams.'])
				? trim($renderer->stdWrap($this->typoscript['additionalParams'], $this->typoscript['additionalParams.']))
				: trim($this->typoscript['additionalParams']);
			if ($components['query'] == '&' || substr($components['query'], 0, 1) != '&') {
				$components['query'] = '';
			}
			// set url using only path and query
			$renderer->lastTypoLinkUrl = $components['path'].'?'.trim($components['query'], '&');
		} else if ($type === 'page') { // magento request -> typo3 page
			// skip if no further hooks were registered
			if (!isset($this->preservedHooks)) {
				return;
			}
			// call the preserved hooks
			foreach ($this->preservedHooks as $hook) {
				\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($hook, $params, $renderer);
			}
		} else {
			throw new Exception(sprintf('The link type "%s" is not supported', $type), 1356840730);
		}
	}

	/**
	 * Helper function for the hooks
	 * 
	 * @param array $source
	 * @param array $replacement
	 * @return array
	 */
	protected function &replace(&$source, $replacement) {
		$preserved = $source;
		$source = $replacement;
		return $preserved;
	}
}
?>