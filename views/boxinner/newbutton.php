<?php
namespace Xiphe\relationboxes\views\boxinner;

if( $RelationDraft->relatedType != '1'
 || count( ( $Rels = $RelationDraft->get_relations() ) ) == 0
 || $Rels[0]->userEditable()
)  {
	$To = $RelationDraft->RelatedPto;
	$HTML->s_div( array(
			'id' => 'rb_' . $To->rewrite['slug'] . '_addnew',
			'class' => 'rb_addnew misc-pub-section'
		) )
		->a( $To->labels->add_new_item,
			array(
				'href' => get_bloginfo('wpurl') . '/wp-admin/post-new.php?post_type=' . 
					$To->rewrite['slug'] . '&related_to=' . $post->ID,
				'class' => 'button',
				'title' => $To->labels->add_new_item
			)
		)
	->end();
}