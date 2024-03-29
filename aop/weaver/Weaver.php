<?php
namespace ITRocks\Framework\AOP;

use ITRocks\Framework\AOP\Weaver\Handler;
use ITRocks\Framework\AOP\Weaver\IHandler;
use ITRocks\Framework\AOP\Weaver\IWeaver;
use ITRocks\Framework\Application;
use ITRocks\Framework\Plugin;
use ITRocks\Framework\Session;

/**
 * The Aop class is an interface to the Aop calls manager
 */
class Weaver implements IWeaver, Plugin
{

	//------------------------------------------------------------------------------------ $file_name
	/**
	 * The last loaded / saved cache file name
	 *
	 * @see loadJoinpoints
	 */
	private string $file_name = '';

	//----------------------------------------------------------------------------------- $joinpoints
	/**
	 * All joinpoints are stored here
	 *
	 * array[$class][$method][$index] = [$type, callback $advice]
	 * array[$function][$index]       = [$type, callback $advice]
	 */
	private array $joinpoints = [];

	//--------------------------------------------------------------------------------- afterFunction
	/**
	 * Weave an aspect after the execution of a given function
	 *
	 * Advices arguments are the pointcut object, then the arguments passed to the joinpoint function,
	 * and finally the value returned by the joinpoint method call.
	 * If set, the value returned by the advice will be the pointcut returned value.
	 * If not set, the result value passed as argument (that can be modified) will be returned
	 *
	 * @param $joinpoint string   The joinpoint defined like a call-back: 'functionName'
	 * @param $advice    callable The call-back call of the advice:
	 *                   ['class_name', 'methodName'], [$object, 'methodName'], 'functionName'
	 */
	public function afterFunction(string $joinpoint, callable $advice) : IHandler
	{
		$this->joinpoints[$joinpoint][] = [Handler::AFTER, $advice];
		return new Handler(Handler::AFTER, $joinpoint, count($this->joinpoints[$joinpoint]) - 1);
	}

	//----------------------------------------------------------------------------------- afterMethod
	/**
	 * Weave an aspect after the execution of a given method
	 *
	 * Advice arguments are the pointcut object, then the arguments passed to the joinpoint method,
	 * and finally the value returned by the joinpoint method call.
	 * If set, the value returned by the advice will be the pointcut returned value.
	 * If not set, the result value passed as argument (that can be modified) will be returned
	 *
	 * @param $joinpoint string[] The joinpoint defined like a call-back: ['class_name', 'methodName']
	 * @param $advice    callable The call-back call of the advice:
	 *                   ['class_name', 'methodName'], [$object, 'methodName'], 'functionName'
	 */
	public function afterMethod(array $joinpoint, callable $advice) : IHandler
	{
		$this->joinpoints[$joinpoint[0]][$joinpoint[1]][] = [Handler::AFTER, $advice];
		return new Handler(
			Handler::AFTER, $joinpoint, count($this->joinpoints[$joinpoint[0]][$joinpoint[1]]) - 1
		);
	}

	//-------------------------------------------------------------------------------- aroundFunction
	/**
	 * Weave an aspect instead of the execution of a given function
	 *
	 * Advice arguments are the pointcut object, then the arguments passed to the joinpoint method,
	 * and finally the value returned by the joinpoint method call.
	 * The value returned by the advice will be the pointcut returned value.
	 *
	 * @param $joinpoint string   The joinpoint defined like a call-back: 'functionName'
	 * @param $advice    callable The call-back call of the advice:
	 *                   ['class_name', 'methodName'], [$object, 'methodName'], 'functionName'
	 */
	public function aroundFunction(string $joinpoint, callable $advice) : IHandler
	{
		$this->joinpoints[$joinpoint][] = [Handler::AROUND, $advice];
		return new Handler(Handler::AROUND, $joinpoint, count($this->joinpoints[$joinpoint]) - 1);
	}

	//---------------------------------------------------------------------------------- aroundMethod
	/**
	 * Weave an aspect instead of the execution of a given method
	 *
	 * Advice arguments are the pointcut object, then the arguments passed to the joinpoint function,
	 * and finally the value returned by the joinpoint method call.
	 * The value returned by the advice will be the pointcut returned value.
	 *
	 * @param $joinpoint string[] The joinpoint defined like a call-back: ['class_name', 'methodName']
	 * @param $advice    callable The call-back call of the advice:
	 *                   ['class_name', 'methodName'], [$object, 'methodName'], 'functionName'
	 */
	public function aroundMethod(array $joinpoint, callable $advice) : IHandler
	{
		$this->joinpoints[$joinpoint[0]][$joinpoint[1]][] = [Handler::AROUND, $advice];
		return new Handler(
			Handler::AROUND, $joinpoint, count($this->joinpoints[$joinpoint[0]][$joinpoint[1]]) - 1
		);
	}

	//------------------------------------------------------------------------------------ backupFile
	/** Copy weaver.php to weaver.php.old for compiler changes detection */
	public function backupFile() : void
	{
		if (!$this->file_name) {
			$this->file_name = $this->defaultFileName();
		}
		if (file_exists($this->file_name . '.old')) {
			unlink($this->file_name . '.old');
		}
		copy($this->file_name, $this->file_name . '.old');
	}

	//-------------------------------------------------------------------------------- beforeFunction
	/**
	 * Weave an aspect before the execution of a given function
	 *
	 * Advice arguments are the pointcut object, then the arguments passed to the joinpoint function.
	 * The advice can return a value: if this value is set, the execution of the joinpoint will be
	 * cancelled and the returned value replaced by this one.
	 *
	 * @param $joinpoint string   The joinpoint defined like a call-back: ['class_name', 'methodName']
	 * @param $advice    callable The call-back call of the advice:
	 *                   ['class_name', 'methodName'], [$object, 'methodName'], 'functionName'
	 */
	public function beforeFunction(string $joinpoint, callable $advice) : IHandler
	{
		$this->joinpoints[$joinpoint][] = [Handler::BEFORE, $advice];
		return new Handler(Handler::BEFORE, $joinpoint, count($this->joinpoints[$joinpoint]) - 1);
	}

	//---------------------------------------------------------------------------------- beforeMethod
	/**
	 * Weave an aspect before the execution of a given method
	 *
	 * Advice arguments are the pointcut object, then the arguments passed to the joinpoint method
	 * The advice can return a value: if this value is set, the execution of the joinpoint will be
	 * cancelled and the returned value replaced by this one.
	 *
	 * @param $joinpoint string[] The joinpoint defined like a call-back: ['class_name', 'methodName']
	 * @param $advice    callable The call-back call of the advice:
	 *                   ['class_name', 'methodName'], [$object, 'methodName'], 'functionName'
	 */
	public function beforeMethod(array $joinpoint, callable $advice) : IHandler
	{
		$this->joinpoints[$joinpoint[0]][$joinpoint[1]][] = [Handler::BEFORE, $advice];
		return new Handler(
			Handler::BEFORE, $joinpoint, count($this->joinpoints[$joinpoint[0]][$joinpoint[1]]) - 1
		);
	}

	//----------------------------------------------------------------------------- changedClassNames
	/** @return string[] class names of changed joinpoints since before call of saveJoinpoints() */
	public function changedClassNames() : array
	{
		$changed_class_names = [];

		$new_classes = $this->fileClassesAsText($this->file_name);
		$old_classes = $this->fileClassesAsText($this->file_name . '.old');

		// added / changed classes
		foreach ($new_classes as $class_name => $new_configuration) {
			if (!isset($old_classes[$class_name]) || ($new_configuration !== $old_classes[$class_name])) {
				if (isset($GLOBALS['D'])) echo "AOP Added / Changed class $class_name" . BRLF;
				$changed_class_names[$class_name] = $class_name;
			}
		}
		// removed classes
		foreach (array_keys($old_classes) as $class_name) {
			if (!isset($new_classes[$class_name])) {
				if (isset($GLOBALS['D'])) echo "AOP Removed class $class_name" . BRLF;
				$changed_class_names[$class_name] = $class_name;
			}
		}
		return $changed_class_names;
	}

	//------------------------------------------------------------------------------- defaultFileName
	public function defaultFileName() : string
	{
		return Application::getCacheDir() . SL . 'weaver.php';
	}

	//------------------------------------------------------------------------------------- dumpArray
	/**
	 * Change joinpoints array into a dumped php source [...].
	 * Replaces all objects by current session plugins getters
	 */
	private function dumpArray(array $array) : string
	{
		$lf1  = LF . TAB;
		$lf2  = $lf1 . TAB;
		$lf3  = $lf2 . TAB;
		$dump = '[';
		foreach ($array as $class => $j1) {
			$dump .= $lf1 . Q . $class . Q . ' => [';
			foreach ($j1 as $method => $j2) {
				if (is_numeric($method)) {
					$this->dumpArrayDetail($dump, $j2, $lf2);
				}
				else {
					$dump .= $lf2 . Q . $method . Q . ' => [';
					foreach ($j2 as $joinpoint) {
						$this->dumpArrayDetail($dump, $joinpoint, $lf3);
					}
					$dump .= $lf2 . '],';
				}
			}
			$dump .= $lf1 . '],';
		}
		$dump .= LF . ']';
		return $dump;
	}

	//------------------------------------------------------------------------------- dumpArrayDetail
	protected function dumpArrayDetail(string &$dump, mixed $joinpoint, string $lf) : void
	{
		$dump .= $lf . '[' . Q . $joinpoint[0] . Q . ', ';
		if (is_array($advice = $joinpoint[1])) {
			$dump .= '[';
			if (is_object($advice[0])) {
				$dump .= '$plugins->get(' . get_class($advice[0]) . '::class), ';
			}
			else {
				$dump .= $advice[0] . '::class, ';
			}
			$dump .= Q . $advice[1] . Q . ']';
		}
		else {
			$dump .= Q . $advice . Q;
		}
		$dump .= '],';
	}

	//----------------------------------------------------------------------------- fileClassesAsText
	/** @return string[] key = name of the class, value = its raw configuration into weaver.php */
	private function fileClassesAsText(string $file_name) : array
	{
		$classes = [];
		$parts   = explode("\n\t'", file_exists($file_name) ? file_get_contents($file_name) : '');
		foreach ($parts as $part) {
			if (!$part || !ctype_upper($part[0])) {
				continue;
			}
			$class_name           = substr($part, 0, strpos($part, Q));
			$classes[$class_name] = rtrim(substr($part, strlen($class_name) + 1), "\t\n\r,");
		}
		return $classes;
	}

	//---------------------------------------------------------------------------------- getJoinpoint
	/**
	 * Gets existing joinpoints for a class method or property
	 *
	 * @param $joinpoint string[] A class + a method or property name
	 * @return array [$index] = [$type, callback $advice]
	 */
	public function getJoinpoint(array $joinpoint) : array
	{
		if (isset($this->joinpoints[$joinpoint[0]][$joinpoint[1]])) {
			return $this->joinpoints[$joinpoint[0]][$joinpoint[1]];
		}
		return [];
	}

	//--------------------------------------------------------------------------------- getJoinpoints
	/**
	 * Gets existing joinpoints for a class name
	 *
	 * @param $joinpoint_name string Joinpoint class or function name
	 * @return array [$method][$index] = [$type, callback $advice]
	 */
	public function getJoinpoints(string $joinpoint_name = '') : array
	{
		return $joinpoint_name
			? ($this->joinpoints[$joinpoint_name] ?? [])
			: $this->joinpoints;
	}

	//--------------------------------------------------------------------------------- hasJoinpoints
	public function hasJoinpoints() : bool
	{
		return (bool)$this->joinpoints;
	}

	//-------------------------------------------------------------------------------- loadJoinpoints
	public function loadJoinpoints(string $file_name) : void
	{
		$this->file_name  = $file_name;
		$this->joinpoints = file_exists($file_name) ? (include $file_name) : [];
	}

	//---------------------------------------------------------------------------------- readProperty
	/**
	 * @param $joinpoint string[] The joinpoint defined like a call-back:
	 *                   ['class_name', 'property_name']
	 * @param $advice    callable The call-back call of the advice:
	 *                   ['class_name', 'methodName'], [$object, 'methodName'], 'functionName'
	 */
	public function readProperty(array $joinpoint, callable $advice) : IHandler
	{
		$this->joinpoints[$joinpoint[0]][$joinpoint[1]][] = [Handler::READ, $advice];
		return new Handler(
			Handler::READ, $joinpoint, count($this->joinpoints[$joinpoint[0]][$joinpoint[1]]) - 1
		);
	}

	//---------------------------------------------------------------------------------------- remove
	/** Un-weave an aspect */
	public function remove(IHandler $handler) : void
	{
		/** @var $handler Handler */
		if (is_string($handler->joinpoint)) {
			$this->joinpoints[$handler->joinpoint][$handler->index] = null;
		}
		else {
			$this->joinpoints[$handler->joinpoint[0]][$handler->joinpoint[1]][$handler->index] = null;
		}
	}

	//-------------------------------------------------------------------------------- saveJoinpoints
	/** @param $file_name string */
	public function saveJoinpoints(string $file_name) : void
	{
		$this->file_name = $file_name;
		// write new weaver.php file content
		script_put_contents(
			$file_name,
			'<?php' . LF . LF
			. '$plugins = ' . Session::class . '::current()->plugins;' . LF . LF
			. 'return ' . $this->dumpArray($this->joinpoints) . ';' . LF
		);
	}

	//--------------------------------------------------------------------------------- writeProperty
	/**
	 * @param $joinpoint string[] The joinpoint defined like a call-back:
	 *                   ['class_name', 'property_name']
	 * @param $advice    callable The call-back call of the advice:
	 *                   ['class_name', 'methodName'], [$object, 'methodName'], 'functionName'
	 */
	public function writeProperty(array $joinpoint, callable $advice) : IHandler
	{
		$this->joinpoints[$joinpoint[0]][$joinpoint[1]][] = [Handler::WRITE, $advice];
		return new Handler(
			Handler::WRITE, $joinpoint, count($this->joinpoints[$joinpoint[0]][$joinpoint[1]]) - 1
		);
	}

}
