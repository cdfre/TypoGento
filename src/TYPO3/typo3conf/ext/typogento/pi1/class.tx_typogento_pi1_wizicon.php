<?php

/**
 * Class that adds the wizard icon.
 *
 * @author Joerg Weller <weller@flagbit.de>
 * @author Artus Kolanowski <artus@ionoi.net>
 * @package TYPO3
 * @subpackage tx_typogento
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class tx_typogento_pi1_wizicon {

	/**
	 * Processing the wizard items array
	 *
	 * @param array $items The wizard items
	 * @return Modified array with wizard items
	 */
	function proc($items) {
		$helper = t3lib_div::makeInstance('tx_typogento_languageHelper');

		$items['plugins_tx_typogento_pi1'] = array(
			'icon' => t3lib_extMgm::extRelPath('typogento').'res/images/wizard-icon.png',
			'title' => $helper->getLabel('pi1_title'),
			'description' => $helper->getLabel('pi1_description'),
			'params' => '&defVals[tt_content][CType]=list&defVals[tt_content][list_type]=typogento_pi1'
		);

		return $items;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typogento/pi1/class.tx_typogento_pi1_wizicon.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typogento/pi1/class.tx_typogento_pi1_wizicon.php']);
}

?>