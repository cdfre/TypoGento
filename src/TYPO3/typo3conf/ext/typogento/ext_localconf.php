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
		'classFile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY).'sv1/class.tx_typogento_sv1.php',
		'className' => 'Tx\\Typogento\\Service\\System\\AuthenticationService'
	)
);

/**
 * Prevents RSA Authentication to start session before TypoGento
 */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
	$_EXTKEY,
	'auth',
	'tx_typogento_sv1',
	array(
		'title' => 'Magento customer session service',
		'description' => 'Initializes the Magento customer session early.',
		'subtype' => 'processLoginDataFE',
		'available' => TRUE,
		'priority' => 80,
		'quality' => 65,
		'os' => '',
		'exec' => '',
		'classFile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY).'sv1/class.tx_typogento_sv1.php',
		'className' => 'Tx\\Typogento\\Service\\System\\AuthenticationService'
	)
);

/**
 * Registers frontend hooks
 */
if (TYPO3_MODE === 'FE') {

    /**
     * Adds frontend user single sign-off feature
     */
    $TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['logoff_pre_processing'][$_EXTKEY] =
        'EXT:'.$_EXTKEY.'/Classes/Service/System/AuthenticationService.php:&Tx\Typogento\Service\System\AuthenticationService->logoffPreProcessing';

    /**
     * Renders the Magento page header and JS rewriter
     */
    $TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-preProcess'][$_EXTKEY] =
        'EXT:'.$_EXTKEY.'/Classes/Hook/TypoScriptHook.php:&Tx\Typogento\Hook\TypoScriptHook->renderPreProcess';

    /**
     * Integrates TypoScript registers
     */
    $TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['configArrayPostProc'][$_EXTKEY] =
        'EXT:'.$_EXTKEY.'/Classes/Hook/TypoScriptHook.php:&Tx\Typogento\Hook\TypoScriptHook->configArrayPostProc';

    /**
     * Invalidates cache on redirects
     */
    $TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['insertPageIncache'][$_EXTKEY] =
        'EXT:'.$_EXTKEY.'/Classes/Hook/TypoScriptHook.php:&Tx\Typogento\Hook\TypoScriptHook';

    /**
     * Provides auto login for Magento customer
     */
    $TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['postUserLookUp'][$_EXTKEY] =
        'EXT:'.$_EXTKEY.'/Classes/Service/System/AuthenticationService.php:&Tx\Typogento\Service\System\AuthenticationService->postUserLookUp';

    /**
     * Prevents RSA Authentication to start session before TypoGento
     */
    $TYPO3_CONF_VARS['EXTCONF']['felogin']['loginFormOnSubmitFuncs'][$_EXTKEY] =
        'EXT:'.$_EXTKEY.'/Classes/Service/System/AuthenticationService.php:&Tx\Typogento\Service\System\AuthenticationService->loginFormHook';
}

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
		'classFile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY).'sv2/class.tx_typogento_sv2.php',
		'className' => 'Tx\\Typogento\\Service\\System\\ReplicationService'
	)
);


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
$TYPO3_CONF_VARS['SC_OPTIONS']['scheduler']['tasks']['Tx\\Typogento\\Task\\ClearSoapCacheTask'] = array(
	'extension'        => $_EXTKEY,
	'title'            => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang.xml:task.title.clear_soap_cache',
	'description'      => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang.xml:task.description.cear_soap_cache'
);
?>
