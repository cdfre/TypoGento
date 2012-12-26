<?php 

namespace Tx\Typogento\Utility;

/**
 * Localization utility
 * 
 * @namespace Tx\Typogento\Utility
 * @author Artus Kolanowsi <artus@ionoi.net>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class LocalizationUtility implements \TYPO3\CMS\Core\SingletonInterface {
	
	public static function translate($key, $arguments = null) {
		return \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($key, 'typogento', $arguments);
	}
}
?>