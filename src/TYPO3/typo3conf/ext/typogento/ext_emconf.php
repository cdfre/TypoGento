<?php

$EM_CONF[$_EXTKEY] = array(
	'title' => 'TypoGento',
	'description' => 'Integrates TYPO3 with Magento (https://github.com/witrin/TypoGento).',
	'category' => 'fe',
	'shy' => 0,
	'version' => '0.5.4',
	'dependencies' => 'sv',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => 'mod_admin,mod_group',
	'state' => 'beta',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => 'fe_users,sys_language,be_users',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Artus Kolanowski',
	'author_email' => 'artus@beluto.com',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'typo3' => '4.5.7-0.0.0',
			'php' => '5.3.1-0.0.0',
		),
		'conflicts' => array(
			'fb_magento' => ''
		),
		'suggests' => array(
		),
	)
);

?>
