<?php
namespace Xiphe\relationboxes;

use Xiphe\THEMASTER as TM;

$To = $RelationDraft->RelatedPto;

	if ($RelationDraft->relatedType == 'n'
	 || count($RelationDraft->get_relations()) == 0
	) {
		$selectLabel = sprintf(__('Add existing %s', 'relationboxes'), $To->labels->name);
		$lastClass = ' misc-pub-section-last';
		$selected = null;
	} else {
		$selectLabel = sprintf(__('Related %s:', 'relationboxes'), $To->labels->singular_name);
		$lastClass = '';
		$selected = TM\THETOOLS::rget($RelationDraft->get_relations(), '0|related_post|ID');
	}

$id = '#rb_'.$To->rewrite['slug'].'_addexisting';

$HTML->s_div($id.'|.ui-widget rb_addexisting misc-pub-section'.$lastClass);
	
	$args = array('null' => __(' - none - ', 'relationboxes'));
	$subposts = get_posts('post_type=' . $To->rewrite['slug']);
	foreach ($subposts as $subpost) {
		$relinit = array(
			'related_post_ID' => $subpost->ID
		);
		if (isset($_GET['post'])) {
			$relinit['post_ID'] = $_GET['post'];
		}
		$TestRel = new Relation($relinit);
		if(($relAccess = $TestRel->userAccessable()) === false) {
			unset($TestRel);
			continue;
		}
		unset($TestRel);

		/* exclude existing relations from dropdown */
		$found = false;
		if ($RelationDraft->relatedType == 'n') {
			foreach($RelationDraft->get_relations() as $Relation) {
				if($Relation->related_post_ID == $subpost->ID) {
					$found = true;
					break;
				}
			}
		}
		if(!$found) {
			$args[$subpost->ID] = array(
				'inner' => $subpost->post_title,
				'attrs' => array(
					'data-nonce' => wp_create_nonce( 
						'rb_ajax_listelement_'.$subpost->post_title
					),
					'data-inner' => strtolower( $subpost->post_title )
				)
			);
			if (isset($_GET['related_to'])
			 && $_GET['related_to'] == $subpost->ID
			) {
				if ($RelationDraft->relatedType == 1) {
					$args[$subpost->ID]['attrs']['selected'] = 'selected';
				} else {
					unset($args[$subpost->ID]);
				}
			}
		}
	}
	
	// $RBMaster->debug( $RelationDraft );

	$selArgs = array(
		'class' => 'rb_addexisting_select',
		'id' => ( $slctid = 'rb_' . $To->rewrite['slug'] . '_addexisting_select' ),
		'name' => 'rb_' . $To->rewrite['slug'] . '_addexisting[]',
	);

	if( $RelationDraft->relatedType == 'n' ) {
		$selArgs['multiple'] = 'multiple';
		$selArgs['style'] = 'height: 150px;';
	}

	if( $RelationDraft->relatedType == '1'
	 && count( ( $Rels = $RelationDraft->get_relations() ) ) > 0
	 && !$Rels[0]->userEditable()
	)  {
		$infomsg = sprintf(
			__( 'You must not edit this Relation because you are not allowed to edit \'%1$s\'. 
%2$s can only handle one %3$s. So if you could change \'%1$s\' 
to another %3$s the relation on \'%1$s\' to this %4$s would be deleted and 
the apperance of \'%1$s\' eventually would change, which should be a decision 
made by the autor of \'%1$s\'.',
				'relationboxes' 
			),
			$Rels[0]->related_post->post_title,
			$RelationDraft->Pto->labels->name,
			$RelationDraft->RelatedPto->labels->singular_name,
			$RelationDraft->Pto->labels->singular_name
		);
		$HTML->b_div(
			sprintf(
				'%1$s %2$s: **%3$s**',
				__('Related', 'relationboxes'),
				$RelationDraft->RelatedPto->labels->singular_name,
				$Rels[0]->related_post->post_title
			),
			'rb_uneditabletitle'
		)
		->abbr( 'info', array(
			 'class' => 'rb_hint',
			 'title' => $infomsg
		));
	} else {
		$HTML->select(
			$selArgs,
			$args,
			$selected,
			$selectLabel
		);
	}

	if( $RelationDraft->relatedType == 'n' ) {
		$HTML->script('jQuery("#'.$slctid.'").removeAttr("multiple").css({height : "auto"});', '.rb_removescript')
			->button( __('Add', 'relationboxes'), '.button rb_add hidden|disabled=');
	}

	$HTML->div( $RelationDraft->relatedType, 'hidden rb_type' );
	if( $RelationDraft->relatedType == '1' ) {
		$HTML->div( TM\THETOOLS::rget( $RelationDraft->get_relations(), '0|related_post_ID'), 'hidden rb_original' );
	}
$HTML->dclear()->end( $id );

if( $RelationDraft->relatedType == 'n' )
	$HTML->dclear()->div( null, 'rb_seperator' );
?>