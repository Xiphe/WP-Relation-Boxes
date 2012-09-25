<?php
if (!empty($Master->aRelationDrafts)) {
	$HTML->s_style();
	foreach($Master->aRelationDrafts as $RelationDraft) {
		$HTML->blank('#rb_'.$RelationDraft->masterKey.' .inside { margin: 0px; padding: 0px; }');
	}
	$HTML->end();
}
?>