<?php
namespace ITRocks\Framework;

use ITRocks\Framework\AOP\Include_Filter;
use ITRocks\Framework\AOP\Joinpoint\Around_Method;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Uri;
use ITRocks\Framework\PHP\Class_File_Name_Getter;
use ITRocks\Framework\PHP\Compiler;
use ITRocks\Framework\PHP\Compiler\More_Sources;
use ITRocks\Framework\PHP\ICompiler;
use ITRocks\Framework\PHP\Reflection_Source;
use ITRocks\Framework\Plugin\Configurable;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\Tools\Namespaces;

/**
 * Automatic routing class
 */
class Router implements Class_File_Name_Getter, Configurable, IAutoloader, ICompiler, Registerable
{

	//-------------------------------------------------------------------------------------- $changes
	/**
	 * @var boolean
	 */
	public bool $changes = false;

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * @var string when set : the class name used for possible html templates indexing
	 */
	private string $class_name;

	//---------------------------------------------------------------------------------- $class_paths
	/**
	 * @var string[] key is full class name, value is file path
	 */
	public array $class_paths = [];

	//----------------------------------------------------------------------------- $controller_calls
	/**
	 * @var array keys are controller and method name, value is [$class_name, $method)
	 */
	public array $controller_calls = [];

	//-------------------------------------------------------------------------- $element_class_names
	/**
	 * @var string[] key is set class name, value is matching element class name
	 */
	public array $element_class_names = [];

	//-------------------------------------------------------------------------------------- $exclude
	/**
	 * @var string
	 */
	public string $exclude = '';

	//----------------------------------------------------------------------------- $full_class_names
	/**
	 * @var string[] key is short class name, value is full class name
	 */
	public array $full_class_names = [];

	//------------------------------------------------------------------------------- $html_templates
	/**
	 * @var array
	 */
	public array $html_templates = [];

	//---------------------------------------------------------------------------------- $routes_file
	/**
	 * @var string
	 */
	public string $routes_file;

	//----------------------------------------------------------------------------------- $view_calls
	/**
	 * @var array
	 */
	public array $view_calls = [];

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Open routes cache file
	 *
	 * @param $configuration array
	 */
	public function __construct($configuration = [])
	{
		if (isset($configuration['exclude'])) {
			$this->exclude = '(' . join('|', $configuration['exclude']) . ')';
		}

		$script_name = substr($_SERVER['SCRIPT_NAME'], strrpos($_SERVER['SCRIPT_NAME'], SL) + 1, -4);
		$this->routes_file = getcwd() . SL . $script_name . SL . $script_name . '/cache/routes.php';
		if (file_exists($this->routes_file)) {
			include $this->routes_file;
		}

		Namespaces::$router = $this;
		//spl_autoload_register([$this, 'autoload']);
	}

	//----------------------------------------------------------------------------------- __serialize
	/**
	 * @return array
	 */
	public function __serialize() : array
	{
		return [];
	}

	//--------------------------------------------------------------------------------- __unserialize
	/**
	 * @param $serialized array
	 * @see Router::__construct()
	 */
	public function __unserialize(array $serialized)
	{
		// routes file is read into __construct()
	}

	//---------------------------------------------------------------------------------- addClassPath
	/**
	 * @param $class_name string
	 * @return string
	 */
	private function addClassPath(string $class_name) : string
	{
		$result = '';
		foreach ($this->filesFor(Namespaces::shortClassName($class_name)) as $file_name) {
			$in_class_name = $this->fileToClassName($file_name);
			if ($in_class_name) {
				$this->changes = true;
				$this->class_paths[$in_class_name] = $file_name;
				if ($in_class_name === $class_name) {
					$result = $file_name;
					break;
				}
			}
		}
		return $result;
	}

	//------------------------------------------------------------------------------ addFullClassName
	/**
	 * @param $short_class_name string
	 * @return string
	 */
	private function addFullClassName(string $short_class_name) : string
	{
		$result = '';
		foreach ($this->filesFor($short_class_name) as $file_name) {
			$in_class_name = $this->fileToClassName($file_name);
			if ($in_class_name) {
				$this->changes = true;
				if (!$result) {
					$this->full_class_names[$short_class_name] = $result = $in_class_name;
				}
				$this->class_paths[$in_class_name] = $file_name;
			}
		}
		return $result;
	}

	//-------------------------------------------------------------------------------------- autoload
	/**
	 * @param $class_name string
	 * @return string
	 * @throws Include_Filter\Exception
	 */
	public function autoload(string $class_name) : string
	{
		$file_path = $this->getClassFileName($class_name);
		if ($file_path) {
			include_once Include_Filter::file($file_path);
			// if included file does not contain the good class : will need to scan for the right file
			if (
				!class_exists($class_name) && !interface_exists($class_name) && !trait_exists($class_name)
			) {
				unset($this->class_paths[$class_name]);
				$file_path = $this->autoload($class_name);
			}
			// initializes plugin
			elseif (Session::current()->plugins->has($class_name)) {
				Session::current()->plugins->get($class_name);
			}
		}
		return $file_path;
	}

	//--------------------------------------------------------------------------------------- compile
	/**
	 * Compile source file into its class path
	 *
	 * @param $source   Reflection_Source
	 * @param $compiler Compiler|null
	 * @return boolean false as this compilation does not modify the class source
	 */
	public function compile(Reflection_Source $source, Compiler $compiler = null) : bool
	{
		foreach ($source->getClasses() as $class) {
			// class name to file path
			if (
				!isset($this->class_paths[$class->name])
				|| ($this->class_paths[$class->name] !== $source->file_name)
			) {
				$this->class_paths[$class->name] = $source->file_name;
				$this->changes = true;
			}
			// set class name to class name
			$set_class_name = $class->getSetClassName();
			if (
				!isset($this->element_class_names[$set_class_name])
				|| ($this->element_class_names[$set_class_name] !== $class->name)
			) {
				$this->element_class_names[$set_class_name] = $class->name;
				$this->changes = true;
			}
			/*
			// short class name to class name
			$short_class_name = Namespaces::shortClassName($class->name);
			if (
				!isset($this->full_class_names[$short_class_name])
				|| ($this->full_class_names[$short_class_name] !== $class->name)
			) {
				$this->full_class_names[$short_class_name] = $class->name;
				$this->changes = true;
			}
			*/
			/*
			// set short class name to set class name
			$short_class_name = Namespaces::shortClassName($set_class_name);
			if (
				!isset($this->full_class_names[$short_class_name])
				|| ($this->full_class_names[$short_class_name] !== $set_class_name)
			) {
				$this->full_class_names[$short_class_name] = $set_class_name;
				$this->changes = true;
			}
			*/
		}
		return false;
	}

	//------------------------------------------------------------------------------- fileToClassName
	/**
	 * @param $file_name string
	 * @return string
	 */
	private function fileToClassName(string $file_name) : string
	{
		$buffer = file_get_contents($file_name);
		$expr = '%\n\s*(?:namespace\s+)([\w\\\\]+)%s';
		preg_match($expr, $buffer, $match);
		$in_namespace = $match ? $match[1] : '';
		$expr = '%\n\s*(?:final\s+)?(?:abstract\s+)?(?:class|interface|trait)\s+(\w+)%s';
		preg_match($expr, $buffer, $match);
		return $match
			? ($in_namespace ? ($in_namespace . BS . $match[1]) : $match[1])
			: '';
	}

	//-------------------------------------------------------------------------------------- filesFor
	/**
	 * @param $short_class_name string
	 * @return string[] files path
	 */
	private function filesFor(string $short_class_name) : array
	{
		$result = [];
		$match  = null;
		foreach (explode(':', get_include_path()) as $path) {
			if ($this->exclude) preg_match($this->exclude, $path, $match);
			if (!$match) {
				if (file_exists($path . SL . $short_class_name . '.php')) {
					$result[] = $path . SL . $short_class_name . '.php';
				}
			}
		}
		return $result;
	}

	//------------------------------------------------------------------------------ getClassFileName
	/**
	 * Checks, searches, and gets the file path for a class name
	 *
	 * @param $class_name string
	 * @return ?string
	 */
	public function getClassFileName(string $class_name) : string
	{
		if (!isset($this->class_paths[$class_name])) {
			return $this->addClassPath($class_name);
		}
		$class_path = $this->class_paths[$class_name];
		if (!file_exists($class_path)) {
			$class_path = $this->addClassPath($class_name);
		}
		return $class_path;
	}

	//------------------------------------------------------------------------- getElementClassNameOf
	/**
	 * @param $class_name string
	 * @return ?string
	 */
	public function getElementClassNameOf(string $class_name) : string
	{
		return $this->element_class_names[$class_name] ?? '';
	}

	//------------------------------------------------------------------------------ getFullClassName
	/**
	 * Checks, searches, and gets the main full class name for a short class name
	 *
	 * @param $short_class_name string
	 * @return string
	 */
	public function getFullClassName(string $short_class_name) : string
	{
		if (str_contains($short_class_name, BS)) {
			trigger_error('Full class name given', E_USER_ERROR);
		}
		if (!isset($this->full_class_names[$short_class_name])) {
			return $this->addFullClassName($short_class_name);
		}
		$class_name = $this->full_class_names[$short_class_name];
		if (!class_exists($class_name)) {
			$class_name = $this->addFullClassName($short_class_name);
		}
		return $class_name;
	}

	//-------------------------------------------------------------------- getPossibleControllerCalls
	/**
	 * @param $object    Uri
	 * @param $joinpoint Around_Method
	 * @return callable[]
	 */
	public function getPossibleControllerCalls(Uri $object, Around_Method $joinpoint) : array
	{
		if (
			isset($this->controller_calls[$object->controller_name][$object->feature_name])
			&& !isset($GLOBALS['F'])
		) {
			$controller = $this->controller_calls[$object->controller_name][$object->feature_name];
			if (method_exists($controller[0], $controller[1])) {
				return [$controller];
			}
		}
		if (
			isset($GLOBALS['F'])
			&& isset($this->controller_calls[$object->controller_name][$object->feature_name])
		) {
			echo 'Router controller call = '
				. json_encode($this->controller_calls[$object->controller_name][$object->feature_name])
				. BR;
		}
		$possible_controller_calls = $joinpoint->process();
		if (isset($GLOBALS['F'])) {
			echo '<pre>'
				. 'Possible controller calls = ' . print_r($possible_controller_calls, true)
				. '</pre>';
		}
		return $possible_controller_calls;
	}

	//---------------------------------------------------------------------- getPossibleHtmlTemplates
	/**
	 * @param $class_name    string
	 * @param $feature_names string|string[]
	 * @param $joinpoint     Around_Method
	 * @return string[]
	 */
	public function getPossibleHtmlTemplates(
		string $class_name, array|string $feature_names, Around_Method $joinpoint
	) : array
	{
		if (is_array($feature_names)) {
			$feature_names = join(DOT, $feature_names);
		}
		if (isset($this->html_templates[$class_name][$feature_names])) {
			$html_template = $this->html_templates[$class_name][$feature_names];
			if (file_exists($html_template)) {
				$this->class_name = '';
				return [$html_template];
			}
		}
		$this->class_name = $class_name;
		return $joinpoint->process();
	}

	//-------------------------------------------------------------------------- getPossibleViewCalls
	/**
	 * @param $class_name    string
	 * @param $feature_names string|string[]
	 * @param $joinpoint     Around_Method
	 * @return callable[]
	 */
	public function getPossibleViewCalls(
		string $class_name, array|string $feature_names, Around_Method $joinpoint
	) : array
	{
		if (is_array($feature_names)) {
			$feature_names = join(DOT, $feature_names);
		}
		if (isset($this->view_calls[$class_name][$feature_names])) {
			$view = $this->view_calls[$class_name][$feature_names];
			if (method_exists($view[0], $view[1])) {
				return [$view];
			}
		}
		return $joinpoint->process();
	}

	//-------------------------------------------------------------------------- moreSourcesToCompile
	/**
	 * Extends the list of files to compile
	 *
	 * @param $more_sources More_Sources
	 */
	public function moreSourcesToCompile(More_Sources $more_sources)
	{
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		/*
		$aop = $register->aop;
		$aop->beforeMethod(
			[Main::class, 'executeController'],
			[$this, 'setPossibleControllerCall']
		);
		$aop->beforeMethod(
			[Set::class, 'elementClassNameOf'],
			[$this, 'getElementClassNameOf']
		);
		$aop->aroundMethod(
			[View::class, 'getPossibleViews'],
			[$this, 'getPossibleViewCalls']
		);
		$aop->beforeMethod(
			[View::class, 'executeView'],
			[$this, 'setPossibleViewCall']
		);
		$aop->aroundMethod(
			[Engine::class, 'getPossibleTemplates'],
			[$this, 'getPossibleHtmlTemplates']
		);
		$aop->beforeMethod(
			[Default_View::class, 'executeTemplate'],
			[$this, 'setPossibleHtmlTemplate']
		);
		$aop->beforeMethod(
			[Default_View::class, 'executeTemplate'],
			[$this, 'setPossibleHtmlTemplate']
		);
		*/
	}

	//--------------------------------------------------------------------- setPossibleControllerCall
	/**
	 * @param $uri         Uri
	 * @param $controller  string
	 * @param $method_name string
	 */
	public function setPossibleControllerCall(Uri $uri, string $controller, string $method_name)
	{
		if (isset($this->controller_calls[$uri->controller_name][$uri->feature_name])) {
			[$check_controller, $check_method_name]
				= $this->controller_calls[$uri->controller_name][$uri->feature_name];
			$changes = (($check_controller !== $controller) || ($check_method_name !== $method_name));
		}
		else {
			$changes = true;
		}
		if ($changes) {
			$this->controller_calls[$uri->controller_name][$uri->feature_name] = [
				$controller, $method_name
			];
			$this->changes = true;
		}
	}

	//----------------------------------------------------------------------- setPossibleHtmlTemplate
	/**
	 * @param $template_file string
	 * @param $parameters    array
	 * @param $feature_name  string
	 */
	public function setPossibleHtmlTemplate(
		string $template_file, array $parameters, string $feature_name
	) {
		if (!$this->class_name) {
			return;
		}
		$features = isset($parameters[Feature::FEATURE])
			? ($parameters[Feature::FEATURE] . DOT . $feature_name)
			: $feature_name;
		$this->html_templates[$this->class_name][$features] = $template_file;
		$this->changes = true;
	}

	//--------------------------------------------------------------------------- setPossibleViewCall
	/**
	 * @param $class_name       string
	 * @param $feature_name     string
	 * @param $parameters       array
	 * @param $view             string
	 * @param $view_method_name string
	 */
	public function setPossibleViewCall(
		string $class_name, string $feature_name, array $parameters, string $view,
		string $view_method_name
	) {
		$features = isset($parameters[Feature::FEATURE])
			? ($parameters[Feature::FEATURE] . DOT . $feature_name)
			: $feature_name;
		if (isset($this->view_calls[$class_name][$features])) {
			[$check_view, $check_view_method_name] = $this->view_calls[$class_name][$features];
			$changes = (($check_view !== $view) || ($check_view_method_name !== $view_method_name));
		}
		else {
			$changes = true;
		}
		if ($changes) {
			$this->view_calls[$class_name][$features] = [$view, $view_method_name];
			$this->changes = true;
		}
	}

}
