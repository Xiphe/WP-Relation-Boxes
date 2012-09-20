<?php
namespace Xiphe\RelationBoxes;

use Xiphe\THEMASTER as TM;

class AjaxController extends TM\THEWPMASTER {
	public $singleton = true;
	public $HTML = true;
	
	protected $actions_ = array(
		'wp_ajax_rb_getLi'
	);
	
	private function asCheck() {
		if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			$this->_r['status'] = 'autosave';
			$this->_r( 'autosave' );
		}
	}

	public function init() {
		$this->get_HTML()->ajaxMode(true);
		$this->get_HTML()->tab = ' ';
	}
	
	public function wp_ajax_rb_getLi() {
		$this->asCheck();
		
		$Relation = new Relation( array( 
			'post_ID' => intval($_POST['ID']),
			'related_post_ID' => intval($_POST['relID']),
			'post_type' => $_POST['type'],
			'related_post_type' => $_POST['relType'],
			'get_post' => 'rel'
		) );
		
		if( !wp_verify_nonce( 
			$_POST['nonce'],
			'rb_ajax_listelement_' . $Relation->related_post->post_title . $this->get_user( 'ID' )
		) ) {
			$this->_exit( 'error', 'Invalid nonce', 111 );
		}
		
		if( !$Relation->userEditable() ) {
			$this->_exit( 'error', 'No rights to edit related post', 211 );
		}

		// $this->debug($Relation->get_draft()->RelatedPto );
		$this->_r['content'] = $this->get_view( 'boxinner/listitem', array(
			'related' => $Relation,
			'To' => $Relation->get_draft()->RelatedPto,
			'sep' => '',
			// 'post' => $Relation->post,
			// 'user_ID' => $this->get_user('ID'),
			'ajaxInsert' => true
		));
		$this->_exit( 'ok', '', 0 );
	}
	
	// private function _r( $msg = null, $errorCode = null ) {
	// 	if( $msg != null ) {
	// 		$this->_r['msg'] = $msg;
	// 	}
	// 	if( $errorCode != null ) {
	// 		$this->_r['errorCode'] = $errorCode;
	// 	}
		
	// 	echo json_encode( $this->_r );
	// 	exit;
	// }
	
} ?>
