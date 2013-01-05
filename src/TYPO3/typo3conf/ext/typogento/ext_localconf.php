<?php

if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

/**
 * Configures the default frontend plugin
 */
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'Tx.' . $_EXTKEY,
	'Pi1',
	array(
		'Block' => 'index',
	),
	array(
		'Block' => '',
	)
);

/**
 * Extend TypoScript from static template uid=43 to set up userdefined tag
 */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
	$_EXTKEY, 
	'editorcfg', 
	'tt_content.CSS_editor.ch.tx_typogento_pi1 = < plugin.tx_typogento_pi1.CSS_editor', 
	43
);

/**
 * Adds default frontend user single sign-on service
 */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
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
		'classFile' => ExtensionManagementUtility::extPath($_EXTKEY).'sv1/class.tx_typogento_sv1.php',
		'className' => 'Tx\\Typogento\\Service\\System\\AuthenticationService'
	)
);

/**
 * Adds default frontend user replication service
 */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
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
		'classFile' => ExtensionManagementUtility::extPath($_EXTKEY).'sv2/class.tx_typogento_sv2.php',
		'className' => 'Tx\\Typogento\\Service\\System\\ReplicationService'
	)
);

/**
 * Registers hooks
 */
if (TYPO3_MODE === 'FE') {
	/**
	 * Adds frontend user single sign-off feature
	 */
	$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['logoff_pre_processing']['typogento'] =
		'EXT:'.$_EXTKEY.'/Classes/Hook/AuthenticationHook.php:&Tx\Typogento\Hook\AuthenticationHook->logoffPreProcessing';
	/**
	 * Renders the Magento page header
	 */
	$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-preProcess']['typogento'] =
		'EXT:'.$_EXTKEY.'/Classes/Hook/TypoScriptHook.php:&Tx\Typogento\Hook\TypoScriptHook->renderPreProcess';
	/**
	 * Integrates TypoScript registers
	 */
	$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['configArrayPostProc']['typogento'] =
		'EXT:'.$_EXTKEY.'/Classes/Hook/TypoScriptHook.php:&Tx\Typogento\Hook\TypoScriptHook->configArrayPostProc';
	
	/**
	 * Invalidates cache on redirects
	 */
	$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['insertPageIncache']['typogento'] = 
		'EXT:'.$_EXTKEY.'/Classes/Hook/TypoScriptHook.php:&Tx\Typogento\Hook\TypoScriptHook';
}

/**
 * Registers cache
 */
if (!is_array($TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['typogento'])) {
	$TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['typogento'] = array();
}

/** 
 * Defines variable frontend as default frontend
 */
if (!isset($TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['typogento']['frontend'])) {
	$TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['typogento']['frontend'] = 't3lib_cache_frontend_VariableFrontend';
}

/**
 * Adds SOAP cache cleaning task
 */
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['Tx\\Typogento\\Task\\ClearSoapCacheTask'] = array(
	'extension'        => $_EXTKEY,
	'title'            => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang.xml:lib_clear_soap_cache_task_name',
	'description'      => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang.xml:lib_clear_soap_cache_task_description'
);

/**
 * Configures Logger, use this to get more information at runtime
 */
$TYPO3_CONF_VARS['LOG']['Tx']['Typogento']['Utility']['LogUtility'] = array(
	'writerConfiguration' => array(
		\TYPO3\CMS\Core\Log\LogLevel::ERROR => array(),
		\TYPO3\CMS\Core\Log\LogLevel::WARNING => array(),
		\TYPO3\CMS\Core\Log\LogLevel::NOTICE => array(),
		\TYPO3\CMS\Core\Log\LogLevel::INFO => array(),
		\TYPO3\CMS\Core\Log\LogLevel::DEBUG => array()
	)
);
?>
