<?php

$path = t3lib_extMgm::extPath('typogento');

return array(
	'tx_typogento_pi1' => $path.'pi1/class.tx_typogento_pi1.php',
	'tx_typogento_pi1_helper' => $path.'pi1/class.tx_typogento_pi1_helper.php',

	'tx_typogento_auth_sv1' => $path.'sv1/class.tx_typogento_auth_sv1.php',

	'tx_typogento_cache' => $path.'lib/class.tx_typogento_cache.php',
	'tx_typogento_interface' => $path.'lib/class.tx_typogento_interface.php',
	'tx_typogento_navigation' => $path.'lib/class.tx_typogento_navigation.php',
	'tx_typogento_realurl' => $path.'lib/class.tx_typogento_realurl.php',
	'tx_typogento_soapinterface' => $path.'lib/class.tx_typogento_soapinterface.php',
	'tx_typogento_tcafields' => $path.'lib/class.tx_typogento_tcafields.php',
	'tx_typogento_div' => $path.'lib/class.tx_typogento_div.php',

	'tx_typogento_header' => $path.'lib/class.tx_typogento_header.php',
	'tx_typogento_div' => $path.'lib/class.tx_typogento_div.php',
	'tx_typogento_observer' => $path.'lib/class.tx_typogento_observer.php',
	'tx_typogento_router' => $path.'lib/routing/class.tx_typogento_router.php',
	'tx_typogento_autoloader' => $path.'lib/class.tx_typogento_autoloader.php',
	'tx_typogento_environment' => $path.'lib/class.tx_typogento_environment.php',

	'tx_typogento_register_abstract' => $path.'lib/register/class.tx_typogento_register_abstract.php',
	'tx_typogento_register_content' => $path.'lib/register/class.tx_typogento_register_content.php',
	'tx_typogento_register_header' => $path.'lib/register/class.tx_typogento_register_header.php',

	'tx_typogento_route' => $path.'lib/routing/class.tx_typogento_route.php',

	'tx_typogento_routebuilder' => $path.'lib/routing/class.tx_typogento_routebuilder.php',
	'tx_typogento_defaultroutebuilder' => $path.'lib/routing/class.tx_typogento_routebuilder.php',

	'tx_typogento_routefilter' => $path.'lib/routing/class.tx_typogento_routefilter.php',
	'tx_typogento_typoscriptroutefilter' => $path.'lib/routing/class.tx_typogento_routefilter.php',

	'tx_typogento_routehandler' => $path.'lib/routing/class.tx_typogento_routehandler.php',
	'tx_typogento_typolinkroutehandler' => $path.'lib/routing/class.tx_typogento_routehandler.php',

	'tx_typogento_languagehelper' => $path.'lib/class.tx_typogento_languagehelper.php',
	'tx_typogento_configuration' => $path.'lib/class.tx_typogento_configuration.php',
	'tx_typogento_magentohelper' => $path.'lib/class.tx_typogento_magentohelper.php',

	'tx_typogento_clearsoapcachetask' => $path.'tasks/class.tx_typogento_clearsoapcachetask.php',

	'tx_typogento_uniqueemail' => $path.'lib/class.tx_typogento_uniqueemail.php',

	'tx_typogento_statusreport' => $path.'reports/class.tx_typogento_statusreport.php'
);

?>