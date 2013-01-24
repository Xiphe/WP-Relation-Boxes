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
		'admin_head'
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
		$this->reg_adminJs('script');
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
	

	public function get_PTO($slug)
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
	
	public function register_relation($item, $connectionType0, $relatedItem)
	{
		$e = explode('-', $connectionType0);
		new XRBM\RelationDraft(array(
			'Pto' => self::get_PTO($item),
			'RelatedPto' => self::get_PTO($relatedItem),
			'type' => $e[0],
			'relatedType' => $e[1]
		));
	}
	
	// public function add_meta_boxes() {
// 		
		// foreach($this->aRelationDrafts as $box) {
			// $this->diebug();
			// add_meta_box( 
				// 'rb_'.$box['id'], 
				// $box['title'],
				// array($this, 'box_inner'),
				// $box['page'],
				// $box['context'],
				// $box['priority'],
				// $box['args']
			// );
		// }
	// }
	
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
	
} ?>