<?php

if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

/**
 * 
 */
t3lib_extMgm::allowTableOnStandardPages('tx_typogento_replication_links');

$TCA['tx_typogento_replication_links'] = array (
	'ctrl' => array (
		'title' => 'LLL:EXT:typogento/res/language/locallang_db.xml:tx_typogento_replication_links',
		'label' => 'uid',
		//'label_userFunc' => 'EXT:typogento/lib/class.tx_typogento_tcafields.php:tx_typogento_tcafields->itemsProcFunc_replicationLinkLabel',
		'deleted' => 'deleted',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY tstamp',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY).'res/images/replication-icon.png',
		//'requestUpdate' => 'provider'
	),
	'interface' => array (
		'maxDBListItems' => 60,
		'showRecordFieldList' => 'provider, disable'
	),
	'feInterface' => array (
		'fe_admin_fieldList' => 'provider, disable'
	),
	'columns' => array (
		'provider' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:typogento/res/language/locallang_db.xml:tx_typogento_replication_links.provider',
			'config' => array (
				'type' => 'select',
				'items' => array (
					array('LLL:EXT:typogento/res/language/locallang_db.xml:tx_typogento_replication_links.provider.1', '1'),
					array('LLL:EXT:typogento/res/language/locallang_db.xml:tx_typogento_replication_links.provider.2', '2'),
				),
				'size' => 1,
				'maxitems' => 1,
				'minitems' => 1,
				'default' => 0
			)
		),
		'source' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:typogento/res/language/locallang_db.xml:tx_typogento_replication_links.source',
			'config' => array (
				'eval' => 'unique',
				'type' => 'select',
				'items' => array(
					array('LLL:EXT:typogento/res/language/locallang_db.xml:tx_typogento_replication_links.source.0', '')
				),
				'itemsProcFunc' => 'EXT:typogento/lib/class.tx_typogento_tcafields.php:tx_typogento_tcafields->itemsProcFunc_replicationSources',
				'size' => 1,
				'maxitems' => 1,
				'default' => 0
			)
		),
		'target' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:typogento/res/language/locallang_db.xml:tx_typogento_replication_links.target',
			'config' => array (
				'eval' => 'unique',
				'type' => 'select',
				'items' => array(
					array('LLL:EXT:typogento/res/language/locallang_db.xml:tx_typogento_replication_links.source.0', '')
				),
				'itemsProcFunc' => 'EXT:typogento/lib/class.tx_typogento_tcafields.php:tx_typogento_tcafields->itemsProcFunc_replicationTargets',
				'size' => 1,
				'maxitems' => 1,
				'default' => 0
			)
		),
		'disable' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:typogento/res/language/locallang_db.xml:tx_typogento_replication_links.disable',
			'config' => array (
				'type' => 'check'
			)
		)
	),
	'types' => array (
		'0' => array('showitem' => 'disable,provider;;1;;')
	),
	'palettes' => array (
		'1' => array('showitem' => 'source,target')
	)
	
);


/**
 * 
 */
t3lib_div::loadTCA('tt_content');

$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1'] = 'layout,select_key';
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1'] = 'layout,select_key, pages, recursive';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1'] = 'pi_flexform';

t3lib_extMgm::addPlugin(array('LLL:EXT:typogento/res/language/locallang_db.xml:tt_content.list_type_pi1', $_EXTKEY.'_pi1'), 'list_type');
t3lib_extMgm::addPiFlexFormValue($_EXTKEY .'_pi1', 'FILE:EXT:typogento/pi1/flexform.xml');
t3lib_extMgm::addStaticFile($_EXTKEY,'pi1/static/','TypoGento');


/**
 * Extends fe_users
 */
$columns = array (
	'static_info_country' => array (
		'exclude' => 1,
		'label' => 'LLL:EXT:typogento/res/language/locallang_db.xml:fe_users.static_info_country',
		'config' => array (
			'type' => 'input',
			'size' => '5',
			'max' => '3',
			'eval' => '',
			'default' => ''
		)
	),
	'date_of_birth' => array (
		'exclude' => 1,
		'label' => 'LLL:EXT:typogento/res/language/locallang_db.xml:fe_users.date_of_birth',
		'config' => array (
			'type' => 'input',
			'size' => '10',
			'max' => '20',
			'eval' => 'date',
			'checkbox' => '0',
			'default' => ''
		)
	),
	'gender' => array (
		'exclude' => 1,
		'label' => 'LLL:EXT:typogento/res/language/locallang_db.xml:fe_users.gender',
		'config' => array (
			'type' => 'select',
			'items' => array (
				array('LLL:EXT:typogento/res/language/locallang_db.xml:fe_users.gender.I.99', '99'),
				array('LLL:EXT:typogento/res/language/locallang_db.xml:fe_users.gender.I.0', '0'),
				array('LLL:EXT:typogento/res/language/locallang_db.xml:fe_users.gender.I.1', '1')
			),
		)
	),
	'tx_typogento_customer' => array (
		'exclude' => 1,
		'label' => 'LLL:EXT:typogento/res/language/locallang_db.xml:fe_users.tx_typogento_customer',
		'config' => array (
			'eval' => 'unique',
			'type' => 'select',
			'items' => array(
				array('LLL:EXT:typogento/res/language/locallang_db.xml:be_users.tx_typogento_customer.0', '')
			),
			'readOnly' => 1,
			'itemsProcFunc' => 'EXT:typogento/lib/class.tx_typogento_tcafields.php:tx_typogento_tcafields->itemsProcFunc_customers',
			'maxitems' => 1,
			'minitems' => 1
		)
	)
);

t3lib_div::loadTCA('fe_users');
t3lib_extMgm::addTCAcolumns('fe_users', $columns, 1);
t3lib_extMgm::addToAllTCAtypes('fe_users', 'tx_typogento_customer;;;;1-1-1');

for ($i = $lastPalette = 0; $i<10; $i++) {
	if (isset($TCA['fe_users']['palettes'][$i]) && is_array($TCA['fe_users']['palettes'][$i])) {
		$lastPalette = $i;
	}
}

++$lastPalette;
$TCA['fe_users']['interface']['showRecordFieldList'] = 
	str_replace(',country', ',static_info_country,country', $TCA['fe_users']['interface']['showRecordFieldList']);
$TCA['fe_users']['feInterface']['fe_admin_fieldList'] = 
	str_replace(',country', ',static_info_country,country', $TCA['fe_users']['feInterface']['fe_admin_fieldList']);
$TCA['fe_users']['types']['0']['showitem'] = 
	str_replace(', country', ", country;;$lastPalette;;1-1-1,", $TCA['fe_users']['types']['0']['showitem']);
$TCA['fe_users']['palettes'][$lastPalette]['showitem'] = 'static_info_country';

$TCA['fe_users']['interface']['showRecordFieldList'] = 
	str_replace('title,','gender,date_of_birth,title,', $TCA['fe_users']['interface']['showRecordFieldList']);
$TCA['fe_users']['feInterface']['fe_admin_fieldList'] .= ',date_of_birth';
$TCA['fe_users']['types']['0']['showitem'] = 
	str_replace(', address', ', date_of_birth, address', $TCA['fe_users']['types']['0']['showitem']);

++$lastPalette;
$TCA['fe_users']['feInterface']['showRecordFieldList'] = 
	str_replace('title,', 'gender,date_of_birth,title,', $TCA['fe_users']['interface']['showRecordFieldList']);
$TCA['fe_users']['feInterface']['fe_admin_fieldList'] = 
	str_replace(', title', ', gender, title', $TCA['fe_users']['feInterface']['fe_admin_fieldList']);
$TCA['fe_users']['types']['0']['showitem'] =
	str_replace(', name', ", name;;$lastPalette;;1-1-1,", $TCA['fe_users']['types']['0']['showitem']);
$TCA['fe_users']['palettes'][$lastPalette]['showitem'] = 'first_name,--linebreak--,middle_name,--linebreak--,last_name,--linebreak--,gender';

$TCA['fe_users']['columns']['email']['config']['eval'] = 'trim, lower, tx_typogento_uniqueemail, required';


/**
 * Extends sys_language
 */
$columns = array (
	'tx_typogento_store' => array(
		'exclude' => 0,
		'label' => 'LLL:EXT:typogento/res/language/locallang_db.xml:sys_language.tx_typogento_store',
		'config' => array (
			'type' => 'select',
			'itemsProcFunc' => 'EXT:typogento/lib/class.tx_typogento_tcafields.php:tx_typogento_tcafields->itemsProcFunc_languages',
			'maxitems' => 1
		)
	)
);

t3lib_div::loadTCA('sys_language');
t3lib_extMgm::addTCAcolumns('sys_language', $columns,1);
t3lib_extMgm::addToAllTCAtypes('sys_language', 'tx_typogento_store;;;;1-1-1');


/**
 * Extends be_users
 */
$columns = array (
	'tx_typogento_group' => array(
		'exclude' => 0,
		'label' => 'LLL:EXT:typogento/res/language/locallang_db.xml:be_users.tx_typogento_group',
		'config' => array (
			'type' => 'select',
			'items' => array(
				array('LLL:EXT:typogento/res/language/locallang_db.xml:be_users.tx_typogento_group.0', '')
			),
			'itemsProcFunc' => 'EXT:typogento/lib/class.tx_typogento_tcafields.php:tx_typogento_tcafields->itemsProcFunc_usergroups',
			'maxitems' => 1
		)
	)
);

t3lib_div::loadTCA('be_users');
t3lib_extMgm::addTCAcolumns('be_users', $columns, 1);
t3lib_extMgm::addToAllTCAtypes('be_users', 'tx_typogento_group;;;;1-1-1');


/**
 * 
 */
if (TYPO3_MODE=='BE') {
	/**
	 * Adds plugin wizard icon
	 */
	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_typogento_pi1_wizicon'] =
		t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_typogento_pi1_wizicon.php';

	/**
	 * Adds module after 'Web'
	 */
	if (!isset($TBE_MODULES['magento'])) {
		$temp_TBE_MODULES = array();
		foreach ($TBE_MODULES as $key => $val) {
			$temp_TBE_MODULES[$key] = $val;
			if ($key == 'web') {
				$temp_TBE_MODULES['magento'] = $val;
			}
		}
		$TBE_MODULES = $temp_TBE_MODULES;
	}

	/**
	 * Adds group module
	 */
	t3lib_extMgm::addModule('magento', '', '', t3lib_extmgm::extPath($_EXTKEY).'mod1/');

	/**
	 * Adds admin module
	 */
	t3lib_extMgm::addModule('magento', 'admin', '', t3lib_extmgm::extPath($_EXTKEY).'mod2/');

	/**
	 * Registers status provider
	 */
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['reports']['tx_reports']['status']['providers']['TypoGento'] 
		= array('tx_typogento_statusReport');
}

?>