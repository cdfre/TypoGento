<?php

$extensionPath = t3lib_extMgm::extPath('wee_typogento');

return array(
	'tx_weetypogento_pi1' => $extensionPath.'pi1/class.tx_weetypogento_pi1.php',
	
	'tx_weetypogento_auth_sv1' => $extensionPath.'sv1/class.tx_weetypogento_auth_sv1.php',
		
	'tx_weetypogento_cache' => $extensionPath.'lib/class.tx_weetypogento_cache.php',
	'tx_weetypogento_interface' => $extensionPath.'lib/class.tx_weetypogento_interface.php',
	'tx_weetypogento_navigation' => $extensionPath.'lib/class.tx_weetypogento_navigation.php',
	'tx_weetypogento_realurl' => $extensionPath.'lib/class.tx_weetypogento_realurl.php',
	'tx_weetypogento_soapinterface' => $extensionPath.'lib/class.tx_weetypogento_soapinterface.php',
	'tx_weetypogento_tcafields' => $extensionPath.'lib/class.tx_weetypogento_tcafields.php',
	'tx_weetypogento_div' => $extensionPath.'lib/class.tx_weetypogento_div.php',

	'tx_weetypogento_header' => $extensionPath.'lib/class.tx_weetypogento_header.php',
	'tx_weetypogento_div' => $extensionPath.'lib/class.tx_weetypogento_div.php',
	'tx_weetypogento_observer' => $extensionPath.'lib/class.tx_weetypogento_observer.php',
	'tx_weetypogento_router' => $extensionPath.'lib/routing/class.tx_weetypogento_router.php',
	'tx_weetypogento_autoloader' => $extensionPath.'lib/class.tx_weetypogento_autoloader.php',
	
	'tx_weetypogento_route' => $extensionPath.'lib/routing/class.tx_weetypogento_route.php',
	
	'tx_weetypogento_routebuilder' => $extensionPath.'lib/routing/class.tx_weetypogento_routebuilder.php',
	'tx_weetypogento_defaultroutebuilder' => $extensionPath.'lib/routing/class.tx_weetypogento_routebuilder.php',
	
	'tx_weetypogento_routefilter' => $extensionPath.'lib/routing/class.tx_weetypogento_routefilter.php',
	'tx_weetypogento_typoscriptroutefilter' => $extensionPath.'lib/routing/class.tx_weetypogento_routefilter.php',
	
	'tx_weetypogento_routehandler' => $extensionPath.'lib/routing/class.tx_weetypogento_routehandler.php',
	'tx_weetypogento_typolinkroutehandler' => $extensionPath.'lib/routing/class.tx_weetypogento_routehandler.php',

	'tx_weetypogento_routeenvironment' => $extensionPath.'lib/routing/class.tx_weetypogento_routeenvironment.php',
	
	'tx_weetypogento_languagehelper' => $extensionPath.'lib/class.tx_weetypogento_languagehelper.php',
	'tx_weetypogento_configurationhelper' => $extensionPath.'lib/class.tx_weetypogento_configurationhelper.php',
	'tx_weetypogento_magentohelper' => $extensionPath.'lib/class.tx_weetypogento_magentohelper.php'
);

?>