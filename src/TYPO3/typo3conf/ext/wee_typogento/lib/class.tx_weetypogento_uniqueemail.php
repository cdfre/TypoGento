<?php 

class tx_weetypogento_uniqueEmail {
	
	
	public function evaluateFieldValue($value, $is_in, &$set) {
		
		$set = true;
		$pid = t3lib_div::_GP('popViewId');
		$helper = t3lib_div::makeInstance('tx_weetypogento_languageHelper');
		
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTcountRows('email', 
			'fe_users', "deleted = 0 and email='{$value}' and pid= {$pid}"
		);
		
		if ($result > 1) {
			$set = false;
			$message = t3lib_div::makeInstance('t3lib_FlashMessage', null, 
				$helper->getLabel('lib_duplicate_email_error'), t3lib_FlashMessage::ERROR
			);
			t3lib_FlashMessageQueue::addMessage($message);
		}
		
		return $value;
	}
}

?>