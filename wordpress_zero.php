<?php
foreach(glob(dirname(__FILE__).'/classes/*.php') as $file)
{
	require_once($file);
}
?>