<?php
namespace Xiphe\relationboxes\views\boxinner;
	$To = $RelationDraft->RelatedPto;
	$From = $RelationDraft->Pto;
	
	$HTML->hidden(
		'name=rb_' . $To->name . '__nonce' . 
		'|value=' . wp_create_nonce( 'rb_' . $From->name . $To->name . '_mBQ3^b7s&e!!KKhN')
	);
	$HTML->div($To->name, 'hidden rb_type');