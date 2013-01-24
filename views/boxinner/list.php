<?php
namespace Xiphe\relationboxes\views\boxinner;

use Xiphe\THEMASTER as TM;

$To = $RelationDraft->RelatedPto;

$id = 'rb_' . $To->rewrite['slug'] . '_list_wrap';
$HTML->s_div( 'id=' . $id . '|class=rb_list_wrap' );
	$lis = array();
	$serial = array();

	$HTML->sg_ul( '#rb_' . $To->rewrite['slug'] . '_list|.rb_list' );
	$relations = $RelationDraft->get_relations();
	if (isset($_GET['related_to'])) {
		$found = false;
		foreach ($relations as $related) {
			if ($related->related_post_ID == $_GET['related_to']) {
				$found = true;
				break;
			}
		}
		if (!$found) {
			$relations[-1] = new Relation(array(
				'post_ID' => intval($GLOBALS['post']->ID),
				'related_post_ID' => intval($_GET['related_to']),
				'unsaved' => true
			));
			$relations[-1]->get_post();
			ksort($relations);
		}
	}

	// TM\diebug($relations);

	foreach ($relations as $related) {
		
		// alternating the rows
		if( !isset( $s ) || $s != '' ) {
			$s = '';
		} else {
			$s = ' rb_alt';
		}
		 
		$serial[] = 'rb[]='.$related->related_post_ID;
		
		$Master->view( 'boxinner/listitem', array(
			'related' => $related,
			'To' => $To,
			'sep' => $s,
			'post' => $post,
			'user_ID' => $user_ID,
			'ajaxInsert' => (isset($related->unsaved) && $related->unsaved == true ? true : null)
		));
	}
	
	$HTML->end()
		 ->hidden( 'name=rb_' . $To->rewrite['slug'] . '_serial|class=rb_serial' .
			'|value=' . str_replace( '=', '\=', implode( '&', $serial ) ) 
		)
->end( '#' . $id );