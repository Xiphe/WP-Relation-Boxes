<?php
namespace Xiphe\RelationBoxes;

use Xiphe\THEMASTER as TM;

	$To = $RelationDraft->RelatedPto;
	$Relation = TM\THETOOLS::rget( $RelationDraft->get_relations(), '0');
if( is_object( $Relation ) ) {
	$id = '#rb_' . $To->rewrite['slug'] . '_edit';
	// $RBMaster->debug( $Relation );
	$HTML->s_div( $id .
		'|class=ui-widget misc-pub-section misc-pub-section-last rb_typeoneentry' )
	
		->div(null, '.rb_message hidden' )
		->s_div( '.rb_options' )
			->a( 
				__('Edit', 'relatonboxes'), 
				'href=' . get_bloginfo('wpurl') . '/wp-admin/post.php?post\=' . 
					$Relation->related_post_ID . '&action\=edit' .
				'|title=' . $To->labels->edit_item
			)
			->a( 
				__('Hide', 'relatonboxes'), array(
					'href' => get_bloginfo('wpurl') . '/wp-admin/post.php?post=' . 
						$Relation->related_post_ID . '&action=rb_hide' .
						'&rb__nonce=' . wp_create_nonce( 
							'rb_hide_' . $Relation->related_post_ID . $_GET['post'] . '_fYyBeTwo^+%Nl_xL'
						),
					'title' => $To->labels->edit_item
				)
			);
			$nonce = 'rb_release_'.$Relation->related_post_ID.'from'.$_GET['post'];
			TM\debug($nonce, 'nonce');
			$HTML->a( __('Release', 'relationboxes'), array(
				'class' => 'rb_submitdelete',
				'href' => get_bloginfo('wpurl') . '/wp-admin/post.php?post=' . $post->ID 
					. '&action=edit&rb_release=' . $Relation->related_post_ID
					. '&rb__nonce=' . wp_create_nonce($nonce),
				'title' => __('Release', 'relatonboxes')
			) )
		->end( '.rb_options' )
		->div( __('Unsaved...', 'relationboxes'), '.rb_unsaved hidden' )
	->end( $id );
}
?>