<?php
namespace Xiphe\relationboxes\views\boxinner;

use Xiphe as X;

$To = $RelationDraft->RelatedPto;
$Relation = X\THETOOLS::rget( $RelationDraft->get_relations(), '0');

if( is_object( $Relation ) ) {
	$id = '#rb_' . $To->name . '_edit';
	// $RBMaster->debug( $Relation );
	$HTML->s_div( $id .
		'|class=ui-widget misc-pub-section misc-pub-section-last rb_typeoneentry' )
	
		->div(null, '.rb_message hidden' )
		->s_div( '.rb_options' );
			$HTML->a(
				__('Edit', 'relationboxes'),
				array(
					'href' => get_bloginfo('wpurl').'/wp-admin/post.php?post='. 
						$Relation->related_post_ID.'&action=edit',
					'title' => $To->labels->edit_item
				)
			);

			$state = $Relation->hidden ? 'show' : 'hide';
			$title = ($Relation->hidden ? __('Show', 'relationboxes') : __('Hide', 'relationboxes'));
			$HTML->a(
				$title,
				array(
					'href' =>  add_query_arg(
						array(
							'rb_'.$state => $Relation->related_post_ID,
							'rb__nonce' => wp_create_nonce(
								'rb_'.$state.'_'.$Relation->related_post_ID.'from'.$GLOBALS['post']->ID
							)
						),
						X\THETOOLS::get_currentUrl(array(), array('post', 'action'), 'keep')
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
							'rb_release' => $Relation->related_post_ID,
							'rb__nonce' => wp_create_nonce(
								'rb_release_'.$Relation->related_post_ID.'from'.$GLOBALS['post']->ID
							)
						),
						X\THETOOLS::get_currentUrl(array(), array('post', 'action'), 'keep')
					),
					'title' => __('Release', 'relationboxes')
				)
			);

		$HTML->close( '.rb_options' )
		->div( __('Unsaved...', 'relationboxes'), '.rb_unsaved hidden' )
	->close( $id );
}
?>