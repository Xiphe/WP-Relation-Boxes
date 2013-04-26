<?php
namespace Xiphe\relationboxes\classes;

use Xiphe\THEMASTER\core as TM;
use Xiphe\relationboxes\models as XRBM;

class Master extends TM\THEWPMASTER {
	public $singleton = true;
	public $HTML = true;
	
	public static $aRelationDrafts = array();
	public static $PTO = array();

	protected $actions_ = array(
		'wpinit' => 'init',
		'admin_head',
		// 'found_posts',
		'pre_get_posts|99',
		'posts_selection|5'
	);

	public function init()
	{
		$this->reg_model('RelationDraft');
		$this->reg_model( 
			'Relation', 
			array( 
				'table' => self::sGet_table_name()
			)
		);
		$this->get_instance('RelationController');
		$this->get_instance('AjaxController');
		
		$this->reg_adminCss('ui-wp/jquery-ui-1.10.0.custom.min');
		$this->reg_adminLess('style');
		$this->reg_adminJs('combobox');
		$this->reg_adminJs('Master');
		$this->reg_adminJsVar(
			'text',
			array(
				'typeOneRefresh' => __('Please save or update to get further options for this relation.', 'relationboxes'),
				'addButton' => __('Add', 'relationboxes')
			)
		);
	}
	
	public function wpinit()
	{
		if (is_admin()) {
			wp_enqueue_script('jquery');
			wp_enqueue_script('jquery-ui-button');
		}
	}
	
	public static function sGet_table_name()
	{
		return $GLOBALS['wpdb']->prefix.'rb_post_relationships';
	}

	/**
	 * The installation, hooked on plugin activation
	 *
	 * @return void
	 * @date Jul 17th 2011
	 */
	public function update()
	{
		$sql = "CREATE TABLE ".self::sGet_table_name()." (
			ID bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			post_ID bigint(20) NOT NULL,
			related_post_ID bigint(20) NOT NULL,
			post_type varchar(200) NOT NULL,
			related_post_type varchar(200) NOT NULL,
			post_order int(7) NOT NULL DEFAULT '0',
			hidden BOOLEAN NOT NULL DEFAULT '0',
			KEY rel_IDs (post_ID,related_post_ID),
			KEY rel_types (post_type,related_post_type)
	    );";
		require_once(ABSPATH.'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		
		update_option("rb_db_version", "1.1");
	}
	
	public static function get_PTO($slug)
	{
		if (!isset(self::$PTO[$slug])) {
			$PTO = get_post_type_object($slug);
			if (!is_object($PTO)) {
				throw new \Exception('ERROR: Post Type with slug "'.$slug.'" does not exist', 1);
				return false;
			} else {
				self::$PTO[$slug] = $PTO;
			}
		}
		return self::$PTO[$slug];
	}

	public static function register_relation($item, $connectionType0, $relatedItem)
	{
		$e = explode('-', $connectionType0);
		new XRBM\RelationDraft(array(
			'Pto' => self::get_PTO($item),
			'RelatedPto' => self::get_PTO($relatedItem),
			'type' => $e[0],
			'relatedType' => $e[1]
		));
	}

	public static function has_relations($post_ID, $related_post_types = '', $and = '')
	{
		global $wpdb;

		$table = self::sGet_table_name();

		if (!empty($related_post_types)) {
			if (is_string($related_post_types)) {
				$related_post_types = array($related_post_types);
			}

			foreach ($related_post_types as $key => $related_post_type) {
				if (!post_type_exists($related_post_type)) {
					unset($related_post_types[$key]);
				} else {
					$related_post_types[$key] = "'$related_post_type'";
				}
			}

			if (!empty($related_post_types)) {
				$and .= ' AND related_post_type IN ('.implode(',', $related_post_types).')';
			}
		}

		return intval($wpdb->get_var($wpdb->prepare(
			"SELECT COUNT(*)
			 FROM $table
			 WHERE post_ID = %d
			 $and
			 ",
			$post_ID
		)));
	}

	public static function get_relations(
		$post_ID,
		$related_post_types = '',
		$and = ''
	) {
		global $wpdb;

		$table = self::sGet_table_name();

		if (!empty($related_post_types)) {
			if (is_string($related_post_types)) {
				$related_post_types = array($related_post_types);
			}

			foreach ($related_post_types as $key => $related_post_type) {
				if (!post_type_exists($related_post_type)) {
					unset($related_post_types[$key]);
				} else {
					$related_post_types[$key] = "'$related_post_type'";
				}
			}

			if (!empty($related_post_types)) {
				$and .= ' AND related_post_type IN ('.implode(',', $related_post_types).')';
			}
		}

		return $wpdb->get_results($wpdb->prepare(
			"SELECT pst.*
			 FROM $wpdb->posts as pst
			 LEFT JOIN $table as rel
			 	ON rel.post_ID = %d
			 	$and

			 WHERE pst.post_status = 'publish'
			 AND pst.ID = rel.related_post_ID
			 ORDER BY rel.post_order ASC
			 ",
			$post_ID
		));
	}

	public function getRelationDrafts()
	{
		return self::$aRelationDrafts;
	}

	public function setRelationDraft($key, $draft)
	{
		self::$aRelationDrafts[$key] = $draft;
	}
	
	public function admin_head() {
		$this->view('adminhead');
	}
	
	public function box_inner($post, $args) {
		$args = $args['args'];
		$args['post'] = $post;
		$args['user_ID'] = $this->get_user('ID');
		
		$this->view('boxinner/newbutton', $args);
		$this->view('boxinner/existingdialoge', $args);
		if ($args['RelationDraft']->relatedType == 'n') {
			$this->view('boxinner/list', $args);
		} else {
			$this->view('boxinner/typeonedetail', $args);
		}
		$this->view('boxinner/nonce', $args);
		$this->view('boxinner/clear');
	}

	/**
	 * Append filters if posts relations should be appended inline.
	 * 
	 * @param  WP_Query $query
	 * @return null
	 */
	public function pre_get_posts($query)
	{
		if (isset($query->query_vars['rb_inline_relations'])) {
			$query->is_single = 0;
			$query->is_singular = 0;
			if ($query->is_page &&
				empty($query->queried_object_id) &&
				get_option('show_on_front') === 'page'
			) {
				$query->queried_object_id = intval(get_option('page_on_front'));
				$query->queried_object = get_post($query->queried_object_id);
				$query->query_vars['pagename'] = $query->queried_object->post_name;
			}
			add_filter('nav_menu_css_class', array($this, 'tmp_nav_menu_css_class'), 10, 2);

			add_filter('posts_join', array($this, 'tmp_posts_join'), 9);
			add_filter('posts_where', array($this, 'tmp_posts_where'), 9);
			add_filter('posts_orderby', array($this, 'tmp_posts_orderby'), 9);
			add_filter('posts_groupby', array($this, 'tmp_posts_groupby'), 9);
			add_filter('post_limits', array($this, 'tmp_post_limits'), 9);
			// add_filter('query', array($this, 'tmp_query'), 9);
		}
	}

	public function posts_selection($query)
	{
		global $wp_query;

		if (isset($wp_query->query_vars['rb_inline_relations'])) {
			remove_filter('posts_join', array($this, 'tmp_posts_join'), 9);
			remove_filter('posts_where', array($this, 'tmp_posts_where'), 9);
			remove_filter('posts_orderby', array($this, 'tmp_posts_orderby'), 9);
			remove_filter('posts_groupby', array($this, 'tmp_posts_groupby'), 9);
			remove_filter('post_limits', array($this, 'tmp_post_limits'), 9);
		}
	}

	public function tmp_query($query)
	{
		debug($query);

		remove_filter('query', array($this, 'tmp_query'), 9);
		
		return $query;
	}

	public function tmp_posts_join($join)
	{
		global $wp_query, $wpdb;

		$table = self::sGet_table_name();

		$join .= "LEFT JOIN $table AS rb_post_relations
            ON rb_post_relations.related_post_ID = $wpdb->posts.ID
            AND rb_post_relations.post_ID IN (
				SELECT ID
				FROM wp_posts
				WHERE 1 = 1
				$this->tmp_where
			)

            LEFT JOIN $wpdb->posts as rb_related_post
            ON rb_post_relations.related_post_ID = $wpdb->posts.ID
            AND rb_post_relations.post_ID = rb_related_post.ID";


		return $join;
	}

	public function tmp_posts_where($where)
	{
		global $wp_query, $wpdb;

		$this->tmp_where = $where;

		if (isset($wp_query->query_vars['rb_relation_type']) &&
			post_type_exists(($rel_type = $wp_query->query_vars['rb_relation_type']))
		) {
			$and2 = "rb_post_relations.related_post_type = '$rel_type'";
		} else {
			$and2 = "rb_post_relations.related_post_type != '$2'";
		}

		$sub_where = str_replace($wpdb->posts.'.', 'rb_related_posts2.', $where);

		$where = preg_replace(
			"/$wpdb->posts.(ID|post_type|post_name)[\s]?=[\s]?['\"]?([^\s'\"]+)['\"]?[\s]?/",
			"(
               $wpdb->posts.$1 = '$2'
               OR ( $wpdb->posts.ID = rb_post_relations.related_post_ID
                  AND $and2
                  AND rb_post_relations.post_ID IN (
                     SELECT rb_related_posts2.ID
                     FROM $wpdb->posts as rb_related_posts2
                     WHERE 1 = 1 $sub_where
                  )
               )
            )",
			$where
		);

		if (!isset($wp_query->query_vars['rb_include_hidden'])) {
			$where .= ' AND (rb_post_relations.hidden = 0 || rb_post_relations.hidden IS NULL)';
		}

		return $where;
	}

	public function tmp_posts_orderby($orderby)
	{
		global $wpdb, $wp_query;

		$rel_order = '';
		if (isset($wp_query->query_vars['rb_relation_order'])) {
			$rel_order = strtoupper($wp_query->query_vars['rb_relation_order']);
		}

		if (!in_array($rel_order, array('DESC', 'ASC'))) {
			$rel_order = 'ASC';
		}

		$orderby = preg_replace(
			"/$wpdb->posts\.([^\s]+)[\s]+(ASC|DESC)/",
			"IF(rb_post_relations.post_order IS NULL, $wpdb->posts.$1, rb_related_post.$1) $2,
               rb_post_relations.post_order $rel_order ",
			$orderby
		);

		return $orderby;
	}

	public function tmp_post_limits($limit)
	{
		global $wp_query;

		if (empty($limit)) {
			$limit .= 'LIMIT '.($wp_query->query_vars['paged']*$wp_query->query_vars['posts_per_page']).
			 ','.$wp_query->query_vars['posts_per_page'].' ';
		}

		return $limit;
	}

	public function tmp_posts_groupby($groupby)
	{
		global $wpdb;

		$groupby .= "$wpdb->posts.ID ";

		return $groupby;
	}

	public function tmp_nav_menu_css_class($classes, $menu_item)
	{
		global $wp_query;

		if (!empty($wp_query->queried_object_id) &&
			$wp_query->queried_object_id === intval($menu_item->object_id)
		) {
			$classes[] = 'current-menu-item';
		}

		return $classes;
	}

	public function found_posts($query)
	{
		global $wp_query, $wpdb;

		if(isset($wp_query->query_vars['rb_add_relations'])) {
			$IDs = array();

			$table = self::sGet_table_name();
			$limit = intval($wp_query->query_vars['rb_add_relations']);
			$limit = ($limit > 0 ? $limit : 5);
			$page = isset($wp_query->query_vars['rb_page']) ? $wp_query->query_vars['rb_page'] : 0;
			$type = false;
			if (isset($wp_query->query_vars['rb_type'])) {
				$type = $wp_query->query_vars['rb_type'];
			}
			$query = '';
			$rel_links = array();

			diebug($wp_query);

			array_map(function (&$post) use (&$query, &$rel_links, $wpdb, $table, $limit, $page, $type) {
				$rel_links[$post->ID] = &$post;

				$q = "( SELECT posts.*,
				               rel.post_ID as rel_parent,
				               rel.post_order as rel_order
			          FROM $wpdb->posts as posts
			          LEFT JOIN $table as rel ON rel.post_ID = %d
			          WHERE posts.ID = rel.related_post_ID
			          ".($type ? "AND posts.post_type = %s\n" : '').
			          "ORDER BY rel.post_order ASC
			          LIMIT %d,%d )
					  UNION ALL\n";

				$vals = array($q, $post->ID);
				if ($type !== false) {
					$vals[] = $type;
				}
				$vals[] = $page;
				$vals[] = $limit;

				$query .= call_user_func_array(array($wpdb, 'prepare'), $vals);
			}, $wp_query->posts);

			$query = trim($query, "UNION ALL\n");

			$results = $wpdb->get_results($query);

			array_map(function ($rel) use (&$rel_links) {
				if (!isset($rel_links[$rel->rel_parent]->rb_relations) ||
					!is_array($rel_links[$rel->rel_parent]->rb_relations)
				) {
					$rel_links[$rel->rel_parent]->rb_relations = array();
				}
				$rel_links[$rel->rel_parent]->rb_relations[$rel->rel_order] = $rel;
			}, $results);
		}
	}
}