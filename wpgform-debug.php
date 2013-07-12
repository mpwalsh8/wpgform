<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
/**
 * GForm functions.
 *
 * $Id$
 *
 * (c) 2011 by Mike Walsh
 *
 * @author Mike Walsh <mike@walshcrew.com>
 * @package wpGForm
 * @subpackage functions
 * @version $Revision$
 * @lastmodified $Date$
 * @lastmodifiedby $Author$
 *
 */

global $wpgform_debug_content ;

$wpgform_debug_content = '' ;
add_action('init', 'wpgform_debug', 0) ;
add_action('wp_footer', 'wpgform_show_debug_content') ;

/**
 * Debug action to examine server variables
 *
 */
function wpgform_debug()
{
    global $wp_filter ;

    wpgform_error_log($_POST) ;

    if (!is_admin())
    {
        wpgform_whereami(__FILE__, __LINE__, '$_SERVER') ;
        wpgform_preprint_r($_SERVER) ;
        wpgform_whereami(__FILE__, __LINE__, '$_ENV') ;
        wpgform_preprint_r($_ENV) ;
        wpgform_whereami(__FILE__, __LINE__, '$_POST') ;
        wpgform_preprint_r($_POST) ;
        wpgform_whereami(__FILE__, __LINE__, '$_GET') ;
        wpgform_preprint_r($_GET) ;
        wpgform_whereami(__FILE__, __LINE__, 'locale') ;
        wpgform_preprint_r(get_locale()) ;
        wpgform_preprint_r(setlocale(LC_ALL,NULL)) ;

        if (array_key_exists('init', $wp_filter))
        {
            wpgform_whereami(__FILE__, __LINE__, '$wp_filter[\'init\']') ;
            wpgform_preprint_r($wp_filter['init']) ;
        }
        if (array_key_exists('template_redirect', $wp_filter))
        {
            wpgform_whereami(__FILE__, __LINE__, '$wp_filter[\'template_redirect\']') ;
            wpgform_preprint_r($wp_filter['template_redirect']) ;
        }
    }
}

/**
 * Debug action to display debug content in a DIV which can be toggled open and closed.
 *
 */
function wpgform_show_debug_content()
{
    global $wpgform_debug_content ;
?>
<style>
h2.wpgform-debug {
    text-align: center;
    background-color: #ffebe8;
    border: 2px solid #ff0000;
}

div.wpgform-debug {
    padding: 10px;
    direction: ltr;
}

div.wpgform-debug h2 {
    background-color: #f00;
}

div.wpgform-debug h3 {
    padding: 10px;
    color: #fff;
    font-weight: bold;
    border: 1px solid #000000;
    background-color: #024593;
}

div.wpgform-debug pre {
    color: #000;
    text-align: left;
    border: 1px solid #000000;
    background-color: #c6dffd;
}
</style>
<script type="text/javascript">
jQuery(document).ready(function($) {
        $("div.wpgform-debug").hide();
        $("a.wpgform-debug-wrapper").show();
        $("a.wpgform-debug-wrapper").text("Show wpGForm Debug Content");
 
    $("a.wpgform-debug-wrapper").click(function(){
    $("div.wpgform-debug").slideToggle();

    if ($("a.wpgform-debug-wrapper").text() == "Show wpGForm Debug Content")
        $("a.wpgform-debug-wrapper").text("Hide wpGForm Debug Content");
    else
        $("a.wpgform-debug-wrapper").text("Show wpGForm Debug Content");
    });
});
</script>
<div class="wpgform-debug">
    <?php echo $wpgform_debug_content ; ?>
</div>
<?php
}

/**
 * wpgform_send_headers()
 *
 * @return null
 */
function wpgform_send_headers()
{
    header('Cache-Control: no-cache, must-revalidate'); // HTTP/1.1
    header('Expires: ' . date(DATE_RFC822, strtotime('yesterday'))); // Date in the past
    header('X-Frame-Options: SAMEORIGIN'); 
}

/**
 * Debug "where am i" function
 */
function wpgform_whereami($f, $l, $s = null)
{
    global $wpgform_debug_content ;

    if (is_null($s))
    {
        $wpgform_debug_content .= sprintf('<h3>%s::%s</h3>', basename($f), $l) ;
        error_log(sprintf('%s::%s', basename($f), $l)) ;
    }
    else
    {
        $wpgform_debug_content .= sprintf('<h3>%s::%s::%s</h3>', basename($f), $l, $s) ;
        error_log(sprintf('%s::%s::%s', basename($f), $l, $s)) ;
    }
}

/**
 * Debug functions
 */
function wpgform_preprint_r()
{
    global $wpgform_debug_content ;

    $numargs = func_num_args() ;
    $arg_list = func_get_args() ;
    for ($i = 0; $i < $numargs; $i++) {
	    $wpgform_debug_content .= sprintf('<pre style="text-align:left;">%s</pre>', print_r($arg_list[$i], true)) ;
    }
    wpgform_error_log(func_get_args()) ;
}

/**
 * Debug functions
 */
function wpgform_htmlspecialchars_preprint_r()
{
    global $wpgform_debug_content ;

    $numargs = func_num_args() ;
    $arg_list = func_get_args() ;
    for ($i = 0; $i < $numargs; $i++) {
	    $wpgform_debug_content .= sprintf('<pre style="text-align:left;">%s</pre>', htmlspecialchars(print_r($arg_list[$i], true))) ;
    }
    wpgform_error_log(func_get_args()) ;
}
/**
 * Debug functions
 */
function wpgform_error_log()
{
    $numargs = func_num_args() ;
    $arg_list = func_get_args() ;
    for ($i = 0; $i < $numargs; $i++) {
	    error_log(print_r($arg_list[$i], true)) ;
    }
}
?>
