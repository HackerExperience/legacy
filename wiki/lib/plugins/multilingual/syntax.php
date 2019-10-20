<?php
/**
 * Translation Plugin: Simple multilanguage plugin
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

class syntax_plugin_multilingual extends DokuWiki_Syntax_Plugin {

    /**
     * for th helper plugin
     */
    var $hlp = null;

    /**
     * Constructor. Load helper plugin
     */
    function syntax_plugin_multilingual(){
        $this->hlp =& plugin_load('helper', 'multilingual');
    }

    /**
     * return some info
     */
    function getInfo(){
        return confToHash(dirname(__FILE__).'/info.txt');
    }

    /**
     * What kind of syntax are we?
     */
    function getType(){
        return 'substition';
    }

    /**
     * Where to sort in?
     */
    function getSort(){
        return 155;
    }


    /**
     * Connect pattern to lexer
     */
    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('~~NOTRANS~~',$mode,'plugin_multilingual');
    }


    /**
     * Handle the match
     */
    function handle($match, $state, $pos, &$handler){
        return array('notrans');
    }

    /**
     * Create output
     */
    function render($format, &$renderer, $data) {
        // store info in metadata
        if($format == 'metadata'){
            $renderer->meta['plugin']['multilingual']['notrans'] = true;
        }
        return false;
    }

    /**
     * Displays the available and configured translations. Needs to be placed in the template.
     */
    function _showTranslations(){
        global $ACT;
        global $ID;
        global $conf;

        if($ACT != 'show') return;
        if($this->hlp->tns && strpos($ID,$this->hlp->tns) !== 0) return;
        $skiptrans = trim($this->getConf('skiptrans'));
        if($skiptrans &&  preg_match('/'.$skiptrans.'/ui',':'.$ID)) return;
        $meta = p_get_metadata($ID);
        if($meta['plugin']['multilingual']['notrans']) return;

        $rx = '/^'.$this->hlp->tns.'(('.join('|',$this->hlp->trans).'):)?/';
        $idpart = preg_replace($rx,'',$ID);

        $out  = '<div class="plugin_multilingual">'.NL;
        $out .= '        <ul>'.NL;
	/*
        $out .= '          <li>'.NL;
	$out .= '            <div class="li">'.NL;
	$out .= '              <span class="curid"><a href="/doku/doku.php/en:doku_doodles" class="media" title="en:doku_doodles"><img src="/doku/lib/exe/fetch.php?w=&amp;h=&amp;cache=&amp;media=http%3A%2F%2Fsnorriheim.dnsdojo.com%2Fdoku%2Flib%2Fplugins%2Fmultilingual%2Fflags%2Fgb.gif" class="media" title="English" alt="English" /></a></span>'.NL;
	$out .= '            </div>'.NL;
	$out .= '          </li>'.NL;
        $out .= '          <li>'.NL;
	$out .= '            <div class="li">'.NL;
	$out .= '              <a href="/doku/doku.php/ko:doku_doodles" class="media" title="ko:doku_doodles"><img src="/doku/lib/exe/fetch.php?w=&amp;h=&amp;cache=&amp;media=http%3A%2F%2Fsnorriheim.dnsdojo.com%2Fdoku%2Flib%2Fplugins%2Fmultilingual%2Fflags%2Fkr.gif" class="media" title="한국말 (Korean)" alt="한국말 (Korean)" /></a>'.NL;
	$out .= '            </div>'.NL;
	$out .= '          </li>'.NL;
	*/
	//*
        foreach($this->hlp->trans as $t){
	    list($link,$name,$exists) = $this->hlp->buildTransLink($t,$idpart);
	    if ( $exists ) {
                $out .= '          <li>'.NL;
	 	$out .= '            <div class="li">'.NL;
		$out .= '              '.html_wikilink($link,$name).NL;
		$out .= '            </div>'.NL;
		$out .= '          </li>'.NL;
     	    } else {
                $out .= '          <li>'.NL;
		$out .= '            <div class="li">'.NL;
		$out .= '              <div class="flag_not_exists">'.NL;
		$out .= '                '.html_wikilink($link,$name).NL;
		$out .= '              </div>'.NL;
		$out .= '            </div>'.NL;
		$out .= '          </li>'.NL;
	    }
        }
	//*/
	/*
        $link = 'link';
	$name = 'name';
                $out .= '          <li>'.NL;
	 	$out .= '            <div class="li">'.NL;
		$out .= '              '.html_wikilink($link,$name).NL;
		$out .= '            </div>'.NL;
		$out .= '          </li>'.NL;
	*/
        $out .= '        </ul>'.NL;
        $out .= '      </div>'.NL;

        return $out;
    }

}

//Setup VIM: ex: et ts=4 enc=utf-8 :
