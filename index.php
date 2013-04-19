<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
/**
 * Plugin Name: WordPress Google Form
 * Plugin URI: http://michaelwalsh.org/wordpress/wordpress-plugins/wpgform/
 * Description: Add Google Forms to a WordPress web site.  Display a Google Form directly into your posts, pages or sidebar.  Style the Google Form to match your existing theme and display a custom confirmation page after form submission.
 * Version: 0.46-beta-13
 * Build: 0.46-beta-13.$WCREV$
 * Last Modified:  $WCDATE$
 * Author: Mike Walsh
 * Author URI: http://www.michaelwalsh.org
 * License: GPL
 * 
 *
 * $Id$
 *
 * (c) 2011 by Mike Walsh
 *
 * @author Mike Walsh <mike@walshcrew.com>
 * @package wpGForm
 * @subpackage admin
 * @version $Rev$
 * @lastmodified $Date$
 * @lastmodifiedby $LastChangedBy$
 *
 */

define('WPGFORM_VERSION', '0.46-beta-9') ;

require_once('wpgform-core.php') ;
require_once('wpgform-post-type.php') ;

// Use the register_activation_hook to set default values
register_activation_hook(__FILE__, 'wpgform_register_activation_hook');

// Use the init action
add_action('init', 'wpgform_init' );

// Use the admin_menu action to add options page
add_action('admin_menu', 'wpgform_admin_menu');

// Use the admin_init action to add register_setting
add_action('admin_init', 'wpgform_admin_init' );

?>
