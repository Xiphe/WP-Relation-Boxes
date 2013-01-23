<?php
namespace Xiphe\relationboxes\classes;

use Xiphe\THEMASTER\core as TM;
use Xiphe as X;
use Xiphe\relationboxes\models as XRBM;

class RelationController extends TM\THEWPMASTER {
	public $singleton = true;
	public $HTML = true;
	
	// private $_RAC; // RBRelationAccessController;
	
	protected $actions_ = array(
		'wpinit' => 'init',
		'save_post|999'
	);

	public function init()
	{
	}
	
	public function wpinit()
	{
		if (is_admin()) {
			if (isset($_GET['rb_release']) && !empty($_GET['rb_release'])) {
				$this->_release(X\THETOOLS::filter_getDataBy('rb'));
			}
			if ($GLOBALS['pagenow'] == 'post-new.php' &&
				isset($_GET['related_to']) &&
				!empty($_GET['related_to'])
			) {
				add_action('admin_notices', array($this, 'relatedMsg'));
			}
		}
	}
	
	private function _get_post_vars($post)
	{
		$r = array();
		foreach ($post as $key => $value) {
			if (substr($key, 0, 2) == 'rb') {
				$clean = substr($key, 3, strlen($key)-3);
				$to = substr($clean, 0, strpos($clean, '_'));
				$r[$to][str_replace('rb_'.$to.'_', '', $key)] = $value;
			}
		}
		foreach ($r as $key => $value) {
			$r[$key]['_pto'] = get_post_type_object($key);
		}
		return $r;
	}
	
	public function save_post($post_id)
	{
		// Do not autosave relations.
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return $post_id;
		}
		
		if (wp_is_post_revision($post_id) || empty($_POST)) {
			return $post_id;
		}

		// Filter POST array for rb_-prefixed keys
		foreach ($this->_get_post_vars($_POST) as $key => $postvar) {
			if($key == '') {
				continue; // Skip if key is empty (global Options)
			}

			// If serial is empty ( no relations jet )
			// and nothing should be added -> skip.
			if ((!isset($postvar['serial']) ||
					$postvar['serial'] == ''
				) &&
				(!isset( $postvar['addexisting']) ||
					(count($postvar['addexisting']) === 1 &&
						($postvar['addexisting'][0] == 'null' ||
							$postvar['addexisting'][0] == ''
	 	  	        	)
			 	  	)
			 	)
			) {
				continue;
			}

			// Verify the nonce
			if (!wp_verify_nonce( 
				$postvar['_nonce'],
				'rb_'.$_POST['post_type'].$key.'_mBQ3^b7s&e!!KKhN')
			) {
				continue;
			}
			
			// Build the addexisting array from serial if js is enabled.
			if (isset($_POST['rb_js']) &&
				isset($postvar['serial']) &&
				$_POST['rb_js'] === 'on'
			) {
				parse_str($postvar['serial'], $postvar['addexisting']);
				$postvar['addexisting'] = $postvar['addexisting']['rb'];
			}

			// Get the Relation Draft
			$Draft = Master::inst()->aRelationDrafts[ 
				$_POST['post_type'].$postvar['_pto']->rewrite['slug']
			];

			// If relation is type 1 try to delete the current relation.
			if ($Draft->relatedType == 1) {
				if (count($postvar['addexisting']) > 1) {
					throw new \Exception('Type1 Relation is about to get more than one relation', 1);
					return false;
				} elseif (!$Draft->delete_relations()) {
					$this->set_adminMessage(__(
						'Relation could not be updated because you do not have permissions to do that.',
						'relationboxes'
					), true);
					continue;
				}
			}

			if ($Draft->type == 1) {
				foreach ($postvar['addexisting'] as $addID) {
					if (!$Draft->Mirror->delete_relations($addID)) {
						$this->set_adminMessage(__(
							'Relation could not be updated because you do not have permissions to do that.',
							'relationboxes'
						), true);
						continue;
					}
				}
			}

			// Add the relations
			if (is_array($postvar['addexisting']) &&
				!empty($postvar['addexisting'])
			) {
				foreach ($postvar['addexisting'] as $key => $addex) {
					if ($addex != 'null') {
						$Relation = new XRBM\Relation( array( 
							'post_ID' => intval($_POST['post_ID']),
							'related_post_ID' => $addex,
							'post_type' => $_POST['post_type'],
							'related_post_type' => $postvar['_pto']->rewrite['slug'],
							'post_order' => $key+1,
						));
						// $this->debug( $Relation );
						$Relation->read('ID');

						// $this->diebug( $Relation );

						$Relation->save();
					}
				}
			}
		}
	} // END save_post_data()
	
	public function relatedMsg($relatedID)
	{
		$msg = sprintf(
			__('This %1$s will be related to "%2$s".', 'relationboxes'),
			Master::$PTO[$GLOBALS['post']->post_type]->labels->singular_name,
			$this->get_HTML()->ri_span(get_the_title($_GET['related_to']), '.rb_returnpostsave_title')
		);
		$this->set_adminMessage($msg, 'info');
	}

	private function _release($data)
	{
		if (!wp_verify_nonce(
			$data['_nonce'], 
			'rb_release_'.$data['release'].'from'.$_GET['post']
		)) {
			return false;
		}

		$Relation = new XRBM\Relation(array( 
			'post_ID' => intval($_GET['post']),
			'related_post_ID' => intval($data['release'])
		));

		if (!$Relation->userEditable()) {
			$this->set_adminMessage(
				sprintf(
					__('Unable to delete relation to **%s**. Access denied.', 'relationboxes'),
					$Relation->get_post('rel', 'post_title')
				),
				'error',
				true
			);
			return false;	
		}

		$url = $this->get_currentUrl();
		X\THETOOLS::filter_urlQuery($url, array('rb_release', 'rb__nonce'));

		$Relation->read('*', 'both');
		$Relation->delete();

		header('Location: '.$url);
		exit();
	}
	
}