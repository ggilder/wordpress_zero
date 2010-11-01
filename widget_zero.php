<?php
/**
 * Class to abstract out some common functionality for WordPress widgets.
 * Inherit from this class in your widget - e.g.:
 * class My_Widget extends Widget_Zero { ... }
 *
 * @author Gabriel Gilder
 */

class Widget_Zero extends WP_Widget {
	
	function add_field($name, $options=array()) {
		$this->fields[$name] = $options;
	}
	
	function get_field_value($name, &$instance){
		if ($this->fields[$name]['default'] && !$instance[$name]){
			return $this->fields[$name]['default'];
		} elseif ($this->fields[$name]['type'] == 'select') {
			return validate_option($instance[$name], $name);
		} else {
			return esc_attr($instance[$name]);
		}
	}
	
	function validate_option($option, $fieldname){
		if (array_key_exists($option, $this->fields[$fieldname]['optionlist'])){
			return $option;
		} else {
			$keys = array_keys($this->fields[$fieldname]['optionlist']);
			return $keys[0];
		}
	}
	
	function form_all_fields(&$instance){
		$fields = $this->ordered_field_names();
		$out = '';
		foreach ($fields as $fieldname) {
			$out .= $this->form_field($fieldname, $instance[$fieldname]);
		}
		return $out;
	}
	
	function ordered_field_names(){
		$names = array();
		$orders = array();
		foreach ($this->fields as $field => $options){
			$names[] = $field;
			$orders[] = $options['order'];
		}
		array_multisort($orders, SORT_ASC, SORT_NUMERIC, $names, SORT_ASC, SORT_STRING);
		return $names;
	}
	
	function form_field($fieldname, &$value){
		$out = $this->label_for($fieldname);
		switch($this->fields[$fieldname]['type']){
			case 'select':
				$out .= $this->form_menu($fieldname, $value);
				break;
			case 'checkbox':
				$out .= $this->form_checkbox($fieldname, $value);
				break;
			default: // default is text
				$out .= $this->form_textfield($fieldname, $value);
		}
		$out .= $this->note_for($fieldname);
		$out = '<p>'.$out.'</p>';
		return $out;
	}
	
	function form_menu($fieldname, &$selected){
		$out = '<select '.$this->id_and_name_for($fieldname).'>';
		foreach ($this->fields[$fieldname]['optionlist'] as $key => $val) {
			$out .= '<option value="'.$key.'"';
			if ($key == $selected) {
				$out .= ' selected="selected"';
			}
			$out .= '>'.$val.'</option>';
		}
		$out .= '</select>';
		return $out;
	}
	
	function form_textfield($fieldname, &$value){
		$out = '<input '.$this->id_and_name_for($fieldname).' type="text" value="'.$value.'"';
		if ($this->fields[$fieldname]['size']) {
			$out .= ' size="'.$this->fields[$fieldname]['size'].'"';
		}
		$out .= '/>';
		return $out;
	}
	
	function form_checkbox($fieldname, &$value){
		$out = '<input type="hidden" name="'.$this->get_field_name($fieldname).'" value="false">';
		$out .= '<input '.$this->id_and_name_for($fieldname).' type="checkbox" '. checked($value).'/>';
		return $out;
	}
	
	function label_for($fieldname){
		$label = $this->fields[$fieldname]['label'];
		if (!$label) $label = $this->default_labelname_for($fieldname);
		$out = '<label for="' . $this->get_field_id($fieldname) . '">' . $label . ':</label>'."\n";
		return $out;
	}
	
	function default_labelname_for($fieldname){
		return ucwords(str_replace('_',' ',$fieldname));
	}
	
	function id_and_name_for($fieldname){
		return 'id="'.$this->get_field_id($fieldname).'" name="'.$this->get_field_name($fieldname).'"';
	}
	
	function note_for($fieldname){
		$out = '';
		if ($this->fields[$fieldname]['note']){
			$out = '<small>'.$this->fields[$fieldname]['note'].'</small>';
		}
		return $out;
	}
	
	function update($new_instance, $old_instance){
		$instance = $old_instance;
		foreach ($this->fields as $field => $options){
			if ($options['type'] == 'select') {
				// validate menu selection
				$instance[$field] = validate_option($new_instance[$field], $field);
			}
			$instance[$field] = $new_instance[$field];
		}
		return $instance;
	}
	
	function form( $instance ) {
		echo $this->form_all_fields($instance);
	}
}


?>