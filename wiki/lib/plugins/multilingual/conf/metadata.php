<?php
/**
 * Options for the translation plugin
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */

$meta['enabled_langs'] = array('string','_pattern' => '/^(|[a-zA-Z\- ,]+)$/');
$meta['flags']             = array('onoff');
$meta['use_browser_lang']  = array('onoff');
$meta['start_redirect']    = array('onoff');
$meta['skiptrans']         = array('string');
$meta['about']             = array('string','_pattern' => '/^(|[\w:\-]+)$/');
?>
