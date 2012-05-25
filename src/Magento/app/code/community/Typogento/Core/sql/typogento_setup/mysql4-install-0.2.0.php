<?php 

/**
 * TypoGento installer
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */


/* @var $installer Mage_Eav_Model_Entity_Setup */
$installer = $this;

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
	ALTER TABLE {$this->getTable('customer_group')} ADD COLUMN `typo3_group_id` INT NULL;
	ALTER TABLE {$this->getTable('customer_group')} ADD INDEX `typo3_group_id` (`typo3_group_id`);
");

$installer->endSetup();
