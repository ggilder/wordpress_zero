<?php
/**
 * Class to abstract out some common functionality for WordPress widgets.
 * Inherit from this class in your widget - e.g.:
 * class MyWidget extends WidgetZero { ... }
 *
 * @author Gabriel Gilder
 */

abstract class WidgetZero extends WP_Widget {
	private $fields = array();
	abstract public function render($fields);
	
	final function set_fields($fields)
	{
		$this->fields = $this->sanitizeFields($fields);
	}
	
	private function sanitizeFields($input)
	{
		$fields = array();
		$usedFieldNames = array();
		foreach($input as $fieldArray){
			if (is_array($fieldArray) && $fieldArray['name']){
				if (in_array($fieldArray['name'], $usedFieldNames)){
					throw new Exception('Fatal attempted reuse of field name '.$fieldArray['name'].'!');
				}
				$usedFieldNames[] = $fieldArray['name'];
				$fields[] = $fieldArray;
			}
		}
		return $fields;
	}
	
	final function get_field_info($name){
		foreach( $this->fields as $field )
		{
			if ($field['name'] == $name) return $field;
		}
		return false;
	}
	
	final function get_field_value($name){
		if (!$this->instance){
			throw new Exception("get_field_value called in non-render context!");
		}
		$field = $this->get_field_info($name);
		if ($field['default'] && $this->instance[$name] == ''){
			return $field['default'];
		} elseif ($field['type'] == 'select') {
			return $this->sanitize_option($this->instance[$name], $field);
		} else {
			return esc_attr($this->instance[$name]);
		}
	}
	
	final function get_all_field_values()
	{
		$data = array();
		foreach ($this->fields as $field){
			$data[$field['name']] = $this->get_field_value($field['name']);
		}
		return $data;
	}
	
	final function sanitize_option($option, $field){
		if (array_key_exists($option, $field['optionlist'])){
			return $option;
		} else {
			$keys = array_keys($field['optionlist']);
			return $keys[0];
		}
	}
	
	final function form_all_fields(&$instance){
		$out = '';
		foreach ($this->fields as $field) {
			$out .= $this->form_field($field, $instance[$field['name']]);
		}
		return $out;
	}
	
	final function form_field($field, &$value){
		$this->add_widget_id_and_name($field);
		$out = $this->label_for($field);
		
		$field['tag']['type'] = $field['type'];
		if ($field['size']) $field['tag']['size'] = $field['size'];
		$field['tag']['value'] = $value;
		
		switch($field['type'])
		{
			case 'select':
			case 'menu':
			case 'checkbox':
			case 'radio':
				$field['tag']['options'] = $field['optionlist'];
				break;
			case '':
			case 'text':
				if (!$field['tag']['class'] && !$field['tag']['size']) $field['tag']['class'] = 'widefat';
				break;
		}
		$out .= HTMLHelper::formfield($field['tag']);
		
		$out .= HTMLHelper::br().$this->note_for($field);
		return HTMLHelper::p(array(), $out);
	}
	
	final function add_widget_id_and_name(&$field)
	{
		if (!is_array($field['tag'])){
			$field['tag'] = array();
		}
		$field['tag']['id'] = $this->get_field_id($field['name']);
		$field['tag']['name'] = $this->get_field_name($field['name']);
	}
	
	final function label_for($field){
		$label = $field['label'];
		if (!$label) $label = NameHelper::naturalizeFieldName($field['name']);
		$label = NameHelper::prepLabelName($label);
		$options = array('for'=>$field['tag']['id'], 'class'=>$field['tag']['name'].'_label');
		if (!$field['type'] || $field['type'] == 'text'){
			$options['style'] = 'display:block;'.$options['style'];
		} else {
			$options['style'] = 'margin-right:0.5em;'.$options['style'];
		}
		return HTMLHelper::label($options, $label);
	}
	
	final function note_for($field){
		$out = '';
		if ($field['note']){
			$out = HTMLHelper::tag('small', array(), $field['note']);
		}
		return $out;
	}
	
	function update($new_instance, $old_instance){
		$instance = $old_instance;
		foreach ($this->fields as $field){
			$fieldname = $field['name'];
			switch ($field['type']){
				case 'select':
					// validate menu selection
					$instance[$fieldname] = $this->sanitize_option($new_instance[$fieldname], $field);
					break;
				case 'toggle':
					$instance[$fieldname] = ((!empty($new_instance[$fieldname])) && ($new_instance[$fieldname] != 'false')) ? true : false;
					break;
				default:
					$instance[$fieldname] = $new_instance[$fieldname];
			}
		}
		return $instance;
	}
	
	function form( $instance ) {
		echo $this->form_all_fields($instance);
	}
	
	final function widget($args, $instance) {
		$this->instance = $instance;
		$this->args = $args;
		$this->render($this->get_all_field_values());
	}
	
	final function template($arg) {
		if (!array_key_exists($arg, $this->args)){
			throw new Exception("Invalid template part {$arg}!");
		}
		return $this->args[$arg];
	}
}


?>