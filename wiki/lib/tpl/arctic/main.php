<?php
/**
 * DokuWiki Arctic Template
 *
 * This is the template you need to change for the overall look
 * of DokuWiki.
 *
 * You should leave the doctype at the very top - It should
 * always be the very first line of a document.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Michael Klier <chi@chimeric.de>
 * @link   http://wiki.splitbrain.org/template:arctic
 * @link   http://chimeric.de/projects/dokuwiki/template/arctic
 */

// must be run from within DokuWiki
if (!defined('DOKU_INC')) die();

global $ACT;

// include custom arctic template functions
require_once(dirname(__FILE__).'/tpl_functions.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $conf['lang']?>"
 lang="<?php echo $conf['lang']?>" dir="<?php echo $lang['direction']?>">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>
    <?php tpl_pagetitle()?>
    [<?php echo strip_tags($conf['title'])?>]
  </title>

  <?php tpl_metaheaders()?>

  <?php echo tpl_favicon() ?>

  <?php tpl_includeFile('meta.html') ?>

</head>
<body>
<?php tpl_includeFile('topheader.html') ?>
<div id="wrapper" class='<?php echo $ACT ?>'>
  <div class="dokuwiki">

    <?php html_msgarea()?>

    <div class="stylehead">
      <div class="header">
        <div class="pagename">
          [[<?php tpl_link(wl($ID,'do=backlink'),tpl_pagetitle($ID,true))?>]]
        </div>

        <div class="logo">
          <?php tpl_link(wl(),$conf['title'],'name="dokuwiki__top" accesskey="h" title="[ALT+H]"')?>
        </div>
          <div style="position: fixed; float: right; top: 35px; right: 0px; width: 3px;">
<?php
      $multi_lingual_plugin = &plugin_load('syntax','multilingual');
      if ( $multi_lingual_plugin ) {
        if ( !plugin_isdisabled($multi_lingual_plugin->getPluginName() ) ) {
          print $multi_lingual_plugin->_showTranslations();
        }
      }
?>

        </div>

      </div>
    
      <?php if(tpl_getConf('trace')) {?> 
      <div class="breadcrumbs">
        <?php ($conf['youarehere'] != 1) ? tpl_breadcrumbs() : tpl_youarehere();?>
      </div>
      <?php } ?>

      <?php tpl_includeFile('header.html') ?>
      </div>

      <?php if(!$toolb) { ?>
      <?php if(!tpl_getConf('hideactions') || tpl_getConf('hideactions') && isset($_SERVER['REMOTE_USER'])) { ?>
      <div class="bar" id="bar__top">
        <div class="bar-left">
          <?php 
            if(!tpl_getConf('closedwiki') || (tpl_getConf('closedwiki') && isset($_SERVER['REMOTE_USER']))) {
                switch(tpl_getConf('wiki_actionlinks')) {
                  case('buttons'):
                    // check if new page button plugin is available
                    if(!plugin_isdisabled('npd') && ($npd =& plugin_load('helper', 'npd'))) {
                      $npd->html_new_page_button();
                    }
                    tpl_button('edit');
                    break;
                  case('links'):
                    // check if new page button plugin is available
                    if(!plugin_isdisabled('npd') && ($npd =& plugin_load('helper', 'npd'))) {
                      $npd->html_new_page_button();
                    }
                    tpl_actionlink('edit');
                    break;
                } 
            }
          ?>
        </div>
        <div class="bar-right">
          <?php
            switch(tpl_getConf('wiki_actionlinks')) {
              case('buttons'):
                if(!tpl_getConf('closedwiki') || (tpl_getConf('closedwiki') && isset($_SERVER['REMOTE_USER']))) {
                  tpl_button('admin');
                  tpl_button('revert');
                  tpl_button('profile');
                  tpl_button('recent');
                  tpl_button('index');
                  tpl_button('login');
                  if(tpl_getConf('sidebar') == 'none') tpl_searchform();
                } else {
                  tpl_button('login');
                }
                break;
              case('links'):
                if(!tpl_getConf('closedwiki') || (tpl_getConf('closedwiki') && isset($_SERVER['REMOTE_USER']))) {
                  tpl_actionlink('admin');
                  tpl_actionlink('revert');
                  tpl_actionlink('profile');
                  tpl_actionlink('recent');
                  tpl_actionlink('index');
                  tpl_actionlink('login');
                  if(tpl_getConf('sidebar') == 'none') tpl_searchform();
                } else {
                  tpl_actionlink('login');
                }
                break;
            }
          ?>
        </div>
    </div>
    <?php } ?>
    <?php } ?>

    <?php tpl_includeFile('pageheader.html') ?>

    <?php flush()?>

    <?php if(tpl_getConf('sidebar') == 'left') { ?>

      <?php if(!arctic_tpl_sidebar_hide()) { ?>
        <div class="left_sidebar">
          <?php tpl_searchform() ?>
          <?php arctic_tpl_sidebar('left') ?>
        </div>
        <div class="right_page">
          <?php ($notoc) ? tpl_content(false) : tpl_content() ?>
        </div>
      <?php } else { ?>
        <div class="page">
          <?php tpl_content()?> 
        </div> 
      <?php } ?>

    <?php } elseif(tpl_getConf('sidebar') == 'right') { ?>

      <?php if(!arctic_tpl_sidebar_hide()) { ?>
        <div class="left_page">
          <?php ($notoc) ? tpl_content(false) : tpl_content() ?>
        </div>
        <div class="right_sidebar">
          <?php tpl_searchform() ?>
          <?php arctic_tpl_sidebar('right') ?>
        </div>
      <?php } else { ?>
        <div class="page">
          <?php tpl_content() ?> 
        </div> 
      <?php }?>

    <?php } elseif(tpl_getConf('sidebar') == 'both') { ?>

      <?php if(!arctic_tpl_sidebar_hide()) { ?>
        <div class="left_sidebar">
          <?php if(tpl_getConf('search') == 'left') tpl_searchform() ?>
          <?php arctic_tpl_sidebar('left') ?>
        </div>
        <div class="center_page">
          <?php ($notoc) ? tpl_content(false) : tpl_content() ?>
        </div>
        <div class="right_sidebar">
          <?php if(tpl_getConf('search') == 'right') tpl_searchform() ?>
          <?php arctic_tpl_sidebar('right') ?>
        </div>
      <?php } else { ?>
        <div class="page">
          <?php tpl_content()?> 
        </div> 
      <?php }?>

    <?php } elseif(tpl_getConf('sidebar') == 'none') { ?>
      <div class="page">
        <?php tpl_content() ?>
      </div>
    <?php } ?>

      <div class="stylefoot">
        <div class="meta">
          <div class="user">
          <?php tpl_userinfo()?>
          </div>
          <div class="doc">
          <?php tpl_pageinfo()?>
          </div>
        </div>
      </div>

    <div class="clearer"></div>

    <?php flush()?>

    <?php if(!$toolb) { ?>
    <?php if(!tpl_getConf('hideactions') || tpl_getConf('hideactions') && isset($_SERVER['REMOTE_USER'])) { ?>
    <?php if(!tpl_getConf('closedwiki') || (tpl_getConf('closedwiki') && isset($_SERVER['REMOTE_USER']))) { ?>
    <div class="bar" id="bar__bottom">
      <div class="bar-left">
        <?php 
          switch(tpl_getConf('wiki_actionlinks')) {
            case('buttons'):
                tpl_button('edit');
                tpl_button('history');
              break;
            case('links'):
                tpl_actionlink('edit');
                tpl_actionlink('history');
              break;
          }
        ?>
      </div>
      <div class="bar-right">
        <?php 
          switch(tpl_getConf('wiki_actionlinks')) {
            case('buttons'):
                tpl_button('media');
                tpl_button('subscribe');
                tpl_button('top');
              break;
            case('links'):
                tpl_actionlink('media');
                tpl_actionlink('subscribe');
                tpl_actionlink('top');
              break;
          }
        ?>
      </div>
    </div>
    <div class="clearer"></div>
    <?php } ?>
    <?php } ?>
    <?php } ?>

    <?php tpl_includeFile('footer.html') ?>

  </div>
</div>

<div class="no"><?php /* provide DokuWiki housekeeping, required in all templates */ tpl_indexerWebBug()?></div>
</body>
</html>
