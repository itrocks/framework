<?php
namespace SAF\AOP;

use SAF\Framework\Reflection_Parameter;

/**
 * Aspect weaver properties compiler
 */
class Properties_Compiler
{
	use Compiler_Toolbox;

	const DEBUG = false;

	//-------------------------------------------------------------------------------------- $actions
	/**
	 * @var string[] key is the original method name, value is the 'rename' or 'trait' action
	 */
	private $actions;

	//---------------------------------------------------------------------------------------- $class
	/**
	 * @var Php_Class
	 */
	private $class;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class Php_Class
	 */
	public function __construct($class)
	{
		$this->class = $class;
	}

	//--------------------------------------------------------------------------------------- compile
	/**
	 * @param $advices array
	 * @return string[]
	 */
	public function compile($advices)
	{
		$this->actions = array();
		$methods = array();
		if ($this->class->type !== 'trait') {
			$methods['__construct'] = $this->compileConstruct($advices);
			if ($methods['__construct']) {
				$methods['__aop']   = $this->compileAop($advices);
				$methods['__get']   = $this->compileGet($advices);
				$methods['__isset'] = $this->compileIsset();
				$methods['__set']   = $this->compileSet($advices);
				$methods['__unset'] = $this->compileUnset();
			}
		}
		foreach ($advices as $property_name => $property_advices) {
			$methods[$property_name . '_read'] = $this->compileRead($property_name, $property_advices);
			$methods[$property_name . '_write'] = $this->compileWrite($property_name, $property_advices);
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
	private function compileAdvice($property_name, $type, $advice, &$init)
	{
		$class_name = $this->class->name;

		/** @var $advice_class_name string */
		/** @var $advice_method_name string */
		/** @var $advice_function_name string */
		/** @var $advice_parameters Reflection_Parameter[] */
		/** @var $advice_string string "array($object_, 'methodName')" | "'functionName'" */
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
			if (isset($advice_parameters[$property_name])) {
				$advice_parameters_string = str_replace(
					'$' . $property_name, '$value', $advice_parameters_string
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
				$pointcut_string = 'array($this, \'' . $property_name . '\')';
				$init['2.joinpoint'] = '$joinpoint = new \SAF\AOP\\' . ucfirst($type) . '_Property_Joinpoint('
					. "\n\t\t" . '__CLASS__, ' . $pointcut_string . ', $value, $stored, ' . $advice_string
					. ');';
			}
			if (
				isset($advice_parameters['property']) || isset($advice_parameters['type'])
				|| isset($advice_parameters['element_type']) || isset($advice_parameters['type_name'])
				|| isset($advice_parameters['element_type_name']) || isset($advice_parameters['class_name'])
			) {
				$init['3.property'] = '$property = new \SAF\Framework\Reflection_Property(__CLASS__, \''
					. $property_name . '\');';
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
			"\n\t\t", '$value'
		);
	}

	//------------------------------------------------------------------------------------ compileAop
	/**
	 * @param $advices array
	 * @return string
	 */
	private function compileAop($advices)
	{
		$parent_code = '';
		$begin_code = '
	/** AOP */
	protected function __aop($init = true)
	{
		if ($init) $this->_ = array();
';
		$code = '';
		foreach ($advices as $property_name => $property_advices) {
			$code .= '
		$this->' . $property_name . '_ = $this->' . $property_name . ';
		unset($this->' . $property_name . ');
		$this->_[\'' . $property_name . '\'] = true;
';
		}
		// todo this check only getters, links and setters. This should check AOP links too.
		if (($parent = $this->class->getParent()) && ($parent->type == 'class')) {
			foreach ($parent->getProperties(array('traits', 'inherited')) as $property) {
				$expr = '%'
					. '\n\s+\*\s+'               // each line beginning by '* '
					. '@(getter|link|setter)'    // 1 : AOP annotation
					. '(?:\s+(?:([\\\\\w]+)::)?' // 2 : class name
					. '(\w+)?)?'                 // 3 : method or function name
					. '%';
				preg_match($expr, $property->documentation, $match);
				if ($match) {
					$parent_code = '
		parent::__aop(false);';
					break;
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
	private function compileConstruct($advices)
	{
		// only if at least one property is declared here
		foreach ($advices as $property_advices) {
			if (isset($property_advices['implements'])) {
				$over = $this->overrideMethod('__construct');
				return
	$over['prototype'] . '
		if (!isset($this->_)) $this->__aop();
		' . $over['call'] . '
	}
';
			}
		}
		return '';
	}

	//------------------------------------------------------------------------------------ compileGet
	/**
	 * @param $advices array
	 * @return string
	 */
	private function compileGet($advices)
	{
		$over = $this->overrideMethod('__get');
		$code =
	$over['prototype'] . '
		if (!isset($this->_) || !isset($this->_[$property_name])) {
			' . $over['call'] . '
		}';
		if ($over['cases']) {
			$switch = true;
			$code .= '
		switch ($property_name) {';
		}
		foreach ($advices as $property_name => $property_advices) {
			if (isset($property_advices['implements']['read'])) {
				if (!isset($switch)) {
					$switch = true;
					$code .= '
		switch ($property_name) {';
				}
				$code .= '
			case \'' . $property_name . '\': return $this->' . $property_name . '_read();';
			}
		}
		if (isset($switch)) {
			$code .= $over['cases'] . '
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
	 * @return string
	 */
	private function compileIsset()
	{
		$over = $this->overrideMethod('__isset');
		return
	$over['prototype'] . '
		if (!isset($this->_) || !isset($this->_[$property_name])) {
			' . $over['call'] . '
		}
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
	private function compileRead($property_name, $advices)
	{
		unset($advices['implements']);
		$code = '';
		$init = array();
		foreach ($advices as $aspect) {
			list($type, $advice) = $aspect;
			if ($type == 'read') {
				if (!isset($prototype)) {
					$prototype = '
	/** AOP */
	private function ' . $property_name . '_read()
	{
		unset($this->_[\'' . $property_name . '\']);
		$value = $this->' . $property_name . ' = $this->' . $property_name . '_;
';
				}
				$code .= $this->compileAdvice($property_name, 'read', $advice, $init);
			}
		}
		if (isset($prototype)) {
			// todo missing call of setters if value has been changed
			return $prototype . $this->initCode($init) . $code . '

		$this->' . $property_name . '_ = $this->' . $property_name . ';
		unset($this->' . $property_name . ');
		$this->_[\'' . $property_name . '\'] = true;
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
	private function compileSet($advices)
	{
		$over = $this->overrideMethod('__set');
		$code =
	$over['prototype'] . '
		if (!isset($this->_) || !isset($this->_[$property_name])) {
			' . $over['call'] . '
		}';
		foreach ($advices as $property_name => $property_advices) {
			if (isset($property_advices['implements']['write'])) {
				if (!isset($switch)) {
					$switch = true;
					$code .= '
		switch ($property_name) {';
				}
				$code .= '
			case \'' . $property_name . '\': $this->' . $property_name . '_write($value); return;';
			}
		}
		if (isset($switch)) {
			$code .= '
		}';
		}
		return $code . '
		$property_name .= \'_\';
		$this->$property_name = $value;
	}
';
	}

	//---------------------------------------------------------------------------------- compileUnset
	/**
	 * @return string
	 */
	private function compileUnset()
	{
		$over = $this->overrideMethod('__unset');
		return
	$over['prototype'] . '
		if (!isset($this->_) || !isset($this->_[$property_name])) {
			' . $over['call'] . '
		}
		$property_name .= \'_\';
		unset($this->$property_name);
	}
';
	}

	//---------------------------------------------------------------------------------- compileWrite
	/**
	 * @param $property_name string
	 * @param $advices       array
	 * @return string
	 */
	private function compileWrite($property_name, $advices)
	{
		unset($advices['implements']);
		$code = '';
		$init = array();
		foreach ($advices as $aspect) {
			list($type, $advice) = $aspect;
			if ($type == 'write') {
				if (!isset($prototype)) {
					$prototype = '
	/** AOP */
	private function ' . $property_name . '_write()
	{
		$value = $this->' . $property_name . ';
		unset($this->_[\'' . $property_name . '\']);
		$this->' . $property_name . ' = $value;
		$value =& $this->' . $property_name . ';
';
				}
				$code .= $this->compileAdvice($property_name, 'read', $advice, $init);
			}
		}
		if (isset($prototype)) {
			return $prototype . $this->initCode($init) . $code . '

		$this->' . $property_name . '_ = $this->' . $property_name . ';
		unset($this->' . $property_name . ');
		$this->_[\'' . $property_name . '\'] = true;
	}
';
		}
		return '';
	}

	//-------------------------------------------------------------------------------- executeActions
	private function executeActions()
	{
		foreach ($this->actions as $method_name => $action) {
			if ($action == 'rename') {
				$regexp = Php_Method::regex($method_name);
				$this->class->source = preg_replace(
					$regexp,
					"\n\t" . '$2' . "\n\t" . '/* $4 */ private $5 function $6_0$7$8',
					$this->class->source
				);
			}
			else {
				trigger_error(
					'Don\'t know how to ' . $action . ' ' . $this->class->name . '::' . $method_name,
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
	private function initCode($init)
	{
		if (isset($init['7.element_type_name']) && isset($init['7.class_name'])) {
			$init['7.class_name_element_type_name'] = '$class_name = ' . $init['7.element_type_name'];
			unset($init['7.class_name']);
			unset($init['7.element_type_name']);
		}
		ksort($init);
		return $init ? ("\n\t\t" . join("\n\t\t", $init) . "\n") : '';
	}

	//-------------------------------------------------------------------------------- overrideMethod
	/**
	 * Override a public method
	 *
	 * @param $method_name string
	 * @return array action (rename, trait), call, Php_Method method, prototype
	 */
	private function overrideMethod($method_name)
	{
		$over = array('cases' => '');
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
			$methods = $this->class->getMethods(array('traits'));
			if (isset($methods[$method_name])) {
				$method = $methods[$method_name];
				$over['action'] = 'trait';
				$over['call']   = '$this->';
			}
			else {
				// the method exists into a parent class / trait and is not abstract
				$methods = $this->class->getMethods(array('inherited'));
				if (isset($methods[$method_name]) && !$methods[$method_name]->isAbstract()) {
					$method = $methods[$method_name];
					$over['action'] = false;
					$over['call']   = 'parent::';
				}
				else {
					// the method does not exist and the parent has no AOP properties
					$over['action'] = false;
					$over['call']   = false;
					// todo this check only getters, links and setters. This should check AOP links too.
					// the parent class has not this method but it has AOP properties)
					if (
						(substr($method_name, 0, 2) == '__')
						&& ($parent = $this->class->getParent())
						&& ($parent->type == 'class')
					) {
						foreach ($parent->getProperties(array('traits', 'inherited')) as $property) {
							$expr = '%'
								. '\n\s+\*\s+'               // each line beginnig by '* '
								. '@(getter|link|setter)'    // 1 : AOP annotation
								. '(?:\s+(?:([\\\\\w]+)::)?' // 2 : class name
								. '(\w+)?)?'                 // 3 : method or function name
								. '%';
							preg_match($expr, $property->documentation, $match);
							if ($match) {
								$over['cases'] .= "\n\t\t\t" . 'case \'' . $property->name . '\':';
							}
						}
						if ($over['cases']) {
							$parameters = '$property_name';
							switch ($method_name) {
								case '__get':
									$over['cases'] .= ' return parent::__get($property_name);';
									break;
								case '__isset':
									$over['cases'] .= ' return parent::__isset($property_name);';
									break;
								case '__set':
									$over['cases'] .= ' parent::__set($property_name, $value); return;';
									$parameters .= ', $value';
									break;
								case '__unset':
									$over['cases'] .= ' parent::__unset($property_name); return;';
									break;
								default:
									$parameters = '';
							}
						}
					}
				}
			}
		}
		// the method exists : prepare call and prototype
		if (isset($method)) {
			$over['method']    = $method;
			$over['prototype'] = $method->prototype;
			if (in_array($method_name, array('__get', '__isset', '__set', '__unset'))) {
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
				$over['call'] = ($method->returns() ? 'return ' : '')
					. $over['call'] . $method_name . $suffix . '(' . $method->getParametersCall() . ');'
					. ($method->returns() ? '' : ' return;');
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
		return $over;
	}

}
