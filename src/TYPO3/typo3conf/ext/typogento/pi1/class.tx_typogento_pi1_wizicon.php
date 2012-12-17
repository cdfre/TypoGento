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
	 * @param array $wizardItems: The wizard items
	 * @return Modified array with wizard items
	 */
	function proc($wizardItems) {
		global $LANG;

		$LL = $this->includeLocalLang();

		$wizardItems['plugins_tx_typogento_pi1'] = array(
			'icon'=>t3lib_extMgm::extRelPath('typogento').'pi1/ce_wiz.gif',
			'title'=>$LANG->getLLL('pi1_title',$LL),
			'description'=>$LANG->getLLL('pi1_plus_wiz_description',$LL),
			'params'=>'&defVals[tt_content][CType]=list&defVals[tt_content][list_type]=typogento_pi1'
		);

		return $wizardItems;
	}

	/**
	 * Reads the [extDir]/locallang.xml and returns the $LOCAL_LANG array found in that file.
	 *
	 * @return The array with language labels
	 */
	function includeLocalLang() {
		$language = $GLOBALS['LANG']->lang;
		$resource = t3lib_extMgm::extPath('typogento').'locallang.xml';
		
		if (t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version) >= 4006000) {
			$parser = t3lib_div::makeInstance('t3lib_l10n_parser_Llxml');
			return $parser->getParsedData($resource, $language);
		} else {
			return t3lib_div::readLLXMLfile($resource, $language);
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typogento/pi1/class.tx_typogento_pi1_wizicon.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typogento/pi1/class.tx_typogento_pi1_wizicon.php']);
}

?>