<?php
namespace Xiphe\relationboxes\models;

use Xiphe as X;
use Xiphe\relationboxes\classes as XRB;

class Relation extends \Xiphe\THEMASTER\core\THEWPMODEL {
	public $ID;
	public $post_ID;
	public $post_type;
	public $related_post_ID;
	public $related_post_type;
	public $post_order = false;
	public $hidden = false;

	public $deleted = false;
	public $unavailable = false;
	
	public $post;
	public $related_post;
	
	public $Mirror;
	
	public $_is_mirror = false;

	private $_Draft;
	
	public function init()
	{
		if ($this->_is_mirror === false) {
			$this->Mirror = new Relation(array(
				'_is_mirror' => true,
				'post_ID' => $this->related_post_ID,
				'related_post_ID' => $this->post_ID,
				'post_type' => $this->related_post_type,
				'related_post_type' => $this->post_type
			));

			if (isset($this->get_post)) {
				$this->get_post($this->get_post);
			}
		}
		
	}
	

	public function after_read($args)
	{
		if ($args == 'both' && $this->_is_mirror === false) {
			$this->Mirror->read();
		}
	}

	public function deleteError()
	{
		$this->debug(func_get_args());
	}

	public function after_delete()
	{
		if ($this->_is_mirror === false && $this->deleted) {
			$this->Mirror->delete();
		}
	}
	
	public function after_save($args = null)
	{
		if ($this->_is_mirror === false) {
			$this->Mirror->read();
			if (!isset($this->Mirror->ID) || $this->Mirror->ID == '' || $this->Mirror->ID == 0) {
				$this->Mirror->save();
			}
		}
	}

	public function get_draft()
	{
		if (!$this->_Draft) {
			$key = $this->get_post('this', 'post_type').$this->get_post('rel', 'post_type');
			$this->_Draft = \Xiphe\relationboxes\classes\Master::inst()->aRelationDrafts[$key];
		}
		return $this->_Draft;
	}
	
	public function userEditable()
	{
		if (current_user_can('edit_others_posts')) {
			return true;
		} elseif($this->get_post('rel', 'post_author') == $user_ID) {
			return true;
		}
		return false;
	}

	public function userAccessable()
	{
		if ($this->userEditable()) {
			return true;
		}
		if ($this->get_draft()->relatedType == 'n') {
			return true;
		} elseif (count($this->get_draft()->Mirror->get_relations($this->related_post_ID )) == 0) {
			return 'hidden';
		}
		return false;
	}

	/**
	 * gets the wp post object and/or the related wp post object
	 *
	 * @param 	string	$post	both|this|rel	the post(s) to return
	 * @param 	string	$path	optional path to specific parameter of the post
	 * @return	object			the requeste wp post object or object containing both
	 **/
	public function get_post($post = 'both', $path = null)
	{
		if ($post == 'both') {
			// return stdClass with both posts
			return (object) array(
				'post' => $this->get_post('this', $path),
				'related_post' => $this->get_post('rel', $path)
			);
		}

		// set IDs an keys
		if ($post == 'rel') {
			$target = 'related_post';
			$post_ID = $this->related_post_ID;
		} else {
			$target = 'post';
			$post_ID = $this->post_ID;
		}

		// check if exists
		if (null === $this->$target) {
			$post = get_post($post_ID);
			if ($post->post_status !== 'publish') {
				$this->unavailable = true;
				$this->$target = false;
				return false;
			}
			$this->$target = $post;
		}

		// return all or specific key
		if (!$path) {
			return $this->$target;
		} else {
			return X\THETOOLS::rget($this->$target, $path);
		}
	}
} ?>