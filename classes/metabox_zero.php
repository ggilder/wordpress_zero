<?php
/**
* Metabox Zero. Make the meta boxes easier.
* Usage example:
* 
* $post_box_fields = array(
* 	array(
* 		'name' => '_page_title',
* 		'label' => 'Browser Window Title',
* 		'size' => 90
* 	),
* 	array(
* 		'name' => '_meta_description',
* 		'size' => 90
* 	),
* );
* 
* $post_box_info = array(
* 	'id' => 'my_post_meta',
* 	'title' => 'Post Info',
* 	'page' => 'post',
* 	'position' => 'normal',
* 	'priority' => 'high'
* );
* 
* new MetaboxZero($post_box_info, $post_box_fields);
* 
*/
class MetaboxZero
{
	protected $fields = null;
	protected $info = null;
	
	function __construct($info, $fields){
		// default info
		$this->info = array(
			'id' => 'metabox_zero_'.rand(),
			'title' => 'Custom Info',
			'page' => 'post',
			'position' => 'normal',
			'priority' => 'high'
		);
		// merge in argument
		$this->info = array_merge($this->info, $info);
		// set noncename based on id
		$this->noncename = $this->info['id'].'_nonce';
		
		// set up fields
		$this->fields = $this->validateFields($fields);
		
		// initialize wordpress hooks
		$this->initMetaBox();
	}
	
	function registerMetabox()
	{
		// allow for multiple page types by forcing page to be an array
		if (!is_array($this->info['page'])){
			$this->info['page'] = array($this->info['page']);
		}
		// then iterate through page array
		foreach ($this->info['page'] as $page){
			add_meta_box( $this->info['id'], $this->info['title'], array($this, 'renderMetabox'),
				$page, $this->info['position'], $this->info['priority'] );
		}
	}
	
	private function validateFields($input)
	{
		$fields = array();
		$usedFieldNames = array();
		foreach($input as $fieldArray){
			if ($fieldArray['name']){
				if (in_array($fieldArray['name'], $usedFieldNames)){
					throw new Exception('Fatal attempted reuse of field name '.$fieldArray['name'].'!');
				}
				$usedFieldNames[] = $fieldArray['name'];
				$fields[] = $fieldArray;
			}
		}
		return $fields;
	}
	
	function renderMetabox()
	{
		global $post;
		if ($this->info['render_callback']){
			call_user_func($this->info['render_callback'], $post);
			return;
		}
		
		$out = HTMLHelper::input(array(
			'type' => 'hidden', 'name' => $this->noncename, 'value' => wp_create_nonce(basename(__FILE__))
		));
		
		foreach($this->fields as $fieldOptions){
			$defaults = array(
				'type' => 'text',
				'size' => 30,
				'style' => 'display:block;'
			);
			$field = array_merge($defaults, $fieldOptions);
			if (!isset($field['label'])){
				$field['label'] = NameHelper::naturalizeFieldName($field['name']);
			}
			if ($field['label']){
				$field['label'] = NameHelper::prepLabelName($field['label']);
			}
			
			$meta = get_post_meta($post->ID, $field['name'], true);
			
			$out .= HTMLHelper::label(array('for'=>$field['name'], 'style'=>'display:block;'), $field['label']);
			
			$out .= HTMLHelper::formfield(array(
				'type' => $field['type'],
				'name' => $field['name'],
				'id' => $field['name'],
				'size' => $field['size'],
				'style' => $field['style'],
				'value' => $meta,
			));
			
			if ($field['notes']){
				$out .= '<br>'.$field['notes'];
			}
		}
		
		echo $out;
	}
	
	function saveMetaboxContent($post_id)
	{
		// verify nonce
		if (!wp_verify_nonce($_POST[$this->noncename], basename(__FILE__))) {
			return $post_id;
		}
		// check autosave
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return $post_id;
		}
		// check permissions
		if ('page' == $_POST['post_type']) {
			if (!current_user_can('edit_page', $post_id)) {
				return $post_id;
			}
		} elseif (!current_user_can('edit_post', $post_id)) {
			return $post_id;
		}
		
		// save fields
		foreach ($this->fields as $field){
			$old = get_post_meta($post_id, $field['name'], true);
			$new = $_POST[$field['name']];
			if ($new !== '' && $new != $old) {
				update_post_meta($post_id, $field['name'], $new);
			} elseif (($new == '') && ($old != '')) {
				delete_post_meta($post_id, $field['name'], $old);
			}
		}
	}
	
	function initMetaBox()
	{
		$t1 = add_action('admin_menu', array($this, 'registerMetabox'));
		$t2 = add_action('save_post', array($this, 'saveMetaboxContent'));
	}
}

?>