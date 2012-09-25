<?php
/*
Plugin Name: Relation Boxes
Plugin URI: https://github.com/Xiphe/WP-Relation-Boxes
Description: A Wordpress Plugin for easy enabling n-1, 1-1 and n-n relationships
Version: 1.0.1
Namespace: Xiphe\RelationBoxes
Date: 25.09.2012 10:02:05 +02:00
Author: Xiphe
Author URI: https://github.com/Xiphe/
Required Plugins: !themaster, !html
Updatable: true
*/

namespace Xiphe\RelationBoxes;

use Xiphe\THEMASTER as TM;

if( !defined( 'WPMASTERAVAILABE' ) || WPMASTERAVAILABE != true ) {
	add_action( 'admin_notices', function() {
		echo '<div class="error"><p>Warning - The Plugin "Relation Boxes" could not be initiated because Plugin <a href="http://plugins.red-thorn.de/libary/!themaster/">!THE MASTER</a> is not available.</p></div>';
	});
} else {
	TM\INIT();
}
?>