<?php

/**
 * Configuration for the module area.
 */
define('TYPO3_MOD_PATH', '../typo3conf/ext/typogento/Configuration/Module/');

$MCONF['name']='magento';
$MCONF['access']='user,group';
$MCONF['script']='_DISPATCH';
$MLANG['default']['tabs_images']['tab'] = 'Resources/Public/Icons/module.png';
$MLANG['default']['ll_ref']='LLL:EXT:typogento/Resources/Private/Language/locallang_mod.xml';
?>