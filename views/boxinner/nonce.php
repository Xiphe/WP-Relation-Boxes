<?php
namespace Xiphe\relationboxes\views\boxinner;
	$To = $RelationDraft->RelatedPto;
	$From = $RelationDraft->Pto;
	$HTML->hidden(
		'name=rb_' . $To->rewrite['slug'] . '__nonce' . 
		'|value=' . wp_create_nonce( 'rb_' . $From->rewrite['slug'] . $To->rewrite['slug'] . '_mBQ3^b7s&e!!KKhN')
	);
	$HTML->div($To->rewrite['slug'], 'hidden rb_type');