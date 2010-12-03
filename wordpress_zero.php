<?php
// check if WidgetZero class is defined as a proxy for determining whether all the classes are loaded already
if(!class_exists('WidgetZero')){
	foreach(glob(dirname(__FILE__).'/classes/*.php') as $file)
	{
		require_once($file);
	}
}
?>