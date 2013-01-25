WP Relation Boxes
=================

A Wordpress Plugin for easy enabling n-1, 1-1 and n-n relationships
between any Posttype.

See [Plugin Page](https://github.com/Xiphe/WP-Relation-Boxes) for details



Installation
------------

1. Install [!THE MASTER](https://github.com/Xiphe/-THE-MASTER)
2. Upload the WP Relation Boxes plugin directory to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. In your own Plugin or Theme use `\Xiphe\RelationBoxes\classes\Master::register_relation('post', 'n-n', 'page');`
5. See [Plugin Wiki](https://github.com/Xiphe/WP-Relation-Boxes/wiki) for usage details


Support
-------

I've written this project for my own needs so i am not willing to give
full support. Anyway, i am very interested in any bugs, hints, requests
or whatever. Please use the [github issue system](https://github.com/Xiphe/WP-Relation-Boxes/issues)
and i will try to answer.


Changelog
---------

### 1.1.1
+ better handling of post deletion and -trash actions

### 1.1.0
+ removed jquery ui and images as they are now taken from THEMASTER
+ compatibility with THEMASTER 3.1
+ remake of the js master
+ Hide link is hidden cause the functionality is not present.
+ Translation

### 1.0.1
+ Should delete 1-n and n-1 relations correctly on both sides now.
+ bugfix

### 1.0.0
+ **First public version**


Todo
----

+ Bughunting
+ Direct redirection for new Relations
+ Hide functionality


License
-------

Copyright (C) 2012 Hannes Diercks

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.