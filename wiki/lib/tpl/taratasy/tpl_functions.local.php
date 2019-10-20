<?php

/**
 * Use the sidebar page from current namespace if available, if not use the global one
 *
 * @author Symon Bent hendrybadao@gmail.com
 */

function _tpl_sidebar() {
    global $INFO, $conf;

    $id = $conf['sidebar'];
    $ns = $INFO['namespace'];

    do {
        $sidebar = $ns . ':' . $id;
        if (page_exists($sidebar)) {
            return $sidebar;
        }
        $ns = substr($ns, 0, strrpos($ns, ':'));
    } while ( ! empty($ns));
    return $id;
}


/**
 * Custom styles to allow different site/sidebar widths per namespace
 * Set in template's configuration (nsWidth).
 *
 * Syntax: "namespace site-width sidebar-width;namespace-2 site-width-2 sidebar-width-2"
 * (semicolons between namespaces sections, spaces within a section)
 * @author Symon Bent hendrybadao@gmail.com
 */
function _tpl_ns_styles() {
    global $INFO;
    $result = '';

    $cur_ns = $INFO['namespace'];
    $args = tpl_getConf('nsWidth');
    if (empty($args)) return;
    $args = explode(';', $args);
    foreach ($args as $arg) {
        list ($ns, $site_width, $sidebar_width) = explode(' ', $arg);
        $ns = ltrim(trim($ns), ':');
        $match = strpos($cur_ns, $ns);
        if ($match == 0 && $match !== false) {
            $result = '<style>' .
                        '.mode_show #dokuwiki__aside { width: ' . $sidebar_width . '; }' . DOKU_LF .
                        '.mode_show #dokuwiki__content { margin-left: -' . $sidebar_width . '; }' . DOKU_LF .
                        '.mode_show #dokuwiki__content .pad { margin-left: ' . $sidebar_width . '; }' . DOKU_LF .
                        '#dokuwiki__site .wrapper { max-width: ' . $site_width . '; }' . DOKU_LF .
                      '</style>';
            echo $result;
            break;
        }
    }
}

/**
 * Print info if the user is logged in
 * and show full name in that case
 *
 * SB: removed the 'Logged in as:' message; too much fluff
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Symon Bent <hendrybadao@gmail.com>
 * @return bool
 */
function _tpl_userinfo() {
    global $INFO;
    if(isset($_SERVER['REMOTE_USER'])) {
        print hsc($INFO['userinfo']['name']).' ('.hsc($_SERVER['REMOTE_USER']).')';
        return true;
    }
    return false;
}