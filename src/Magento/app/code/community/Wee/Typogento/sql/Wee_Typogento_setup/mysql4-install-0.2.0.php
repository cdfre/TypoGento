<?php 

/**
 * TypoGento Installer
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 * @todo use $installer->addAttribute() instead of raw sql
 */

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->addAttribute('customer', 'typo3_uid', array(
	'type'            => 'int',
	'label'           => 'TYPO3 UID',
	'input'           => 'hidden',
	'global'          => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
	'required'        => false,
	'default'         => '',
	'user_defined'    => 0
));

$installer->run("
ALTER TABLE {$this->getTable('customer_group')} ADD  `typo3_group_id` INT NULL;
ALTER TABLE  {$this->getTable('customer_group')} ADD INDEX (  `typo3_group_id` );
");

/*
$installer->run("
INSERT INTO {$this->getTable('eav_attribute')} (
`attribute_id` ,    // NULL
`entity_type_id` ,  // 1
`attribute_code` ,  // typo3_uid
`attribute_model` , // NULL
`backend_model` ,   // ''
`backend_type` ,    // int
`backend_table` ,   // ' '
`frontend_model` ,  // ''
`frontend_input` ,  // hidden
`frontend_label` ,  // ''
`frontend_class` ,  // ''
`source_model` ,    // ''
`is_global` ,       // 1
`is_visible` ,      // 1
`is_required` ,     // 0
`is_user_defined` , // 0
`default_value` ,   // ''
`is_searchable` ,   // 0
`is_filterable` ,
`is_comparable` ,
`is_visible_on_front` ,
`is_unique` ,
`is_visible_in_advanced_search` ,
`is_configurable` ,
`apply_to` ,
`position` ,       // 0
`note` ,           // ''
`is_used_for_price_rules` // 1
)
VALUES (
NULL , '1', 'typo3_uid', NULL , '', 'int', ' ', '', 'hidden', '', '', '', '1', '1', '0', '0', '', '0', '0', '0', '0', '0', '0', '0', '', '0', '', '1'
);

");
*/

$installer->endSetup();