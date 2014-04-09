<?php

/**
 * This patch get rid of trailing "active" ghost functions from previous script execution
 */
if (function_exists('_runkit_function_rename')) {
	runkit_function_remove('_runkit_function_rename');
}
runkit_function_rename('runkit_function_rename', '_runkit_function_rename');
runkit_function_add(
	'runkit_function_rename', '$function_name, $new_name',
	'
		if (($new_name[0] == "_") && function_exists($new_name)) {
			runkit_function_remove($new_name);
		}
		return _runkit_function_rename($function_name, $new_name);
	'
);

/**
 * This is a patch for runkit, as you can't add a method that override a parent class method
 *
 * The process is not done again when __runkit_* functions are already created : when executing PHP
 * as an apache module, runkit modified functions are not reset to default at each script end !
 */

runkit_function_rename('runkit_method_add', '_runkit_method_add');
runkit_function_add(
	'runkit_method_add', '$class_name, $method_name, $args, $code, $flags = RUNKIT_ACC_PUBLIC',
	'
		$parent_classes = [];
		$parent_count = 0;
		while (method_exists($class_name, $method_name)) {
			$parent_class = (new ReflectionMethod($class_name, $method_name))->class;
			if ($parent_class == $class_name) break;
			runkit_method_rename($parent_class, $method_name, "_" . $method_name . "_back" . $parent_count);
			$parent_classes[$parent_count++] = $parent_class;
		}
		$result = _runkit_method_add($class_name, $method_name, $args, $code, $flags);
		foreach ($parent_classes as $parent_count => $parent_class) {
			runkit_method_rename($parent_class, "_" . $method_name . "_back" . $parent_count, $method_name);
		}
		return $result;
	'
);

runkit_function_rename('runkit_method_rename', '_runkit_method_rename');
runkit_function_add(
	'runkit_method_rename', '$class_name, $method_name, $new_name',
	'
		$parent_classes = [];
		$parent_count = 0;
		while (method_exists($class_name, $new_name)) {
			$parent_class = (new ReflectionMethod($class_name, $new_name))->class;
			if ($parent_class == $class_name) break;
			runkit_method_rename($parent_class, $new_name, "_" . $new_name . "_back" . $parent_count);
			$parent_classes[$parent_count++] = $parent_class;
		}
		$result = _runkit_method_rename($class_name, $method_name, $new_name);
		foreach ($parent_classes as $parent_count => $parent_class) {
			runkit_method_rename($parent_class, "_" . $new_name . "_back" . $parent_count, $new_name);
		}
		return $result;
	'
);
