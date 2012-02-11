<?php

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Wee TypoGento',
	'description' => 'Integrates TYPO3 with Magento. Forked from Flagbit\'s TypoGento.',
	'category' => 'fe',
	'shy' => 0,
	'version' => '0.3.5',
	'dependencies' => 'sv',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => 'mod_admin,mod_group',
	'state' => 'beta',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Artus Kolanowski',
	'author_email' => 'artus@beluto.com',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'typo3' => '4.5.7-'
		),
		'conflicts' => array(
			'fb_magento' => ''
		),
		'suggests' => array(
		),
	)
);

?>
