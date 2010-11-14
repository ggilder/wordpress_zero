<?php
/**
* HTML Helper. Because HTML is boring.
*/
class HTMLHelper
{
	public static function formfield($options=array())
	{
		switch($options['type']){
			case 'toggle':
				return self::toggle($options);
			case 'textarea':
				return self::textarea($options);
			case 'text':
			case 'input':
			default:
				return self::input($options);
		}
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
		return self::tag('label', $options, $content);
	}
	public static function input($options=array())
	{
		return self::tag('input', $options);
	}
	public static function textarea($options=array())
	{
		$content = false;
		if (isset($options['value'])) $content = $options['value'];
		unset($options['value']);
		return self::tag('textarea', $options, $content);
	}
	
	
	
	public static function tag($tag, $attributes=array(), $content=false)
	{
		$out = '<'.$tag;
		foreach($attributes as $key => $val){
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