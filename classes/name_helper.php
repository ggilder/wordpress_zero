<?php
class NameHelper{
	public static function naturalizeFieldName($str)
	{
		return ucwords(trim(str_replace('_',' ',$str)));
	}
	public static function prepLabelName($str)
	{
		// add colon to end of label name unless it ends with colon or question mark
		if (!in_array( substr($str, -1), array(':','?') )) $str .= ':';
		return $str;
	}
}
?>