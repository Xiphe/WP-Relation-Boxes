<?php
/*
Plugin Name: Relation Boxes
Plugin URI: https://github.com/Xiphe/WP-Relation-Boxes
Description: A Wordpress Plugin to establish n-1, 1-1 and n-n relationships between posts
Version: 1.2.0-dev2
Namespace: Xiphe\relationboxes
Date: 2013-05-08 09:49:25 +02.00
Author: Xiphe
Author URI: https://github.com/Xiphe/
Required Plugins: themaster, html
Update Server: http://wpupdates.xiphe.net/v1/
*/

namespace Xiphe\relationboxes;

if (function_exists('add_action')) {
	add_action('plugins_loaded', function () {
		if (!defined('WPMASTERAVAILABE') || WPMASTERAVAILABE != true) {
			add_action('admin_notices', function() {
				echo '<div class="error"><p>Warning - The Plugin "Relation Boxes" could not be initiated because Plugin <a href="http://plugins.red-thorn.de/libary/!themaster/">!THE MASTER</a> is not available.</p></div>';
			});
		} else {
			\Xiphe\THEMASTER\INIT();
		}
	});
}