<?php
namespace Xiphe\relationboxes\views\boxinner;

use Xiphe\THEMASTER as TM;
use Xiphe\THETOOLS;

$id = 'rb_' . $related->related_post_ID;
$addClass = '';
if (isset($ajaxInsert) && $ajaxInsert === true) {
	$addClass .= 'rb_unsaved ';
}

if ($related->hidden) {
	$addClass .= 'rb_hidden ';
}

	$HTML->s_li( array(
		'id' => $id,
		'class' => $addClass . 'rb_listentry rb_' . $To->name . '_listentry' . $sep,
		'data-id' => $related->related_post_ID
	))
		->span( null, 'rb_drag_handler' )
		->span( $related->related_post->post_title, 'rb_list-title')
		->s_span( 'rb_showhover alignright' );

			// THIS IS SKIPPED IF INSERTED VIA AJAX

			if( !isset( $ajaxInsert ) || $ajaxInsert !== true ) {
				$HTML->a(
					__('Edit', 'relationboxes'),
					array(
						'href' => get_bloginfo('wpurl').'/wp-admin/post.php?post='. 
							$related->related_post_ID.'&action=edit',
						'title' => $To->labels->edit_item
					)
				);

				$state = $related->hidden ? 'show' : 'hide';
				$title = ($related->hidden ? __('Show', 'relationboxes') : __('Hide', 'relationboxes'));
				$HTML->a(
					$title,
					array(
						'href' =>  add_query_arg(
							array(
								'rb_'.$state => $related->related_post_ID,
								'rb__nonce' => wp_create_nonce(
									'rb_'.$state.'_'.$related->related_post_ID.'from'.$GLOBALS['post']->ID
								)
							),
							THETOOLS::get_currentUrl(array(), array('post', 'action'), 'keep')
						),
						'class' => 'rb_submit'.$state,
						'title' => $title
					)
				);

				$HTML->a(
					__('Release', 'relationboxes'),
					array(
						'class' => 'rb_submitdelete',
						'href' => add_query_arg(
							array(
								'rb_release' => $related->related_post_ID,
								'rb__nonce' => wp_create_nonce(
									'rb_release_'.$related->related_post_ID.'from'.$GLOBALS['post']->ID
								)
							),
							THETOOLS::get_currentUrl(array(), array('post', 'action'), 'keep')
						),
						'title' => __('Release', 'relationboxes')
					)
				);
			}

			// END AJAX SKIP

			else {
				$HTML->a(
					__('Hide', 'relationboxes'),
					array(
						'class' => 'rb_submithide unsaved',
						'href' => '#' . __('hide', 'relationboxes'),
						'title' => __('Hide', 'relationboxes')
					)
				)
				->a(
					__('Release', 'relationboxes'),
					array(
						'class' => 'rb_submitdelete unsaved',
						'href' => '#' . __('release', 'relationboxes'),
						'title' => __('Release', 'relationboxes')
					)
				);
			}


$HTML->end('#'.$id);