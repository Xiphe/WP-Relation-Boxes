<?php
namespace Xiphe\relationboxes\views\boxinner;

use Xiphe\THEMASTER as TM;

$id = 'rb_' . $related->related_post_ID;
$unsavedClass = '';
if( isset( $ajaxInsert ) && $ajaxInsert === true ) {
	$unsavedClass = 'unsaved ';
}
	$HTML->s_li( array(
		'id' => $id,
		'class' => $unsavedClass . 'rb_listentry rb_' . $To->rewrite['slug'] . '_listentry' . $sep,
		'data-id' => $related->related_post_ID
	))
		->span( null, 'rb_drag_handler' )
		->span( $related->related_post->post_title, 'rb_list-title')
		->s_span( 'rb_showhover alignright' );
			// THIS IS SCIPPED IF INSERTED VIA AJAX
			if( !isset( $ajaxInsert ) || $ajaxInsert !== true ) {
				$HTML->a( __( 'Edit', 'relatonboxes' ), array(
					'href' => get_bloginfo( 'wpurl' ) . '/wp-admin/post.php?post=' . 
						$related->related_post_ID . '&action=edit',
					'title' => $To->labels->edit_item
				))
				->a( __( 'Hide', 'relatonboxes' ), array(
					'href' =>  get_bloginfo( 'wpurl' ) . '/wp-admin/post.php?post=' . 
						$related->related_post_ID . '&action=rb_hide' .
						'&rb__nonce=' . wp_create_nonce( 
							'rb_hide_'.$related->related_post_ID.'from'.$GLOBALS['post']->ID
						),
					'class' => 'rb_submithide',
					'title' => $To->labels->edit_item
				));
				$nonce = 'rb_release_'.$related->related_post_ID.'from'.$GLOBALS['post']->ID;
				$HTML->a( __( 'Release', 'relationboxes' ), array(
					'class' => 'rb_submitdelete',
					'href' => get_bloginfo( 'wpurl' ) . '/wp-admin/post.php?post=' . $post->ID 
						. '&action=edit&rb_release=' . $related->related_post_ID
						. '&rb__nonce=' . wp_create_nonce($nonce),
					'title' => __('Release', 'relatonboxes')
				) );
			}
			// END AJAX SKIP
			else {
				$HTML->a( __( 'Hide', 'relationboxes' ), array(
					'class' => 'rb_submithide unsaved',
					'href' => '#' . __( 'hide', 'relationboxes' ),
					'title' => __( 'Hide', 'relatonboxes' )
				) )
				->a( __( 'Release', 'relationboxes' ), array(
					'class' => 'rb_submitdelete unsaved',
					'href' => '#' . __( 'release', 'relationboxes' ),
					'title' => __( 'Release', 'relatonboxes' )
				) );
			}
$HTML->end( '#' . $id );