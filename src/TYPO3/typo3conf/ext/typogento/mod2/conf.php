<?php

/**
 * Admin module configuration
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
define('TYPO3_MOD_PATH', '../typo3conf/ext/typogento/mod2/');
$BACK_PATH='../../../../typo3/';

$MCONF['name']='magento_admin';

$MCONF['access']='user,group';
$MCONF['script']='index.php';

$MLANG['default']['tabs_images']['tab'] = '../res/images/module-icon.png';
$MLANG['default']['ll_ref']='LLL:EXT:typogento/mod2/locallang_mod.xml';
?>