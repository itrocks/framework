<?php
namespace Framework;

class Html_Template
{

	//-------------------------------------------------------------------------------------- $content
	/**
	 * @var string
	 */
	public $content;

	//-------------------------------------------------------------------------------------- $feature
	/**
	 * @var string
	 */
	public $feature;

	//--------------------------------------------------------------------------------------- $object
	/**
	 * @var object
	 */
	public $object;

	//----------------------------------------------------------------------------------------- $path
	/**
	 * Template file path (base for css / javascript links)
	 *
	 * @var string
	 */
	private $path;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Constructs a template object, initializing the source data object and the template access path
	 *
	 * @param object $object
	 * @param string $template_file full path to template file
	 * @param string $feature_name feature name
	 */
	public function __construct($object, $template_file, $feature_name)
	{
		$this->object  = $object;
		$this->path    = substr($template_file, 0, strrpos($template_file, "/"));
		$this->content = file_get_contents($template_file);
		$this->feature = $feature_name;
	}

	//----------------------------------------------------------------------------------------- parse
	/**
	 * Parse the template replacing templating codes by object's properties and functions results
	 *
	 * @return string
	 */
	public function parse()
	{
		$content = $this->content;
		$content = $this->parseContainer($content);
		$content = $this->parseVars($content, $this->object);
		$content = $this->replaceLinks($content);
		$content = $this->replaceUris($content);
		return $content;
	}

	//-------------------------------------------------------------------------------- parseContainer
	/**
	 * Replace code before <!--BEGIN--> and after <!--END--> by the html main container's code
	 *
	 * @param  string $content
	 * @return string updated content
	 */
	private function parseContainer($content)
	{
		$i = strpos($content, "<!--BEGIN-->");
		if ($i !== false) {
			$j = strrpos($content, "<!--END-->");
			$container = file_get_contents(Html_Configuration::$main_template, true);
			$content = str_replace(
				'{content}',
				substr($content, $i + 12, $j - $i - 12),
				$container
			);
		}
		return $content;
	}

	//------------------------------------------------------------------------------------- parseFunc
	/**
	 * Parse a special data / function and returns its return value
	 *
	 * @param object $object
	 * @param string $func_name
	 */
	private function parseFunc($object, $func_name)
	{
		if (isset($this->$func_name)) {
			return $this->$func_name;
		} else {
			$func_name = Names::propertyToMethod($func_name, "get");
			return Html_Template_Funcs::$func_name($this, $object);
		}
	}

	//-------------------------------------------------------------------------------------- parseVar
	/**
	 * Parse a variable / function and returns its return value
	 *
	 * @param  object $object
	 * @param  string $var_name
	 * @return string
	 */
	private function parseVar($object, $var_name)
	{
		foreach (explode(".", $var_name) as $property_name) {
			if ($property_name[0] === '@') {
				$object = $this->parseFunc($object, substr($property_name, 1));
			} elseif (strpos($property_name, '()')) {
				$objet = $object->$property_name();
			} else {
				$object = $object->$property_name;
			}
		}
		if (is_object($object)) {
			if (method_exists($object, "__toString")) {
				return strval($object);
			} else {
				return "";
			}
		} else {
			return $object;
		}
	}

	//------------------------------------------------------------------------------------- parseVars
	/**
	 * Parse all variables from the template
	 *
	 * @example parsed variables will have those forms :
	 *   simply display variable or function /method result value :
	 *     {variable_name}
	 *     {methodName()}
	 *     {object_property.sub-object_property}
	 *     {@html_template_function_name}
	 *   condition / loop on variable or function / method result :
	 *     <!--variable_name-->(...)<!--variable_name-->
	 *     <!--methodName()-->(...)<!--methodName()-->
	 *     <!--@function-->(...)<!--@function-->
	 *
	 * @param  string $content
	 * @param  object $object
	 * @return string
	 */
	private function parseVars($content, $object)
	{
		$content = $this->parseLoops($content, $object);
		$j = 0;
		while (($i = strpos($content, '{', $i)) !== false) {
			$i ++;
			if ($this->parseThis($content, $i)) {
				$j = strpos($content, '}', $i);
				$var_name = substr($content, $i, $j - $i);
				$value = $this->parseVar($object, $var_name);
				$content = substr($content, 0, $i - 1) . $value . substr($content, $j + 1);
				$i --;
			}
		}
		return $content;
	}

	//------------------------------------------------------------------------------------ parseLoops
	/**
	 * Parse all loops and conditions from the template
	 *
	 * @example parsed conditions will have those forms :
	 *   <!--variable_name-->(...)<!--variable_name-->
	 *   <!--methodName()-->(...)<!--methodName()-->
	 *   <!--@function-->(...)<!--@function-->
	 *
	 * @param  string $string
	 * @param  object $object
	 * @return string updated content
	 */
	private function parseLoops($content, $object)
	{
		$j = 0;
		while (($i = strpos($content, '<!--' , $i)) !== false) {
			$i += 4;
			$j = strpos($content, '-->', $i);
			if ($this->parseThis($content, $i)) {
				$var_name = substr($content, $i, $j - $i);
				$length = strlen($var_name);
				$i += $length + 3;
				$j = strrpos($content, '<!--' . $var_name . '-->', $j);
				$loop_content = substr($content, $i, $j - $i);
				$this->removeSample($loop_content);
				$separator = $this->parseSeparator($loop_content);
				$elements = $this->parseVar($object, $var_name);
				if (is_array($elements)) {
					$do = false;
					$loop_insert = "";
					foreach ($elements as $element) {
						if ($do) $loop_insert .= $this->parseVars($separator, $element); else $do = true;
						$sub_content = $this->parseVars($loop_content, $element);
						$loop_insert .= $sub_content;
					}
				} elseif (strlen($elements)) {
					$loop_insert = $this->parseVars($loop_content, $object);
				} else {
					$loop_insert  = "";
				}
				$content = substr($content, 0, $i - $length - 7)
					. $loop_insert
					. substr($content, $j + $length + 7);
				$i -= 4;
			}
		}
		return $content;
	}

	//-------------------------------------------------------------------------------- parseSeparator
	/**
	 * Remove <!--separator-->(...) code from a loop content, and returns the separator content
	 *
	 * @param  string $content
	 * @return string the separator content
	 */
	private function parseSeparator(&$content)
	{
		if (($k = strpos($content, "<!--separator-->")) !== false) {
			$separator = substr($content, $k + 16);
			$content = substr($content, 0, $k);
		} else {
			$separator = "";
		}
		return $separator;
	}

	//------------------------------------------------------------------------------------- parseThis
	/**
	 * Return true if the text at position $i of $content is a variable or function name
	 *
	 * @param  string $content
	 * @param  integer $i
	 * @return boolean
	 */
	private function parseThis($content, $i)
	{
		$c = $content[$i];
		return (($c >= 'a') && ($c <= 'z')) || ($c == '@');		
	}

	//---------------------------------------------------------------------------------- removeSample
	/**
	 * Remove <!--sample-->(...) code from loop content
	 *
	 * @param string $content
	 */
	private function removeSample(&$content)
	{
		if (($i = strrpos($content, "<!--sample-->")) !== false) {
			$content = substr($content, 0, $i);
		}
	}

	//---------------------------------------------------------------------------------- replaceLinks
	/**
	 * Replace links with correct absolute paths into $content
	 *
	 * @param string $content
	 */
	private function replaceLinks($content)
	{
		$links = array('action="', 'href="');
		foreach ($links as $link) {
			$i = 0;
			while (($i = strpos($content, $link, $i)) !== false) {
				$i += strlen($link);
				$j = strpos($content, '"', $i);
				if (substr($content, $i, 1) === '/') {
					$full_path = substr($_SERVER["PHP_SELF"], 0, strpos($_SERVER["PHP_SELF"], ".php"))
						. substr($content, $i, $j - $i);
					$content = substr($content, 0, $i) . $full_path . substr($content, $j);
				}
			}
		}
		return $content;
	}

	//----------------------------------------------------------------------------------- replaceUris
	/**
	 * Replace URIs with correct URIs paths into $content
	 *
	 * @param  string $content
	 * @return string updated content
	 */
	private function replaceUris($content)
	{
		$links = array('@import "', 'src="');
		foreach ($links as $link) {
			$i = 0;
			while (($i = strpos($content, $link, $i)) !== false) {
				$i += strlen($link);
				$j = strpos($content, '"', $i);
				$full_path = substr(
					$this->path . "/" . substr($content, $i, $j - $i),
					strlen($_SERVER["DOCUMENT_ROOT"])
				);
				$content = substr($content, 0, $i) . $full_path . substr($content, $j);
			}
		}
		return $content;
	}

}
