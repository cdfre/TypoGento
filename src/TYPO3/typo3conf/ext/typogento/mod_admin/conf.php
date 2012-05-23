<?php

/**
 * Module config
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
define('TYPO3_MOD_PATH', '../typo3conf/ext/typogento/mod_admin/');
$BACK_PATH='../../../../typo3/';

$MCONF['name']='txtypogentoMgroup_txtypogentoMadmin';

$MCONF['access']='user,group';
$MCONF['script']='index.php';

$MLANG['default']['tabs_images']['tab'] = 'moduleicon.gif';
$MLANG['default']['ll_ref']='LLL:EXT:typogento/mod_admin/locallang_mod.xml';
?>