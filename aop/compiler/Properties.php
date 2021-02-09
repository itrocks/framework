<?php
namespace ITRocks\Framework\AOP\Compiler;

use ITRocks\Framework\AOP\Weaver\Handler;
use ITRocks\Framework\PHP\Reflection_Class;
use ITRocks\Framework\PHP\Reflection_Method;

/**
 * Aspect weaver properties compiler
 */
class Properties
{
	use Scanners;
	use Toolbox;

	//----------------------------------------------------------------------------------------- DEBUG
	const DEBUG = false;

	//-------------------------------------------------------------------------------- INIT_JOINPOINT
	const INIT_JOINPOINT = '2.joinpoint';

	//------------------------------------------------------------------------------ $SETTER_RESERVED
	/**
	 * @var string[]
	 */
	private static $SETTER_RESERVED = [
		'class_name', 'element_type', 'element_type_name', 'joinpoint', 'object',
		'property', 'property_name', 'result', 'stored', 'type', 'type_name', 'value'
	];

	//-------------------------------------------------------------------------------------- $actions
	/**
	 * @var string[] key is the original method name, value is the 'rename' or 'trait' action
	 */
	private $actions;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class Reflection_Class
	 */
	public function __construct(Reflection_Class $class)
	{
		$this->class = $class;
	}

	//--------------------------------------------------------------------------------------- compile
	/**
	 * @param $advices array
	 * @return string[]
	 */
	public function compile(array $advices)
	{
		$this->actions = [];
		$methods = [];
		if ($this->class->type !== T_TRAIT) {
			$methods['__construct'] = $this->compileConstruct($advices);
			if ($methods['__construct']) {
				$methods['__aop']     = $this->compileAop($advices);
				$methods['__default'] = $this->compileDefault($advices);
				$methods['__get']     = $this->compileGet($advices);
				$methods['__isset']   = $this->compileIsset($advices);
				$methods['__set']     = $this->compileSet($advices);
				$methods['__unset']   = $this->compileUnset($advices);
				$methods['__wakeup']  = $this->compileWakeup();
			}
			else {
				unset($methods['__construct']);
			}
		}
		foreach ($advices as $property_name => $property_advices) {
			if ($read = $this->compileRead($property_name, $property_advices)) {
				$methods['_' . $property_name . '_read'] = $read;
			}
			if ($write = $this->compileWrite($property_name, $property_advices)) {
				$methods['_' . $property_name . '_write'] = $write;
			}
		}
		$this->executeActions();
		return $methods;
	}

	//--------------------------------------------------------------------------------- compileAdvice
	/**
	 * @param $property_name string
	 * @param $type          string
	 * @param $advice        string[]|object[]|string
	 * @param $init          string[]
	 * @return string
	 */
	private function compileAdvice($property_name, $type, $advice, array &$init)
	{
		$class_name = $this->class->name;

		/** @var $advice_class_name string */
		/** @var $advice_method_name string */
		/** @var $advice_function_name string */
		/** @var $advice_parameters string[] */
		/** @var $advice_string string [$object_, 'methodName'] | 'functionName' */
		/** @var $advice_has_return boolean */
		/** @var $is_advice_static boolean */
		list(
			$advice_class_name, $advice_method_name, $advice_function_name,
			$advice_parameters, $advice_string, $advice_has_return, $is_advice_static
		) = $this->decodeAdvice($advice, $class_name);

		// $advice_parameters_string, $joinpoint_code
		$joinpoint_code = '';
		if ($advice_parameters) {
			$advice_parameters_string = '$' . join(', $', array_keys($advice_parameters));
			if (
				isset($advice_parameters[$property_name])
				&& !in_array($property_name, self::$SETTER_RESERVED)
			) {
				$advice_parameters_string = str_replace(
					'$' . $property_name, '$value', $advice_parameters_string
				);
			}
			if (isset($advice_parameters['property_name'])) {
				$advice_parameters_string = str_replace(
					'$property_name', Q . $property_name . Q, $advice_parameters_string
				);
			}
			if (isset($advice_parameters['result'])) {
				$advice_parameters_string = str_replace('$result', '$value', $advice_parameters_string);
			}
			if (isset($advice_parameters['object']) && !isset($parameters['object'])) {
				$advice_parameters_string = str_replace('$object', '$this', $advice_parameters_string);
			}
			if (isset($advice_parameters['stored']) || isset($advice_parameters['joinpoint'])) {
				$init['1.stored'] = '$stored =& $this->' . $property_name . ';';
			}
			if (isset($advice_parameters['joinpoint'])) {
				$pointcut_string = '[$this, ' . Q . $property_name . Q . ']';
				$init[self::INIT_JOINPOINT] = '$joinpoint = new \ITRocks\Framework\AOP\Joinpoint' . BS . ucfirst($type) . '_Property('
					. LF . TAB . TAB . '__CLASS__, ' . $pointcut_string . ', $value, $stored, ' . $advice_string
					. ');';
			}
			if (
				isset($advice_parameters['property']) || isset($advice_parameters['type'])
				|| isset($advice_parameters['element_type']) || isset($advice_parameters['type_name'])
				|| isset($advice_parameters['element_type_name']) || isset($advice_parameters['class_name'])
			) {
				$init['3.property'] = '$property = new \ITRocks\Framework\Reflection\Reflection_Property($this, '
					. Q . $property_name . Q . ');';
			}
			if (
				isset($advice_parameters['type']) || isset($advice_parameters['type_name'])
				|| isset($advice_parameters['element_type_name']) || isset($advice_parameters['class_name'])
			) {
				$init['4.type'] = '$type = $property->getType();';
			}
			if (isset($advice_parameters['element_type'])) {
				$init['5.element_type'] = '$element_type = $property->getElementType();';
			}
			if (isset($advice_parameters['type_name'])) {
				$init['6.type_name'] = '$type_name = $type->asString();';
			}
			if (isset($advice_parameters['element_type_name'])) {
				$init['7.element_type_name'] = '$element_type_name = $type->getElementTypeAsString();';
			}
			if (isset($advice_parameters['class_name'])) {
				$init['7.class_name'] = '$class_name = $type->getElementTypeAsString();';
			}
		}
		else {
			$advice_parameters_string = '';
		}

		return $this->generateAdviceCode(
			$advice, $advice_class_name, $advice_method_name, $advice_function_name,
			$advice_parameters_string, $advice_has_return, $is_advice_static, $joinpoint_code,
			LF . TAB . TAB, '$value'
		);
	}

	//------------------------------------------------------------------------------------ compileAop
	/**
	 * @param $advices array
	 * @return string
	 */
	private function compileAop(array $advices)
	{
		$parent_code = '';
		$begin_code = '
	/** AOP initialization for an object : called by __construct */
	protected function __aop($init = true)
	{
		if ($init) $this->_ = [];';
		$code = '';
		foreach ($advices as $property_name => $property_advices) {
			if (
				!isset($property_advices['override'])
				// no AOP if the only 'advice' is 'default' (not a real advice, in fact)
				&& ((count($property_advices) > 1) || !isset($property_advices['default']))
			) {
				$code .= '
		';
				if (!isset($property_advices['replaced'])) {
					$code .= '
		$this->' . $property_name . '_ = isset($this->' . $property_name . ')'
						. ' ? $this->' . $property_name . ' : null;';
				}
				$code .= '
		unset($this->' . $property_name . ');
		$this->_[' . Q . $property_name . Q . '] = true;';
			}
		}
		// TODO  not all cases are threat by this first easy code without the patch next : found why
		if ($parent_class = $this->class->getParentClass()) {
			if (isset($parent_class->getMethods([T_EXTENDS])['__aop'])) {
				$parent_code = '

		if (method_exists(get_parent_class($this), \'__aop\')) parent::__aop(false);';
			}
			// kept this for patch of Built class that inherit a class with __aop,
			// and classes that inherit a Built class (or they will not call parent::__aop)
			else {
				// TODO this check only getters, links and setters. This should check AOP links too.
				foreach ($parent_class->getProperties([T_EXTENDS, T_USE]) as $property) {
					$expr = '%'
						. '\n\s+\*\s+'               // each line beginning by '* '
						. '@(getter|link|setter)'    // 1 : AOP annotation
						. '(?:\s+(?:([\\\\\w]+)::)?' // 2 : class name
						. '(\w+)?)?'                 // 3 : method or function name
						. '%';
					preg_match($expr, $property->getDocComment(), $match);
					if ($match) {
						$parent_code = '

		if (method_exists(get_parent_class($this), \'__aop\')) parent::__aop(false);';
					}
				}
			}
		}
		return $begin_code . $code . $parent_code . '
	}
';
	}

	//------------------------------------------------------------------------------ compileConstruct
	/**
	 * Compile __construct if there is at least one property declared in this class / traits
	 *
	 * @param $advices array
	 * @return string
	 */
	private function compileConstruct(array $advices)
	{
		// only if at least one property is declared here
		foreach ($advices as $property_advices) {
			if (
				isset($property_advices['default'])
				|| isset($property_advices['implements'])
				|| isset($property_advices['replaced'])
			) {
				$over = $this->overrideMethod('__construct', false);
				$code = $over['call'] ?: (
					$this->class->getParentClass()
					? "if (method_exists(get_parent_class(__CLASS__), '__construct')) {
			parent::__construct();
		}"
					: ''
				);
				return
					$over['prototype'] . '
		if (!isset($this->id)) $this->__default();
		if (!isset($this->_)) $this->__aop();'
					. ($code ? (LF . TAB . TAB . $code) : '')
					. '
	}
';
			}
		}
		return '';
	}

	//-------------------------------------------------------------------------------- compileDefault
	/**
	 * @param $advices array
	 * @return string
	 */
	private function compileDefault(array $advices)
	{
		$over = $this->overrideMethod('__default', false);
		$code = '';
		foreach ($advices as $property_name => $property_advices) {
			if (isset($property_advices['default'])) {
				list($object, $method) = $property_advices['default'];
				$operator              = ($object === '$this') ? '->' : '::';
				$code .= "if (!isset(\$this->$property_name)) {
			\$this->$property_name = $object$operator$method(
				new \\ITRocks\\Framework\\Reflection\\Reflection_Property(__CLASS__, '$property_name')
			);
		}" . LF . TAB . TAB;
			}
		}
		if (!isset($operator) && beginsWith($over['call'], 'parent::')) {
			return '';
		}
		$code .= $over['call'] ?: (
			$this->class->getParentClass()
			? "if (method_exists(get_parent_class(__CLASS__), '__default')) {
			parent::__default();
		}"
				: ''
		);
		return $over['prototype'] . LF . TAB . TAB . $code . LF . TAB . '}' . LF;
	}

	//------------------------------------------------------------------------------------ compileGet
	/**
	 * @param $advices array
	 * @return string
	 */
	private function compileGet(array $advices)
	{
		$over = $this->overrideMethod('__get', true, $advices);
		$code = $over['prototype'] . '
		if (!isset($this->_) || !isset($this->_[$property_name])) {
			' . $over['call'] . '
		}';
		if ($over['cases']) {
			$switch = true;
			$code .= '
		switch ($property_name) {';
		}
		foreach ($advices as $property_name => $property_advices) {
			if (isset($property_advices['replaced'])) {
				if (!isset($switch)) {
					$switch = true;
					$code .= '
		switch ($property_name) {';
				}
				if ($property_advices['replaced'] == 'this') {
					$code .= '
			case ' . Q . $property_name . Q . ': $value =& $this; return $value;';
				}
				else {
					$code .= '
			case ' . Q . $property_name . Q . ': $value =& $this->' . $property_advices['replaced'] . '; return $value;';
				}
				if (isset($over['cases'][$property_name])) {
					unset($over['cases'][$property_name]);
					if (count($over['cases']) == 1) {
						$over['cases'] = [];
					}
				}
			}
			elseif (isset($property_advices['implements'][Handler::READ])) {
				if (!isset($switch)) {
					$switch = true;
					$code .= '
		switch ($property_name) {';
				}
				$code .= '
			case ' . Q . $property_name . Q . ': return $this->_' . $property_name . '_read();';
			}
		}
		if (isset($switch)) {
			$code .= join('', $over['cases']) . '
		}';
		}
		return $code . '
		$property_name .= \'_\';
		return $this->$property_name;
	}
';
	}

	//---------------------------------------------------------------------------------- compileIsset
	/**
	 * @param $advices array
	 * @return string
	 */
	private function compileIsset(array $advices)
	{
		$over = $this->overrideMethod('__isset');
		$code = $over['prototype'] . '
		if (!isset($this->_) || !isset($this->_[$property_name])) {
			' . $over['call'] . '
		}';
		foreach ($advices as $property_name => $property_advices) {
			if (isset($property_advices['replaced'])) {
				if (!isset($switch)) {
					$switch = true;
					$code .= '
		switch ($property_name) {';
				}
				if ($property_advices['replaced'] == 'this') {
					$code .= '
			case ' . Q . $property_name . Q . ': return true;';
				}
				else {
					$code .= '
			case ' . Q . $property_name . Q . ': return isset($this->' . $property_advices['replaced'] . ');';
				}
			}
		}
		if (!isset($switch) && beginsWith($over['call'], 'return parent::')) {
			return '';
		}
		if (isset($switch)) {
			$code .= '
		}';
		}
		return $code . '
		$property_name .= \'_\';
		return isset($this->$property_name);
	}
';
	}

	//----------------------------------------------------------------------------------- compileRead
	/**
	 * @param $property_name string
	 * @param $advices       array
	 * @return string
	 */
	private function compileRead($property_name, array $advices)
	{
		$code = '';
		$init = [];
		$last = '';
		foreach ($advices as $key => $aspect) if (is_numeric($key)) {
			if ($aspect[0] === Handler::WRITE) {
				$last = '$last = ';
				break;
			}
		}
		foreach ($advices as $key => $aspect) if (is_numeric($key)) {
			list($type, $advice) = $aspect;
			if ($type === Handler::READ) {
				if (!isset($prototype)) {
					$prototype = '
	/** AOP */
	private function & _' . $property_name . '_read()
	{
		unset($this->_[' . Q . $property_name . Q . ']);
		' . $last . '$value = $this->' . $property_name . ' =& $this->' . $property_name . '_;
';
				}
				$code .= $this->compileAdvice($property_name, Handler::READ, $advice, $init);
				if ($last) {
					$code .= '
		if ($this->' . $property_name . ' !== $last) {
			$this->_' . $property_name . '_write($this->' . $property_name . ');
			$last = $this->' . $property_name . ';
		}';
				}
			}
		}
		if (isset($prototype)) {
			if (isset($init[self::INIT_JOINPOINT])) {
				$reset_aop = '

		if ($joinpoint->disable) {
			unset($this->' . $property_name . '_);
		}
		else {
			unset($this->' . $property_name . ');
			$this->_[' . Q . $property_name . Q . '] = true;
		}
				';
			}
			else {
				$reset_aop = '

		unset($this->' . $property_name . ');
		$this->_[' . Q . $property_name . Q . '] = true;
				';
			}
			// todo missing call of setters if value has been changed
			return $prototype . $this->initCode($init) . $code . $reset_aop . '
		return $value;
	}
';
		}
		return '';
	}

	//------------------------------------------------------------------------------------ compileSet
	/**
	 * @param $advices array
	 * @return string
	 */
	private function compileSet(array $advices)
	{
		$over = $this->overrideMethod('__set', true, $advices);
		$code = $over['prototype'] . '
		if (!isset($this->_) || !isset($this->_[$property_name])) {
			' . $over['call'] . '
		}';
		if ($over['cases']) {
			$switch = true;
			$code .= '
		switch ($property_name) {';
		}
		foreach ($advices as $property_name => $property_advices) {
			if (isset($property_advices['replaced'])) {
				if (!isset($switch)) {
					$switch = true;
					$code .= '
		switch ($property_name) {';
				}
				if ($property_advices['replaced'] == 'this') {
					$code .= '
			case ' . Q . $property_name . Q . ': foreach (get_object_vars($this) as $k => $v) if ($k != \'' . $property_name . '\' && !isset($value->$k)) unset($this->$v); foreach (get_object_vars($value) as $k => $v) $this->$k = $v; return;';
				}
				else {
					$code .= '
			case ' . Q . $property_name . Q . ': $this->' . $property_advices['replaced'] . ' = $value; return;';
				}
				if (isset($over['cases'][$property_name])) {
					unset($over['cases'][$property_name]);
					if (count($over['cases']) == 1) {
						$over['cases'] = [];
					}
				}
			}
			elseif (isset($property_advices['implements'][Handler::WRITE])) {
				if (!isset($switch)) {
					$switch = true;
					$code .= '
		switch ($property_name) {';
				}
				$code .= '
			case ' . Q . $property_name . Q . ': $this->_' . $property_name . '_write($value); return;';
			}
		}
		if (isset($switch)) {
			$code .= join('', $over['cases']) . '
		}';
		}
		if (beginsWith($over['call'], 'parent::')) {
			if (!isset($switch)) {
				return '';
			}
			return $code . '
		return parent::__set($property_name, $value);
	}
';
		}
		return $code . '
		$id_property_name = \'id_\' . $property_name;
		if (is_object($value) && isset($value->id)) $this->$id_property_name = $value->id;
		elseif (isset($this->$id_property_name))    unset($this->$id_property_name);
		$property_name .= \'_\';
		$this->$property_name = $value;
	}
';
	}

	//---------------------------------------------------------------------------------- compileUnset
	/**
	 * @param $advices array
	 * @return string
	 */
	private function compileUnset(array $advices)
	{
		$over = $this->overrideMethod('__unset');
		$code = $over['prototype'] . '
		if (!isset($this->_) || !isset($this->_[$property_name])) {
			' . $over['call'] . '
		}';
		foreach ($advices as $property_name => $property_advices) {
			if (isset($property_advices['replaced'])) {
				if (!isset($switch)) {
					$switch = true;
					$code .= '
		switch ($property_name) {';
				}
				if ($property_advices['replaced'] == 'this') {
					$code .= '
			case ' . Q . $property_name . Q . ': trigger_error("You can\'t unset the link property", E_USER_ERROR); return;';
				}
				else {
					$code .= '
			case ' . Q . $property_name . Q . ': unset($this->' . $property_advices['replaced'] . '); return;';
				}
			}
		}
		if (!isset($switch) && beginsWith($over['call'], 'parent::')) {
			return '';
		}
		if (isset($switch)) {
			$code .= '
		}';
		}
		return $code . '
		$property_name .= \'_\';
		$this->$property_name = null;
	}
';
	}

	//--------------------------------------------------------------------------------- compileWakeup
	/**
	 * When we unserialize a method, default properties are created even if they were not in the
	 * serialized class (with a null value) : unset the properties overridden using AOP
	 *
	 * @return string
	 */
	private function compileWakeup()
	{
		$over = $this->overrideMethod('__wakeup', false);
		if (beginsWith($over['call'], 'parent::')) {
			return '';
		}
		return $over['prototype'] . '
		if ($this->_) foreach (array_keys($this->_) as $aop_property) {
			unset($this->$aop_property);
		}
	}
';
	}

	//---------------------------------------------------------------------------------- compileWrite
	/**
	 * @param $property_name string
	 * @param $advices       array
	 * @return string
	 */
	private function compileWrite($property_name, array $advices)
	{
		$code = '';
		$init = [];
		foreach ($advices as $key => $aspect) if (is_numeric($key)) {
			list($type, $advice) = $aspect;
			if ($type === Handler::WRITE) {
				if (!isset($prototype)) {
					$prototype = '
	/** AOP ' . $property_name . ' writer : implementation for @setter called by __set */
	private function _' . $property_name . '_write($value)
	{
		if (isset($this->_[' . Q . $property_name . Q . '])) {
			unset($this->_[' . Q . $property_name . Q . ']);
			$this->' . $property_name . ' = $this->' . $property_name . '_;
			$writer = true;
		}
';
				}
				$advice_code = $this->compileAdvice($property_name, Handler::WRITE, $advice, $init);
				if (strpos($advice_code, '$value = ') !== false) {
					$advice_code .= LF . TAB . TAB . '$this->' . $property_name . ' = $value;';
				}
				$code .= $advice_code;
			}
		}
		if (isset($prototype)) {
			return $prototype . $this->initCode($init) . $code . '

		if (isset($writer)) {
			$this->' . $property_name . '_ = $this->' . $property_name . ';
			unset($this->' . $property_name . ');
			$this->_[' . Q . $property_name . Q . '] = true;
		}
	}
';
		}
		return '';
	}

	//-------------------------------------------------------------------------------- executeActions
	/**
	 * Execute $this->actions on methods
	 * - rename will change the name of the method
	 * - trait will ?
	 */
	private function executeActions()
	{
		foreach ($this->actions as $method_name => $action) {
			if ($action == 'rename') {
				$regexp = Reflection_Method::regex($method_name);
				$this->class->source->setSource(preg_replace(
					$regexp,
					'$1$2/* $4 */ private $5function $6$7_0$8$9',
					$this->class->source->getSource())
				);
			}
			elseif ($action == 'trait') {
				// TODO don't know what has to be done for this case
				trigger_error(
					'Don\'t know how to ' . $action . SP . $this->class->name . '::' . $method_name,
					E_USER_NOTICE
				);
			}
			else {
				trigger_error(
					'Don\'t know how to ' . $action . SP . $this->class->name . '::' . $method_name,
					E_USER_ERROR
				);
			}
		}
	}

	//-------------------------------------------------------------------------------------- initCode
	/**
	 * @param $init string[]
	 * @return string
	 */
	private function initCode(array $init)
	{
		if (isset($init['7.element_type_name']) && isset($init['7.class_name'])) {
			$init['7.class_name_element_type_name'] = '$class_name = ' . $init['7.element_type_name'];
			unset($init['7.class_name']);
			unset($init['7.element_type_name']);
		}
		ksort($init);
		return $init ? (LF . TAB . TAB . join(LF . TAB . TAB, $init) . LF) : '';
	}

	//-------------------------------------------------------------------------------- overrideMethod
	/**
	 * Override a public method
	 *
	 * @param $method_name  string
	 * @param $needs_return boolean if false, call will not need return statement
	 * @param $advices      array
	 * @return array action (rename, trait), call, Reflection_Method method, prototype
	 */
	private function overrideMethod($method_name, $needs_return = true, array $advices = [])
	{
		$over       = ['cases' => []];
		$parameters = '';
		// the method exists into the class
		$methods = $this->class->getMethods();
		if (isset($methods[$method_name])) {
			$method = $methods[$method_name];
			$over['action'] = 'rename';
			$over['call']   = '$this->';
		}
		else {
			// the method exists into a trait of the class
			$methods = $this->class->getMethods([T_USE]);
			if (isset($methods[$method_name])) {
				$method = $methods[$method_name];
				$over['action'] = 'trait';
				$over['call']   = '$this->';
			}
			else {
				// the method exists into a parent class / trait and is not abstract
				$methods = $this->class->getMethods([T_EXTENDS, T_IMPLEMENTS]);
				if (isset($methods[$method_name]) && !$methods[$method_name]->isAbstract()) {
					$method = $methods[$method_name];
					$over['action'] = false;
					$over['call']   = 'parent::';
				}
				else {
					// the method does not exist and the parent has no AOP properties
					$over['action'] = false;
					$over['call']   = false;
				}
			}
		}
		// add parent AOP properties cases
		$over['cases'] = $this->parentCases($method_name, $parameters, $advices);
		// the method exists : prepare call and prototype
		if (isset($method)) {
			$over['method']    = $method;
			$over['prototype'] = rtrim($method->getPrototypeString());
			if (in_array($method_name, ['__get', '__isset', '__set', '__unset'])) {
				$parameters = $method->getParametersNames();
				if (reset($parameters) !== 'property_name') {
					$over['prototype'] .= '
		$property_name = $' . reset($parameters) . ';';
				}
				if ((count($parameters) == 2) && (end($parameters) !== 'value')) {
					$over['prototype'] .= '
		$value = $' . end($parameters) . ';';
				}
			}
			if ($over['call']) {
				$suffix = ($over['call'] === 'parent::') ? '' : '_0';
				$method_returns = $method->returns() || in_array($method->name, ['__get', '__isset']);
				$over['call'] = ($method_returns ? 'return ' : '')
					. $over['call'] . $method_name . $suffix . '(' . $method->getParametersCall() . ');'
					. (($method_returns || !$needs_return) ? '' : ' return;');
			}
		}
		// the method does not exist : call default code and create default prototype
		else {
			$over['action'] = false;
			$over['method'] = false;
			if (!$over['call']) {
				$parameters = '$property_name';
				switch ($method_name) {
					case '__get':
						$over['call'] = 'return $this->$property_name;';
						break;
					case '__isset':
						$over['call'] = 'return isset($this->$property_name);';
						break;
					case '__set':
						$over['call'] = '$this->$property_name = $value; return;';
						$parameters .= ', $value';
						break;
					case '__unset':
						$over['call'] = 'unset($this->$property_name); return;';
						break;
					default:
						$parameters = '';
				}
			}
			$over['prototype'] = '
	/** AOP */
	public function ' . $method_name . '(' . $parameters . ')
	{';
		}
		if ($over['action']) {
			$this->actions[$method_name] = $over['action'];
		}
		$over['prototype'] = preg_replace('%(function\s+)(__get\s*\()%', '$1& $2', $over['prototype']);
		return $over;
	}

	//----------------------------------------------------------------------------------- parentCases
	/**
	 * @param $method_name string
	 * @param $parameters  string
	 * @param $advices     array
	 * @return string[]
	 * @todo this check only getters, links and setters. This should check AOP links too.
	 * (the parent class has not this method but it has AOP properties)
	 */
	private function parentCases($method_name, &$parameters, array $advices)
	{
		$cases = [];
		if (
			in_array($method_name, ['__get', '__set'])
			&& ($this->class->type === T_CLASS)
			&& ($parent = $this->class->getParentClass())
		) {
			$annotation = ($method_name == '__get') ? '(getter|link)' : 'setter';
			$type       = ($method_name == '__get') ? Handler::READ : Handler::WRITE;
			$overrides  = [];
			foreach ($this->scanForOverrides(
				$parent->getDocComment([T_EXTENDS, T_USE]), [substr($method_name, 2) . 'ter']
			) as $override) {
				$overrides[$override['property_name']] = true;
			}
			foreach ($parent->getProperties([T_EXTENDS, T_USE], $parent) as $property) {
				if (!isset($advices[$property->name]['implements'][$type])) {
					$expr = '%'
						. '\n\s+\*\s+'               // each line beginnig by '* '
						. AT . $annotation           // 1 : AOP annotation
						. '(?:\s+(?:([\\\\\w]+)::)?' // 2 : class name
						. '(\w+)?)?'                 // 3 : method or function name
						. '%';
					preg_match($expr, $property->getDocComment(), $match);
					if ($match || isset($overrides[$property->name])) {
						$cases[$property->name] = LF . TAB . TAB . TAB . 'case ' . Q . $property->name . Q . ':';
					}
				}
			}
			if ($cases) {
				$parameters = '$property_name';
				switch ($method_name) {
					case '__get':
						$cases[] = ' return parent::__get($property_name);';
						break;
					case '__isset':
						$cases[] = ' return parent::__isset($property_name);';
						break;
					case '__set':
						$cases[] = ' parent::__set($property_name, $value); return;';
						$parameters .= ', $value';
						break;
					case '__unset':
						$cases[] = ' parent::__unset($property_name); return;';
						break;
					default:
						$parameters = '';
				}
			}
		}
		return $cases;
	}

}
