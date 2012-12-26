<?php 

namespace Tx\Typogento\Task;

use \Tx\Typogento\Service\SoapService;

/**
 * Clear the cache for Magento API calls (SOAP).
 * 
 * @author Artus Kolanowski <artus@ionoi.net>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class ClearSoapCacheTask extends \TYPO3\CMS\Scheduler\Task\AbstractTask {

	public function execute() {
		try {
			$cache = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Tx\\Typogento\\Service\\CacheService');
			$cache->flushByTag(SoapService::CACHE_TAG);
			return true;
		} catch(\Exception $e) {
			return false;
		}
	}
}
?>