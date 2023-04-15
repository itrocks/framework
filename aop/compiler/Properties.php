<?php
namespace ITRocks\Framework\AOP\Compiler;

use ITRocks\Framework\AOP\Weaver\Handler;
use ITRocks\Framework\PHP\Reflection_Class;
use ITRocks\Framework\PHP\Reflection_Method;
use ITRocks\Framework\PHP\Reflection_Source;
use ITRocks\Framework\Reflection\Annotation\Class_;
use ITRocks\Framework\Reflection\Annotation\Property\Link_Annotation;
use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Reflection\Attribute\Property\All;
use ITRocks\Framework\Reflection\Attribute\Property\Getter;
use ITRocks\Framework\Reflection\Attribute\Property\Setter;

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

	//------------------------------------------------------------------------------- SETTER_RESERVED
	private const SETTER_RESERVED = [
		'class_name', 'element_type', 'element_type_name', 'joinpoint', 'object',
		'property', 'property_name', 'result', 'stored', 'type', 'type_name', 'value'
	];

	//-------------------------------------------------------------------------------------- $actions
	/** @var string[] key is the original method name, value is the 'rename' or 'trait' action */
	private array $actions;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(Reflection_Class $class)
	{
		$this->class = $class;
	}

	//--------------------------------------------------------------------------------------- compile
	/** @return string[] */
	public function compile(array $advices) : array
	{
		ksort($advices);
		$this->actions = [];
		$methods       = [];

		$aop_properties = [];
		$id_properties  = [];
		$id_property    = '';
		if (
			$this->class->getAttributes(Store::class)
			&& !Class_\Link_Annotation::of($this->class)->value
		) {
			$id_property = LF . TAB . 'public int   $id;';
		}
		foreach ($this->class->getProperties() as $property) {
			$property_name = $property->getName();
			if (isset($advices[$property_name][0])) {
				$aop_properties[] = '$' . $property_name . '_';
				if (
					Link_Annotation::of($property)->isObject()
					&& ($property->getFinalClassName() === $this->class->getName())
				) {
					$id_properties[] = '$id_' . $property_name;
				}
			}
		}
		if ($aop_properties) {
			$aop_properties = join(', ', $aop_properties) . ';';
			$id_properties  = $id_properties ? ("\n\tpublic ?int  " . join(', ', $id_properties) . ';') : '';
			$methods[' aop properties '] = '
	/** AOP properties */
	public array $_;
	public mixed ' . $aop_properties . $id_property . $id_properties . '
';
		}

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
	 * @param $advice string[]|object[]|string
	 * @param $init   string[]
	 */
	private function compileAdvice(
		string $property_name, string $type, array|string $advice, array &$init
	) : string
	{
		$class_name = $this->class->name;

		/** @var $advice_class_name    string */
		/** @var $advice_method_name   string */
		/** @var $advice_function_name string */
		/** @var $advice_parameters    string[] */
		/** @var $advice_string        string [$object_, 'methodName'] | 'functionName' */
		/** @var $advice_has_return    boolean */
		/** @var $is_advice_static     boolean */
		[
			$advice_class_name, $advice_method_name, $advice_function_name,
			$advice_parameters, $advice_string, $advice_has_return, $is_advice_static
		] = $this->decodeAdvice($advice, $class_name);

		// $advice_parameters_string, $joinpoint_code
		$joinpoint_code = '';
		if ($advice_parameters) {
			$advice_parameters_string = '$' . join(', $', array_keys($advice_parameters));
			if (
				isset($advice_parameters[$property_name])
				&& !in_array($property_name, self::SETTER_RESERVED)
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
				$init['1.stored'] = 'if ((new \ReflectionProperty($this, ' . Q . $property_name . Q . '))->isInitialized($this)) {
			$stored =& $this->' . $property_name . ';
		}
		else {
			$get_stored_value_back = true;
			$stored = null;
		}';
				// TODO : $stored = null will work for nullable advice parameters only : hard typing will make other types crash. Use a constant which types would be accepted by all advices using it.
			}
			if (isset($advice_parameters['joinpoint'])) {
				$pointcut_string = '[$this, ' . Q . $property_name . Q . ']';
				$init[self::INIT_JOINPOINT]
					= '$joinpoint = new \ITRocks\Framework\AOP\Joinpoint' . BS . ucfirst($type) . '_Property('
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
				$init['8.class_name'] = '$class_name = $type->getElementTypeAsString();';
			}
		}
		else {
			$advice_parameters_string = '';
		}

		$advice_code = $this->generateAdviceCode(
			$advice, $advice_class_name, $advice_method_name, $advice_function_name,
			$advice_parameters_string, $advice_has_return, $is_advice_static, $joinpoint_code,
			LF . TAB . TAB, '$value'
		);

		if (isset($advice_parameters['stored']) || isset($advice_parameters['joinpoint'])) {
			$advice_code .= '
		if (isset($get_stored_value_back)) {
			unset($get_stored_value_back);
			if (isset($stored)) {
				if (!(new \ReflectionProperty($this, ' . Q . $property_name . Q . '))->isInitialized($this)) {
					$this->' . $property_name . ' = $stored;
				}
				$this->' . $property_name . ' =& $stored;
			} 
		}';
		}

		return $advice_code;
	}

	//------------------------------------------------------------------------------------ compileAop
	private function compileAop(array $advices) : string
	{
		$parent_code = '';
		$begin_code  = '
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
		if (!isset($this->_[' . Q . $property_name . Q . '])) {';
				if (!isset($property_advices['replaced'])) {
					$code .= '
			if ((new \ReflectionProperty($this, ' . Q . $property_name . Q . '))->isInitialized($this)) {
				$this->' . $property_name . '_ = $this->' . $property_name . ';
			}';
				}
				$code .= '
			unset($this->' . $property_name . ');
			$this->_[' . Q . $property_name . Q . '] = true;
		}';
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
					if (
						Getter::of($property)->callable
						|| Setter::of($property)
						|| All::of($property)?->value
						|| Link_Annotation::of($property)->value
					) {
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
	/** Compile __construct if there is at least one property declared in this class / traits */
	private function compileConstruct(array $advices) : string
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
	private function compileDefault(array $advices) : string
	{
		$over = $this->overrideMethod('__default', false);
		$code = '';
		foreach ($advices as $property_name => $property_advices) {
			if (isset($property_advices['default'])) {
				[$object, $method] = $property_advices['default'];
				$operator          = ($object === '$this') ? '->' : '::';
				if (in_array($object, ['$this', 'self', 'static'])) {
					$reflection_class = $this->class;
				}
				else {
					// may be quite slow, but I have to check if there is a $property parameter into default
					$reflection_class = Reflection_Source::ofClass($object)->getClass($object);
				}
				// TODO BUG getMethods on Date_Time stops after min method : there are missing lot of them !
				$reflection_methods = $reflection_class->getMethods([T_EXTENDS, T_IMPLEMENTS, T_USE]);
				$reflection_method  = $reflection_methods[$method] ?? null;
				$parameter_names    = $reflection_method?->getParametersNames(false) ?: [];
				$code .= "if (!(new \ReflectionProperty(\$this, '$property_name'))->isInitialized(\$this)) {"
					. LF . TAB . TAB . TAB . "\$this->$property_name = \\$object$operator$method(";
				if (($parameter_names[0] ?? null) === 'property') {
					$code .= 'new \ITRocks\Framework\Reflection\Reflection_Property'
						. "(__CLASS__, '$property_name')";
				}
				$code .= ');' . LF . TAB . TAB . '}' . LF . TAB . TAB;
			}
		}
		if (!isset($operator) && str_starts_with($over['call'], 'parent::')) {
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
	private function compileGet(array $advices) : string
	{
		$over = $this->overrideMethod('__get', true, $advices);
		$code = $over['prototype'] . '
		if (!isset($this->_) || !isset($this->_[$property_name])) {
			' . $over['call'] . '
		}';
		if ($over['cases']) {
			$switch = true;
			$code  .= '
		switch ($property_name) {';
		}
		foreach ($advices as $property_name => $property_advices) {
			if (isset($property_advices['replaced'])) {
				if (!isset($switch)) {
					$switch = true;
					$code  .= '
		switch ($property_name) {';
				}
				if ($property_advices['replaced'] === 'this') {
					$code .= '
			case ' . Q . $property_name . Q . ': $value =& $this; return $value;';
				}
				else {
					$code .= '
			case ' . Q . $property_name . Q . ': $value =& $this->' . $property_advices['replaced'] . '; return $value;';
				}
				if (isset($over['cases'][$property_name])) {
					unset($over['cases'][$property_name]);
					if (count($over['cases']) === 1) {
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
	private function compileIsset(array $advices) : string
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
				if ($property_advices['replaced'] === 'this') {
					$code .= '
			case ' . Q . $property_name . Q . ': return true;';
				}
				else {
					$code .= '
			case ' . Q . $property_name . Q . ': return isset($this->' . $property_advices['replaced'] . ');';
				}
			}
		}
		if (!isset($switch) && str_starts_with($over['call'], 'return parent::')) {
			return '';
		}
		if (isset($switch)) {
			$code .= '
		}';
		}
		return $code . '
		try {
			$ref = $this->__get($property_name);
		}
		catch (Error $error) {
			if (str_ends_with(
				$error->getMessage(),
				\'::$\' . $property_name . \' must not be accessed before initialization\'
			)) {
				return false;
			}
			throw $error;
		}
		$property_name .= \'_\';
		return isset($this->$property_name);
	}
';
	}

	//----------------------------------------------------------------------------------- compileRead
	private function compileRead(string $property_name, array $advices) : string
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
			[$type, $advice] = $aspect;
			if ($type === Handler::READ) {
				if (!isset($prototype)) {
					$prototype = '
	/** AOP */
	private function & _' . $property_name . '_read() : mixed
	{
		unset($this->_[' . Q . $property_name . Q . ']);
		if ((new \ReflectionProperty($this, ' . Q . $property_name . '_' . Q . '))->isInitialized($this)) {
			$this->' . $property_name . ' = $this->' . $property_name . '_;
			' . $last . '$value = $this->' . $property_name . ' =& $this->' . $property_name . '_;
			unset($this->' . $property_name . '_);
		}
		else {
			' . $last . '$value = ' . Q . '-AOP-UNINITIALIZED-' . Q . ';
		}
';
					// TODO : AOP_UNINITIALIZED will work for string advice parameters only : hard typing will make other types crash. Use a constant which types would be accepted by all advices using it.
				}
				$code .= $this->compileAdvice($property_name, Handler::READ, $advice, $init);
				if ($last) {
					$code .= '
		if ((new \ReflectionProperty($this, ' . Q . $property_name . Q . '))->isInitialized($this) && ($this->' . $property_name . ' !== $last)) {
			$this->_' . $property_name . '_write($this->' . $property_name . ');
			$last = $this->' . $property_name . ';
		}';
				}
			}
		}
		$code .= '

		if ((new \ReflectionProperty($this, ' . Q . $property_name . Q . '))->isInitialized($this)) {
			$this->' . $property_name . '_ =  $this->' . $property_name . ';
			$this->' . $property_name . '_ =& $this->' . $property_name . ';
		}';
		if (isset($prototype)) {
			if (isset($init[self::INIT_JOINPOINT])) {
				$reset_aop = '
		if (!$joinpoint->disable) {
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
	private function compileSet(array $advices) : string
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
				if ($property_advices['replaced'] === 'this') {
					$code .= '
			case ' . Q . $property_name . Q . ': foreach (get_object_vars($this) as $k => $v) if ($k !== \'' . $property_name . '\' && !isset($value->$k)) unset($this->$v); foreach (get_object_vars($value) as $k => $v) $this->$k = $v; return;';
				}
				else {
					$code .= '
			case ' . Q . $property_name . Q . ': $this->' . $property_advices['replaced'] . ' = $value; return;';
				}
				if (isset($over['cases'][$property_name])) {
					unset($over['cases'][$property_name]);
					if (count($over['cases']) === 1) {
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
		if (str_starts_with($over['call'], 'parent::')) {
			if (!isset($switch)) {
				return '';
			}
			return $code . '
		parent::__set($property_name, $value);
	}
';
		}
		return $code . '
		$id_property_name = \'id_\' . $property_name;
		if (is_object($value) && isset($value->id)) $this->$id_property_name = $value->id;
		elseif (property_exists($this, $id_property_name)) unset($this->$id_property_name);
		$property_name .= \'_\';
		$this->$property_name = $value;
	}
';
	}

	//---------------------------------------------------------------------------------- compileUnset
	private function compileUnset(array $advices) : string
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
				if ($property_advices['replaced'] === 'this') {
					$code .= '
			case ' . Q . $property_name . Q . ': trigger_error("You can\'t unset the link property", E_USER_ERROR); return;';
				}
				else {
					$code .= '
			case ' . Q . $property_name . Q . ': unset($this->' . $property_advices['replaced'] . '); return;';
				}
			}
		}
		if (isset($switch)) {
			$code .= '
		}';
		}
		elseif (str_starts_with($over['call'], 'parent::')) {
			return '';
		}
		return $code . '
		unset($this->{"id_$property_name"});
		$property_name .= \'_\';
		unset($this->$property_name);
	}
';
	}

	//--------------------------------------------------------------------------------- compileWakeup
	/**
	 * When we unserialize a method, default properties are created even if they were not in the
	 * serialized class (with a null value) : unset the properties overridden using AOP
	 */
	private function compileWakeup() : string
	{
		$over = $this->overrideMethod('__wakeup', false);
		if (str_starts_with($over['call'], 'parent::')) {
			return '';
		}
		return $over['prototype'] . '
		if (isset($this->_)) foreach (array_keys($this->_) as $aop_property) {
			unset($this->$aop_property);
		}
	}
';
	}

	//---------------------------------------------------------------------------------- compileWrite
	private function compileWrite(string $property_name, array $advices) : string
	{
		$code = '';
		$init = [];
		foreach ($advices as $key => $aspect) if (is_numeric($key)) {
			[$type, $advice] = $aspect;
			if ($type === Handler::WRITE) {
				if (!isset($prototype)) {
					$prototype = '
	/** AOP ' . $property_name . ' writer : implementation for #Setter called by __set */
	private function _' . $property_name . '_write(mixed $value) : void
	{
		if (isset($this->_[' . Q . $property_name . Q . '])) {
			unset($this->_[' . Q . $property_name . Q . ']);
			if ((new \ReflectionProperty($this, ' . Q . $property_name . '_' . Q . '))->isInitialized($this)) {
				$this->' . $property_name . ' =  $this->' . $property_name . '_;
				$this->' . $property_name . ' =& $this->' . $property_name . '_;
				unset($this->' . $property_name . '_);
			}
			$writer = true;
		}
';
				}
				$advice_code = $this->compileAdvice($property_name, Handler::WRITE, $advice, $init);
				if (str_contains($advice_code, '$value = ')) {
					$advice_code .= LF . TAB . TAB . '$this->' . $property_name . ' = $value;';
				}
				$code .= $advice_code;
			}
		}
		if (isset($prototype)) {
			return $prototype . $this->initCode($init) . $code . '

		if (isset($writer)) {
			if ((new \ReflectionProperty($this, ' . Q . $property_name . Q . '))->isInitialized($this)) {
				$this->' . $property_name . '_ =  $this->' . $property_name . ';
				$this->' . $property_name . '_ =& $this->' . $property_name . ';
				unset($this->' . $property_name . ');
			}
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
	private function executeActions() : void
	{
		foreach ($this->actions as $method_name => $action) {
			if ($action === 'rename') {
				$regexp = Reflection_Method::regex($method_name);
				$this->class->source = $this->class->source->setSource(preg_replace(
					$regexp,
					'$1$2/* $4 */ private $5function $6$7_0$8$9',
					$this->class->source->getSource())
				);
			}
			elseif ($action === 'trait') {
				// TODO eg __construct into a trait, we have to rename to __construct_* on use for
				trigger_error("Don't know how to $action {$this->class->name}::$method_name");
			}
			else {
				trigger_error("Don't know how to $action {$this->class->name}::$method_name", E_USER_ERROR);
			}
		}
	}

	//-------------------------------------------------------------------------------------- initCode
	/** @param $init string[] */
	private function initCode(array $init) : string
	{
		if (isset($init['7.element_type_name']) && isset($init['7.class_name'])) {
			$init['7.class_name_element_type_name'] = '$class_name = ' . $init['7.element_type_name'];
			unset($init['7.class_name']);
			unset($init['7.element_type_name']);
		}
		ksort($init);
		return $init ? (LF . TAB . TAB . join(LF . TAB . TAB, $init)) : '';
	}

	//-------------------------------------------------------------------------------- overrideMethod
	/**
	 * Override a public method
	 *
	 * @param $needs_return boolean if false, call will not need return statement
	 * @return array action (rename, trait), call, Reflection_Method method, prototype
	 */
	private function overrideMethod(
		string $method_name, bool $needs_return = true, array $advices = []
	) : array
	{
		$over       = [];
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
					$method = null;
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
				if ((count($parameters) === 2) && (end($parameters) !== 'value')) {
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
				$parameters = 'string $property_name';
				// Prepending $accessible to $over['call'] is not properly implemented for now :
				// - with property_exists(), all properties unset for AOP are not tested for accessibility
				// - without property_exists(), all accesses to dynamic properties like id will crash
				/**
				$accessible = '$calling_class = debug_backtrace()[1][\'class\'];
			if (property_exists($this, $property_name) && (get_class($this) !== $calling_class)) {
				$reflection_property = new \ReflectionProperty($this, $property_name);
				if ($reflection_property->isPrivate()) {
					throw new \ReflectionException(\'Could not access private property \' . get_class($this) . "::$property_name");
				}
				elseif ($reflection_property->isProtected() && !is_a($this, $calling_class)) {
					throw new \ReflectionException(\'Could not access protected property \' . get_class($this) . "::$property_name");
				}
			}
			';
				 */
				switch ($method_name) {
					case '__get':
						$over['call'] = 'return $this->$property_name;';
						break;
					case '__isset':
						$over['call'] = 'return isset($this->$property_name);';
						break;
					case '__set':
						$over['call'] = '$this->$property_name = $value; return;';
						$parameters  .= ', mixed $value';
						break;
					case '__unset':
						$over['call'] = 'unset($this->$property_name); return;';
						break;
					default:
						$parameters = '';
				}
			}
			$return_type = match($method_name) {
				'__construct', '__destruct' => '',
				'__get'   => 'mixed',
				'__isset' => 'bool',
				default   => 'void'
			};
			if ($return_type) {
				$return_type = ' : ' . $return_type;
			}
			$over['prototype'] = '
	/** AOP */
	public function ' . $method_name . '(' . $parameters . ')' . $return_type . '
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
	 * @return string[]
	 * @todo this check only getters, links and setters. This should check AOP links too.
	 * (the parent class has not this method, but it has AOP properties)
	 */
	private function parentCases(string $method_name, string &$parameters, array $advices) : array
	{
		$cases = [];
		if (
			in_array($method_name, ['__get', '__set'])
			&& ($this->class->type === T_CLASS)
			&& ($parent = $this->class->getParentClass())
		) {
			$attributes = ($method_name === '__get') ? [All::class, Getter::class] : [Setter::class];
			$type       = ($method_name === '__get') ? Handler::READ : Handler::WRITE;
			foreach ($parent->getProperties([T_EXTENDS, T_USE], $parent) as $property) {
				if (isset($advices[$property->name]['implements'][$type])) continue;
				$apply = ($method_name === '__get') && Link_Annotation::of($property)->value;
				if (!$apply) foreach (array_keys($property->getAttributes()) as $attribute_name) {
					if (!in_array($attribute_name, $attributes)) continue;
					$apply = true;
					break;
				}
				if (!$apply) continue;
				$cases[$property->name] = LF . TAB . TAB . TAB . 'case ' . Q . $property->name . Q . ':';
			}
			if ($cases) {
				$parameters = '$property_name';
				switch ($method_name) {
					case '__get':
						$cases[] = ' return parent::__get($property_name);';
						break;
					case '__set':
						$cases[] = ' parent::__set($property_name, $value); return;';
						$parameters .= ', $value';
						break;
					default:
						$parameters = '';
				}
			}
		}
		return $cases;
	}

}
