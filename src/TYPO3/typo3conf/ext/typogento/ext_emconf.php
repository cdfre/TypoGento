<?php

$EM_CONF[$_EXTKEY] = array(
	'title' => 'TypoGento',
	'description' => 'Integrates TYPO3 with Magento (https://github.com/witrin/TypoGento).',
	'category' => 'fe',
	'shy' => 0,
	'version' => '2.0.0',
	'dependencies' => 'sv,fluid,extbase',
	'conflicts' => '',
	'priority' => 'top',
	'loadOrder' => '',
	'module' => 'magento,administration',
	'state' => 'beta',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => 'fe_users,sys_language,be_users',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Artus Kolanowski',
	'author_email' => 'artus@ionoi.net',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'typo3' => '6.0.0-0.0.0'
		),
		'suggests' => array(
		),
	)
);

?>
