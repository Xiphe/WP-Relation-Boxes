<?php
namespace Xiphe\relationboxes\models;

use Xiphe\relationboxes\classes as XRB;

class RelationDraft extends \Xiphe\THEMASTER\core\THEWPMODEL {
	public $Pto;
	public $RelatedPto;
	
	public $masterKey;
	
	public $type;
	public $relatedType;
	public $speakingType;
	
	public $_is_mirror = false;
	public $Mirror;

	public $aRelations;
	
	public static $Master;

	public function init()
	{
		if (empty(self::$Master)) {
			self::$Master = XRB\Master::inst();
		}
		
		$this->_gen_speakingType();
		$this->masterKey = $this->Pto->name.$this->RelatedPto->name;
		$drafts = self::$Master->getRelationDrafts();
		
		if (!isset($drafts[$this->masterKey])) {
			self::$Master->setRelationDraft($this->masterKey, $this);
		} elseif($this->Pto->name !== $this->RelatedPto->name) {
			throw new \ErrorException(
				sprintf(
					'ERROR: Multiple initiations for a relation between %s and %s.',
					$this->Pto->labels->name,
					$this->RelatedPto->labels->name
				),
				1
			);
			return false;
		} else {
			return;
		}

		add_action('add_meta_boxes', array($this, 'add_meta_box'));
		if (!$this->_is_mirror) {
			$this->Mirror = new RelationDraft(array(
				'_is_mirror' => true,
				'Pto' => $this->RelatedPto,
				'RelatedPto' => $this->Pto,
				'type' => $this->relatedType,
				'relatedType' => $this->type
			));
		} else {
			$key = $this->RelatedPto->name.$this->Pto->name;
			$this->Mirror = $drafts[$key];
		}
	}
	
	public function add_meta_box()
	{
		add_meta_box( 
			'rb_'.$this->masterKey, 
			$this->RelatedPto->labels->name,
			array(
				self::$Master, 'box_inner'
			),
			$this->Pto->name,
			'normal',
			'core',
			array(
				'RelationDraft' => $this
			)
		);
	}
	
	public function get_relations($post_ID = null, $addUnavailable = false)
	{
		if (empty($post_ID)) {
			$post_ID = $GLOBALS['post']->ID;
		}
		if (!is_array($this->aRelations)) {
			foreach (self::$Master->get_models( 
				'Relation', 
				array(
					// 'post_ID' => $post_ID,
					'post_type' => $this->Pto->name,
					'related_post_type' => $this->RelatedPto->name,
				), 
				'post_order',
				'ASC',
				array( 'get_post' => 'rel' )
			) as $Rel) {
				if (!$addUnavailable && $Rel->unavailable) {
					continue;
				}
				$this->aRelations[$Rel->post_ID][] = $Rel;
			}
		}
		if (!isset($this->aRelations[$post_ID])) {
			return array();
		}

		return $this->aRelations[$post_ID];
	}

	public function delete_relations($post_ID = null, $anyway = false)
	{
		if (empty($post_ID)) {
			$post_ID = $GLOBALS['post']->ID;
		}
		foreach ($this->get_relations($post_ID) as $Rel) {
			if (!$Rel->userEditable() && !$anyway) {
				// TODO: Display Error.
				return false;
			}
		}
		foreach ($this->get_relations($post_ID) as $key => $Rel) {
			$Rel->read('ID', 'both')->delete();
			if ($Rel->deleted == true) {
				unset($this->aRelations[$post_ID][ $key ]);
			}
		}
		if (!isset($this->aRelations[$post_ID]) ||
			count($this->aRelations[$post_ID]) == 0
		) {
			return true;
		}
		return false;
	}
	
	private function _gen_speakingType() {
		if (in_array( 
				strtolower(
					substr(
						$this->Pto->labels->singular_name,
						0,
						1
					)
				),
				array('a','e','i','o','u')
		)) {
			$this->speakingType = 'An '.$this->Pto->labels->singular_name;
		} else {
			$this->speakingType = 'A '.$this->Pto->labels->singular_name;
		}
		$this->speakingType .= ' can have ';
		if ($this->relatedType == 'n') {
			$this->speakingType .= 'many '.$this->RelatedPto->labels->name;
		} else {
			$this->speakingType .= 'one '.$this->RelatedPto->labels->singular_name;
		}
		$this->speakingType .= '.'; 
	}
} ?>