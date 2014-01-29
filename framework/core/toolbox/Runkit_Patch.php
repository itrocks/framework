<?php
/**
 * This is a patch for runkit, as you can't add a method that override a parent class method
 */

runkit_function_rename('runkit_method_add', '__runkit_method_add');
runkit_function_add(
	'runkit_method_add', '$class_name, $method_name, $args, $code, $flags = RUNKIT_ACC_PUBLIC',
	'
		$parent_classes = array();
		$parent_count = 0;
		while (method_exists($class_name, $method_name)) {
			$parent_class = (new ReflectionMethod($class_name, $method_name))->class;
			if ($parent_class == $class_name) break;
			runkit_method_rename($parent_class, $method_name, "_" . $method_name . "_back" . $parent_count);
			$parent_classes[$parent_count++] = $parent_class;
		}
		$result = __runkit_method_add($class_name, $method_name, $args, $code, $flags);
		foreach ($parent_classes as $parent_count => $parent_class) {
			runkit_method_rename($parent_class, "_" . $method_name . "_back" . $parent_count, $method_name);
		}
		return $result;
	'
);

runkit_function_rename('runkit_method_rename', '__runkit_method_rename');
runkit_function_add(
	'runkit_method_rename', '$class_name, $method_name, $new_name',
	'
		$parent_classes = array();
		$parent_count = 0;
		while (method_exists($class_name, $new_name)) {
			$parent_class = (new ReflectionMethod($class_name, $new_name))->class;
			if ($parent_class == $class_name) break;
			runkit_method_rename($parent_class, $new_name, "_" . $new_name . "_back" . $parent_count);
			$parent_classes[$parent_count++] = $parent_class;
		}
		$result = __runkit_method_rename($class_name, $method_name, $new_name);
		foreach ($parent_classes as $parent_count => $parent_class) {
			runkit_method_rename($parent_class, "_" . $new_name . "_back" . $parent_count, $new_name);
		}
		return $result;
	'
);
