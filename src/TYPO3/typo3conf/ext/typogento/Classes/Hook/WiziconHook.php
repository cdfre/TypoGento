<?php

namespace Tx\Typogento\Hook;

use \Tx\Typogento\Utility\LocalizationUtility;
/**
 * Class that adds the wizard icon.
 *
 * @author Joerg Weller <weller@flagbit.de>
 * @author Artus Kolanowski <artus@ionoi.net>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class WiziconHook {

	/**
	 * Processing the wizard items array
	 *
	 * @param array $items The wizard items
	 * @return Modified array with wizard items
	 */
	function proc($items) {
		$items['plugins_tx_typogento_pi1'] = array(
			'icon' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('typogento').'Resources/Public/Icons/wizard.png',
			'title' => LocalizationUtility::translate('pi1_title'),
			'description' => LocalizationUtility::translate('pi1_description'),
			'params' => '&defVals[tt_content][CType]=list&defVals[tt_content][list_type]=typogento_pi1'
		);

		return $items;
	}
}
?>