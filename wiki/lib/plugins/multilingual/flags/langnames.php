<?php
/**  THIS PAGE HAS TO BE utf-8 ENCODED.
 * Info Plugin: Simple multilanguage plugin language names
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Daniel Darvish, MD <ddarvish@hibm.org>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

// For a list of country codes see http://www.iso.org/iso/country_codes/iso_3166_code_lists/english_country_names_and_code_elements.htm
// Please ensure there is only one line per two letter language code.  Listed alphabetically.  Thank you.
$langname['af'] = 'Afrikaans';    $langflag['af'] = 'af.gif'; 
$langname['ar'] = 'العربية (Arabic)';     $langflag['ar'] = 'sa.gif';
$langname['cs'] = 'čeština or Česky (Czech)';      $langflag['cs'] = 'cz.gif'; 
$langname['da'] = 'Dansk (Danish)';      $langflag['da'] = 'dk.gif'; 
$langname['de'] = 'Deutsch (German)';    $langflag['de'] = 'de.gif'; 
$langname['en'] = 'English';    $langflag['en'] = 'gb.gif'; //may use uk.gif if you want!
$langname['ga'] = 'Gaeilge (Irish)';    $langflag['ga'] = 'ie.gif'; 
$langname['el'] = 'ελληνικά (Greek)';   $langflag['el'] = 'gr.gif'; 
$langname['es'] = 'Español (Spanish, Mexico)';    $langflag['es'] = 'mx.gif'; 
$langname['fa'] = 'فارسی (Persian/Farsi, Iran)';   $langflag['fa'] = 'ir.gif'; 
$langname['fr'] = 'Français (French)';   $langflag['fr'] = 'fr.gif'; 
$langname['he'] = 'עברית (Hebrew, Israel)';     $langflag['he'] = 'il.gif'; 
$langname['it'] = 'Italiano';   $langflag['it'] = 'it.gif'; 
$langname['ja'] = '日本語 (Japanese)';   $langflag['ja'] = 'jp.gif'; 
$langname['ko'] = '한국어 (Korean)';     $langflag['ko'] = 'kr.gif'; 
$langname['no'] = 'Norsk or Bokmål (Norwegian';  $langflag['no'] = 'no.gif'; 
$langname['nl'] = 'Nederlands'; $langflag['nl'] = 'nl.gif'; 
$langname['pl'] = 'Poliski (Polish)';  $langflag['pl'] = 'pl.gif';
$langname['pt'] = 'Português (Portuguese, Portugal)';  $langflag['pt'] = 'pt.gif'; 
$langname['pt-br'] = 'Português brasileiro (Portuguese, Brasil)';   $langflag['pt-br'] = 'br.gif'; 
$langname['ru'] = 'Русский (Russsian)';  $langflag['ru'] = 'ru.gif'; 
$langname['ro'] = 'Română (Romanian)'; $langflag['ro'] = 'ro.gif'; 
$langname['sv'] = 'Svenska (Swedish)';    $langflag['sv'] = 'se.gif'; 
$langname['th'] = 'ภาษาไทย (Thai)';  $langflag['th'] = 'th.gif'; 
$langname['tr'] = 'Tϋrkçe (Turkish)';  $langflag['tr'] = 'tr.gif'; 
$langname['vi'] = 'Tiếng Việt (Vietnamese)';  $langflag['vi'] = 'vn.gif'; 
$langname['zh'] = '中文 (Chinese)';    $langflag['zh'] = 'cn.gif'; 


/*
Fot this to work, the function _buildTransLink($lng,$idpart) in syntax.php should look have the following code (see BOF and EOF: add flags ...)

    function _buildTransLink($lng,$idpart){
        global $conf;
        global $saved_conf;
        if($lng){
            $link = ':'.$this->tns.$lng.':'.$idpart;
            $name = $lng;
        }else{
            $link = ':'.$this->tns.$idpart;
            if(!$conf['lang_before_translation']){
              $name = $conf['lang'];
            } else {
              $name = $conf['lang_before_translation'];
            }
        }

        // BOF: add flags by ddarvish; ******************************
        if(file_exists(DOKU_PLUGIN.'translation/flags/langnames.php')) {
          require(DOKU_PLUGIN.'translation/flags/langnames.php');
          if(file_exists(DOKU_PLUGIN.'translation/flags/'.$langflag[$name])){
            $flag['title'] = $langname[$name];
            $flag['src'] = DOKU_URL.'lib/plugins/translation/flags/'.$langflag[$name];
            return html_wikilink($link,$flag);
          }
        }
        // EOF: add flags by ddarvish;*********************************

        return html_wikilink($link,$name);
    }

*/
