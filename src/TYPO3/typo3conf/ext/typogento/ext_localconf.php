<?php

if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

/**
 * Change caching behaviour of Magento Frontend Plugin
 */
t3lib_extMgm::addPItoST43(
	$_EXTKEY, 
	'pi1/class.tx_typogento_pi1.php','_pi1','list_type', 
	1
);

/**
 * Extend TypoScript from static template uid=43 to set up userdefined tag
 */
t3lib_extMgm::addTypoScript(
	$_EXTKEY, 
	'editorcfg', 
	'tt_content.CSS_editor.ch.tx_typogento_pi1 = < plugin.tx_typogento_pi1.CSS_editor', 
	43
);

/**
 * Adds default frontend user single sign-on service
 */
t3lib_extMgm::addService(
	$_EXTKEY, 
	'auth', 
	'tx_typogento_sv1',
	array(
		'title' => 'Magento customer single sign-on service',
		'description' => 'Provides single sign-on for TYPO3 frontend users and Magento customers.',
		'subtype' => 'getUserFE,authUserFE',
		'available' => TRUE,
		'priority' => 60,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'sv1/class.tx_typogento_sv1.php',
		'className' => 'tx_typogento_sv1'
	)
);

/**
 * Adds default frontend user replication service
 */
t3lib_extMgm::addService(
	$_EXTKEY,
	'auth',
	'tx_typogento_sv2',
	array(
		'title' => 'Magento customer replication service',
		'description' => 'Automatic, on-demand replication of TYPO3 frontend users and Magento customers.',
		'subtype' => 'getUserFE',
		'available' => TRUE,
		'priority' => 100,
		'quality' => 100,
		'os' => '',
		'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'sv2/class.tx_typogento_sv2.php',
		'className' => 'tx_typogento_sv2'
	)
);

/**
 * Register system hooks
 */
if (TYPO3_MODE === 'FE') {
	/**
	 *
	 */
	$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['logoff_pre_processing']['typogento'] =
		'EXT:typogento/lib/class.tx_typogento_observer.php:tx_typogento_observer->logoffPreProcessing';
	/**
	 * Improves TypoGento automatic header integration
	 */
	$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-preProcess']['typogento'] =
		'EXT:typogento/lib/class.tx_typogento_observer.php:&tx_typogento_observer->renderPreProcess';
	/**
	 * Improves internal FlexForm access and its caching
	 */
	$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['configArrayPostProc']['typogento'] =
		'EXT:typogento/lib/class.tx_typogento_observer.php:&tx_typogento_observer->configArrayPostProc';
	/**
	 * 
	 */
	$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_content.php']['getData']['typogento'] =
		'EXT:typogento/lib/class.tx_typogento_observer.php:&tx_typogento_observer';
	
	/**
	 * Provides auto login for Magento customer
	 */
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['postUserLookUp']['typogento_sv1'] =
		'EXT:typogento/sv1/class.tx_typogento_sv1.php:&tx_typogento_sv1->postUserLookUp';
}

/**
 * Register cache
 */
if (!is_array($TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['typogento'])) {
	$TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['typogento'] = array();
}

/** 
 * Define variable frontend as default frontend, this must be set with TYPO3 4.5 and below 
 * and overrides the default variable frontend of 4.6
 */
if (!isset($TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['typogento']['frontend'])) {
	$TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['typogento']['frontend'] = 't3lib_cache_frontend_VariableFrontend';
}

/**
 * Setup the default configuration for 4.5 and bellow
 */
if (t3lib_div::int_from_ver(TYPO3_version) < '4006000') {
	/**
	 * Define database backend as backend (default in 4.6)
	 */
	if (!isset($TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['typogento']['backend'])) {
		$TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['typogento']['backend'] = 't3lib_cache_backend_DbBackend';
	}
	/**
	 * Define data and tags table (obsolete in 4.6)
	 */
	if (!isset($TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['typogento']['options'])) {
		$TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['typogento']['options'] = array();
	}
	if (!isset($TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['typogento']['options']['cacheTable'])) {
		$TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['typogento']['options']['cacheTable'] = 'tx_typogento_cache';
	}
	if (!isset($TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['typogento']['options']['tagsTable'])) {
		$TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['typogento']['options']['tagsTable'] = 'tx_typogento_cache_tags';
	}
}

/**
 * Add validator for unique frontend user emails
 */
$TYPO3_CONF_VARS['SC_OPTIONS']['tce']['formevals']['tx_typogento_uniqueemail'] = 
	'EXT:typogento/lib/class.tx_typogento_uniqueemail.php';

/**
 * Add SOAP cache cleaning task
 */
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['tx_typogento_clearSoapCacheTask'] = array(
	'extension'        => $_EXTKEY,
	'title'            => 'LLL:EXT:' . $_EXTKEY . '/locallang.xml:lib_clear_soap_cache_task_name',
	'description'      => 'LLL:EXT:' . $_EXTKEY . '/locallang.xml:lib_clear_soap_cache_task_description'
);

?>
