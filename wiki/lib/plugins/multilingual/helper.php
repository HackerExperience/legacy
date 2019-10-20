<?php
/**
 * Translation Plugin: Simple multilanguage plugin
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();


class helper_plugin_multilingual extends DokuWiki_Plugin {

    var $trans = array(); // List of enabled translations

    /**
     * Initialize
     */
    function helper_plugin_multilingual(){
        require_once(DOKU_INC.'inc/pageutils.php');
        require_once(DOKU_INC.'inc/utf8.php');

        // load wanted translation into array
        $this->trans = strtolower(str_replace(',',' ',$this->getConf('enabled_langs')));
        $this->trans = array_unique(array_filter(explode(' ',$this->trans)));
        sort($this->trans);
    }

    /**
     * return some info
     */
    function getInfo(){
        return confToHash(dirname(__FILE__).'/info.txt');
    }

    /**
     * Retrun an array of this dokuwiki's supported languages. Note that
     * this is not the same as conf['enabled_langs']
     */
    function getSupportedLanguages() {
        $supportedLanguages = array();
	if ($handle = opendir(DOKU_INC.'inc/lang')) {
	    while (false !== ($file = readdir($handle))) {
	        if (is_dir(DOKU_INC.'inc/lang/'.$file)) {
		    array_push($supportedLanguages,$file);
		}    
	    }
	    closedir($handle);
	}
	return $supportedLanguages;
    }
    /**
     * Builds a link, either text or graphical depending on the configuration.
     */
    function buildTransLink($lng,$idpart){
        global $conf;
        global $saved_conf;
	global $ID;
        
       /***********************
        * Setup
        **********************/
        $link = ':'.$lng.':'.$idpart;
	$name = $lng;
        $exists = true;
       
       /***********************
	 * Flags
	 **********************/
        if(file_exists(DOKU_PLUGIN.'multilingual/flags/langnames.php') && $this->getConf('flags')) {
          require(DOKU_PLUGIN.'multilingual/flags/langnames.php');
          if(file_exists(DOKU_PLUGIN.'multilingual/flags/'.$langflag[$name])){
            $flag['title'] = $langname[$name];
            $flag['src'] = DOKU_URL.'lib/plugins/multilingual/flags/'.$langflag[$name];
            resolve_pageid(getNS($ID),$link,$exists);
	    
            return array($link,$flag,$exists);
          }
        } 

        /***********************
	 * Default Fallback
	 **********************/
        return array($link,$name,$exists);
    }
}
