<?php
/**
* HTML Helper. Because HTML is boring.
*/
class HTMLHelper
{
	public static function formfield($options=array())
	{
		switch($options['type']){
			case 'select':
			case 'menu':
				unset($options['type']);
				return self::select($options);
			case 'toggle':
				return self::toggle($options);
			case 'textarea':
				unset($options['type']);
				return self::textarea($options);
			case 'text':
			default:
				return self::input($options);
		}
	}
	
	public static function select($params=array())
	{
		$list = '';
		foreach($params['options'] as $value => $label)
		{
			$item_params = array('value'=>$value);
			if ($value == $params['value']) $item_params['selected'] = 'selected';
			$list .= self::tag('option', $item_params, $label);
		}
		unset($params['options'], $params['value']);
		return self::tag('select', $params, $list);
	}
	/**
	 * Special form field generator for a toggle, that is, a single checkbox field with a default false value in a hidden field
	 *
	 * @param array $options
	 * @return string tag HTML
	 * @author Gabriel Gilder
	 */
	public static function toggle($options=array())
	{
		$out = self::input(array(
			'type'=>'hidden',
			'name'=>$options['name'],
			'value'=>'0'
		));
		$toggle = array(
			'type'=>'checkbox',
			'name'=>$options['name'],
			'value'=>'1',
		);
		if ($options['value'] && $options['value'] != '0'){
			$toggle['checked'] = 'checked';
		}
		$out .= self::input($toggle);
		return $out;
	}
	
	
	public static function label($options=array(), $content)
	{
		return self::tag('label', $options, htmlspecialchars($content));
	}
	public static function input($options=array())
	{
		if (!$options['type']) $options['type'] = 'text';
		return self::tag('input', $options);
	}
	public static function textarea($options=array())
	{
		$content = false;
		if (isset($options['value'])) $content = $options['value'];
		unset($options['value']);
		return self::tag('textarea', $options, htmlspecialchars($content));
	}
	
	
	public static function br($options=array())
	{
		return self::tag('br', $options);
	}
	public static function p($options=array(), $content)
	{
		return self::tag('p', $options, $content);
	}
	

	public static function tag($tag, $attributes=array(), $content=false)
	{
		$out = '<'.$tag;
		foreach($attributes as $key => $val){
			// key name must be valid for HTML attributes
			if (!preg_match('/^[a-zA-Z_:][-a-zA-Z0-9_:\.]*$/', $key)) continue;
			if ($val === true){
				$out .= ' '.$key;
			} elseif ($val === false || $val === null){
				continue;
			} else {
				$out .= ' '.$key.'="'.htmlspecialchars($val).'"';
			}
		}
		$out .= '>';
		if ($content !== false){
			$out .= $content.'</'.$tag.'>';
		}
		return $out;
	}
}

?>