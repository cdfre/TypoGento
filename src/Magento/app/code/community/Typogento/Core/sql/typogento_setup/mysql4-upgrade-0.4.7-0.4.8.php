<?php 

/**
 * TypoGento installer
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */


/* @var $installer Mage_Eav_Model_Entity_Setup */
$installer = $this;

$installer->startSetup();

if ($installer->getAttributeId('customer', 'typo3_uid') !== false) {
	$installer->removeAttribute('customer', 'typo3_uid');
}

if ($installer->getConnection()->tableColumnExists($this->getTable('customer_group'), typo3_group_id)) {
	$installer->run("
		ALTER TABLE {$this->getTable('customer_group')} DROP INDEX `typo3_group_id`;
		ALTER TABLE {$this->getTable('customer_group')} DROP COLUMN `typo3_group_id`;
	");
}

$installer->endSetup();
