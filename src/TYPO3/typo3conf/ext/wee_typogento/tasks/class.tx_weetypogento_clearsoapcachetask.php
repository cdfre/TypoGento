<?php

/**
 * 
 *
 */
class tx_weetypogento_clearSoapCacheTask extends tx_scheduler_Task {
	
	public function execute() {
		try {
			$cache = t3lib_div::makeInstance('tx_weetypogento_cache');
			$cache->flushByTag(tx_weetypogento_soapinterface::CACHE_TAG);
			return true;
		} catch(Exception $e) {
			return false;
		}
	}
}

?>