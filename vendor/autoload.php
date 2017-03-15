<?php
spl_autoload_register(function($className) {
	$file = dirname(__FILE__) .'/classes/'. $className .'.class.php';

	if (
		!class_exists($className)
		&& is_readable($file)
	) require_once $file;
});