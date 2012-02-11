<?php

if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

/**
* Changes caching behaviour of Magento Frontend Plugin
*/
t3lib_extMgm::addPItoST43($_EXTKEY, 'pi1/class.tx_weetypogento_pi1.php','_pi1','list_type', 1);

/**
 *  Extending TypoScript from static template uid=43 to set up userdefined tag
 */
t3lib_extMgm::addTypoScript($_EXTKEY, 'editorcfg', 'tt_content.CSS_editor.ch.tx_weetypogento_pi1 = < plugin.tx_weetypogento_pi1.CSS_editor', 43);

/**
 * 
 */
t3lib_extMgm::addService($_EXTKEY, 'auth' /* sv type */, 'tx_weetypogento_auth_sv1' /* sv key */,
	array(
		'title' => 'Magento Customer Login',
		'description' => 'Login a frontend user automatically if one is found in the Magento customer table.',

		'subtype' => 'getUserFE,authUserFE,getGroupsFE',

		'available' => TRUE,
		'priority' => 60,
		'quality' => 50,

		'os' => '',
		'exec' => '',

		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'lib/class.tx_weetypogento_auth_sv1.php',
		'className' => 'tx_weetypogento_auth_sv1',
	)
);

/**
 * 	
 */
if (!is_array($TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['wee_typogento'])) {
	$TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['wee_typogento'] = array();
}
/**
 * Define string frontend as default frontend, this must be set with TYPO3 4.5 and below 
 * and overrides the default variable frontend of 4.6
 */
if (!isset($TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['wee_typogento']['frontend'])) {
	$TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['wee_typogento']['frontend'] = 't3lib_cache_frontend_VariableFrontend';
}
if (t3lib_div::int_from_ver(TYPO3_version) < '4006000') {
	/**
	 * Define database backend as backend for 4.5 and below (default in 4.6)
	 */
	if (!isset($TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['wee_typogento']['backend'])) {
		$TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['wee_typogento']['backend'] = 't3lib_cache_backend_DbBackend';
	}
	
	/**
	 * Define data and tags table for 4.5 and below (obsolete in 4.6)
	 */
	if (!isset($TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['wee_typogento']['options'])) {
		$TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['wee_typogento']['options'] = array();
	}
	if (!isset($TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['wee_typogento']['options']['cacheTable'])) {
		$TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['wee_typogento']['options']['cacheTable'] = 'tx_weetypogento_cache';
	}
	if (!isset($TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['wee_typogento']['options']['tagsTable'])) {
		$TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['wee_typogento']['options']['tagsTable'] = 'tx_weetypogento_cache_tags';
	}
}

/**
 * Setup System Hooks
 */
if (TYPO3_MODE === 'FE') {
	
	/**
	 *
	 */
	$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['logoff_pre_processing']['wee_typogento'] =
		'EXT:wee_typogento/lib/class.tx_weetypogento_observer.php:tx_weetypogento_observer->logoffPreProcessing';
	
	/**
	 * Improves TypoGento automatic header integration
	 */
	$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-preProcess']['wee_typogento'] =
			'EXT:wee_typogento/lib/class.tx_weetypogento_observer.php:&tx_weetypogento_observer->renderPreProcess';
	
	/**
	 * Improves internal FlexForm access and its caching
	 */
	$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['configArrayPostProc']['wee_typogento'] =
			'EXT:wee_typogento/lib/class.tx_weetypogento_observer.php:&tx_weetypogento_observer->configArrayPostProc';
	
	/**
	 * 
	 */
	$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_content.php']['getData']['wee_typogento'] =
			'EXT:wee_typogento/lib/class.tx_weetypogento_observer.php:&tx_weetypogento_observer';

} elseif (TYPO3_MODE == 'BE') {
	 
	/**
	 * Register the cache in backend so it will be cleared with "clear all caches"
	 */
	try {
		t3lib_cache::initializeCachingFramework();
		// State cache
		$GLOBALS['typo3CacheFactory']->create(
			'wee_typogento',
			$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['wee_typogento']['frontend'],
			$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['wee_typogento']['backend'],
			$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['wee_typogento']['options']
		);
		 
	} catch(t3lib_cache_exception_NoSuchCache $exception) {
		 
	}
}
/**
 * Adds RealURL auto configuration
 *
 * @todo
 */
//if($_EXTCONF['realurl']){
//require_once($_EXTPATH.'lib/class.tx_weetypogento_realurl.php');
//$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/realurl/class.tx_realurl_autoconfgen.php']['extensionConfiguration']['wee_typogento'] = 'EXT:wee_typogento/lib/class.tx_weetypogento_realurl.php:tx_weetypogento_realurl->addMagentoConfig';
//}

?>
