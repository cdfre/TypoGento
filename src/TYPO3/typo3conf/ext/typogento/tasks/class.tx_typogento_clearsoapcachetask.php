<?php

/**
 * 
 *
 */
class tx_typogento_clearSoapCacheTask extends tx_scheduler_Task {
	
	public function execute() {
		try {
			$cache = t3lib_div::makeInstance('tx_typogento_cache');
			$cache->flushByTag(tx_typogento_soapinterface::CACHE_TAG);
			return true;
		} catch(Exception $e) {
			return false;
		}
	}
}

?>