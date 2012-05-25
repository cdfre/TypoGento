<?php

$extensionPath = t3lib_extMgm::extPath('typogento');

return array(
	'tx_typogento_pi1' => $extensionPath.'pi1/class.tx_typogento_pi1.php',
	
	'tx_typogento_auth_sv1' => $extensionPath.'sv1/class.tx_typogento_auth_sv1.php',
		
	'tx_typogento_cache' => $extensionPath.'lib/class.tx_typogento_cache.php',
	'tx_typogento_interface' => $extensionPath.'lib/class.tx_typogento_interface.php',
	'tx_typogento_navigation' => $extensionPath.'lib/class.tx_typogento_navigation.php',
	'tx_typogento_realurl' => $extensionPath.'lib/class.tx_typogento_realurl.php',
	'tx_typogento_soapinterface' => $extensionPath.'lib/class.tx_typogento_soapinterface.php',
	'tx_typogento_tcafields' => $extensionPath.'lib/class.tx_typogento_tcafields.php',
	'tx_typogento_div' => $extensionPath.'lib/class.tx_typogento_div.php',

	'tx_typogento_header' => $extensionPath.'lib/class.tx_typogento_header.php',
	'tx_typogento_div' => $extensionPath.'lib/class.tx_typogento_div.php',
	'tx_typogento_observer' => $extensionPath.'lib/class.tx_typogento_observer.php',
	'tx_typogento_router' => $extensionPath.'lib/routing/class.tx_typogento_router.php',
	'tx_typogento_autoloader' => $extensionPath.'lib/class.tx_typogento_autoloader.php',
	
	'tx_typogento_route' => $extensionPath.'lib/routing/class.tx_typogento_route.php',
	
	'tx_typogento_routebuilder' => $extensionPath.'lib/routing/class.tx_typogento_routebuilder.php',
	'tx_typogento_defaultroutebuilder' => $extensionPath.'lib/routing/class.tx_typogento_routebuilder.php',
	
	'tx_typogento_routefilter' => $extensionPath.'lib/routing/class.tx_typogento_routefilter.php',
	'tx_typogento_typoscriptroutefilter' => $extensionPath.'lib/routing/class.tx_typogento_routefilter.php',
	
	'tx_typogento_routehandler' => $extensionPath.'lib/routing/class.tx_typogento_routehandler.php',
	'tx_typogento_typolinkroutehandler' => $extensionPath.'lib/routing/class.tx_typogento_routehandler.php',

	'tx_typogento_routeenvironment' => $extensionPath.'lib/routing/class.tx_typogento_routeenvironment.php',
	
	'tx_typogento_languagehelper' => $extensionPath.'lib/class.tx_typogento_languagehelper.php',
	'tx_typogento_configurationhelper' => $extensionPath.'lib/class.tx_typogento_configurationhelper.php',
	'tx_typogento_magentohelper' => $extensionPath.'lib/class.tx_typogento_magentohelper.php',

	'tx_typogento_clearsoapcachetask' => $extensionPath.'tasks/class.tx_typogento_clearsoapcachetask.php',

	'tx_typogento_uniqueemail' => $extensionPath.'lib/class.tx_typogento_uniqueemail.php',

	'tx_typogento_statusreport' => $extensionPath.'reports/class.tx_typogento_statusreport.php'
);

?>