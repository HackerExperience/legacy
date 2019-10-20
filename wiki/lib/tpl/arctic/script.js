/**
 * javascript functionality for the arctic template
 * copies the mothod for dokuwikis TOC functionality
 * in order to keep the template XHTML valid
 */

/**
 * Adds the toggle switch to the TOC
 */
function addSbLeftTocToggle() {
    if(!document.getElementById) return;
    var header = jQuery('#sb__left__toc__header');
    if(!header.length) return;

    var obj          = document.createElement('span');
    obj.id           = 'sb__left__toc__toggle';
    obj.innerHTML    = '<span>&minus;</span>';
    obj.className    = 'toc_close';
    obj.style.cursor = 'pointer';

    //prependChild(header,obj);
    jQuery( header ).prepend( obj );
    //obj.parentNode.onclick = toggleSbLeftToc;
    jQuery( obj.parentNode ).bind( 'click', toggleSbLeftToc );
    try {
       obj.parentNode.style.cursor = 'pointer';
       obj.parentNode.style.cursor = 'hand';
    }catch(e){}
}

/**
 * This toggles the visibility of the Table of Contents
 */
function toggleSbLeftToc() {
  var toc = jQuery('#sb__left__toc__inside');
  var obj = jQuery('#sb__left__toc__toggle');
  if( toc.css( 'display' ) == 'none' ) {
    toc.css( 'display', 'block' );
    obj.innerHTML       = '<span>&minus;</span>';
    obj.className       = 'toc_close';
  } else {
    toc.css( 'display', 'none' );
    toc.style.display   = 'none';
    obj.innerHTML       = '<span>+</span>';
    obj.className       = 'toc_open';
  }
}

/**
 * Adds the toggle switch to the TOC
 */
function addSbRightTocToggle() {
    if(!document.getElementById) return;
    var header = jQuery('#sb__right__toc__header');
    if(!header.length) return;

    var obj          = document.createElement('span');
    obj.id           = 'sb__right__toc__toggle';
    obj.innerHTML    = '<span>&minus;</span>';
    obj.className    = 'toc_close';
    obj.style.cursor = 'pointer';

    //prependChild(header,obj);
    jQuery( header ).prepend( obj );

    //obj.parentNode.onclick = toggleSbRightToc;
    jQuery( obj.parentNode ).bind( 'click', toggleSbRightToc );
    try {
       obj.parentNode.style.cursor = 'pointer';
       obj.parentNode.style.cursor = 'hand';
    }catch(e){}
}

/**
 * This toggles the visibility of the Table of Contents
 */
function toggleSbRightToc() {
  var toc = jQuery('#sb__right__toc__inside');
  var obj = jQuery('#sb__right__toc__toggle');

  if( toc.css( 'display' ) == 'none' ) {
    toc.css( 'display', 'block' );
    obj.innerHTML       = '<span>&minus;</span>';
    obj.className       = 'toc_close';
  } else {
    toc.css( 'display', 'none' );
    obj.innerHTML       = '<span>+</span>';
    obj.className       = 'toc_open';
  }
}

var left_dw_index = jQuery('#left__index__tree').dw_tree({deferInit: true,
    load_data: function  (show_sublist, $clicky) {
        jQuery.post(
            DOKU_BASE + 'lib/exe/ajax.php',
            $clicky[0].search.substr(1) + '&call=index',
            show_sublist, 'html'
        );
    }
});  
var right_dw_index = jQuery('#right__index__tree').dw_tree({deferInit: true,
    load_data: function  (show_sublist, $clicky) {
        jQuery.post(
            DOKU_BASE + 'lib/exe/ajax.php',
            $clicky[0].search.substr(1) + '&call=index',
            show_sublist, 'html'
        );
    }
});  

jQuery(function(){
// from lib/scripts/index.js 
    var $tree = jQuery('#left__index__tree');
    left_dw_index.$obj = $tree;
    left_dw_index.init();

    var $tree = jQuery('#right__index__tree');
    right_dw_index.$obj = $tree;
    right_dw_index.init();

// add TOC events
    jQuery(addSbLeftTocToggle);
    jQuery(addSbRightTocToggle);

});

// vim:ts=4:sw=4:et:enc=utf-8:
