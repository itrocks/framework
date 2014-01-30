<?php
namespace SAF\Framework;

/**
 * Aop_Dynamics stores aop links to enable at each script start
 */
class Aop_Dynamics implements Plugin
{
	use Current { current as private pCurrent; }

	//---------------------------------------------------------------------------------------- $links
	/**
	 * Aop dynamic links list
	 *
	 * Each entry is an array which elements are each an Aop entry array :
	 * 0 : "after", "after_returning", "after_throwing", "around", "before"
	 * 1 : the pointcut class name (can be short or long)
	 * 2 : the pointcut method (if terminated by "()") or property name (else)
	 * 3 : the advice class name (can be short or long)
	 * 4 : the name of the static method to call into the advice class
	 *
	 * @var array[] key is the short / long class name
	 */
	private $links = array();

	//------------------------------------------------------------------------------------------- add
	/**
	 * Add dynamic links to current list
	 *
	 * Each entry is an array which elements are each an Aop entry array :
	 * 0 : "after", "after_returning", "after_throwing", "around", "before"
	 * 1 : the pointcut class name (can be short or long)
	 * 2 : the pointcut method (if terminated by "()") or property name (else)
	 * 3 : the advice class name (can be short or long)
	 * 4 : the name of the static method to call into the advice class
	 *
	 * @param $links array[] key is the short / long class name
	 */
	public function add($links)
	{
		$this->links = arrayMergeRecursive($this->links, $links);
	}

	//--------------------------------------------------------------------------------------- current
	/**
	 * @param $set_current Aop_Dynamics
	 * @return Aop_Dynamics
	 */
	public static function current(Aop_Dynamics $set_current = null)
	{
		return self::pCurrent($set_current);
	}

	//------------------------------------------------------------------------------------- linkClass
	/**
	 * Register callback advices for joinpoints associated to the class name
	 *
	 * @param $class_name string class name
	 */
	private function linkClass($class_name)
	{
		if (isset($this->links[$class_name])) {
			foreach ($this->links[$class_name] as $link) {
				trigger_error("NOT DONE YED", E_USER_ERROR); // TODO implement Aop_Dynamics::linkClass()
				switch ($link[0]) {
					case Aop::AFTER:
						break;
					case Aop::AROUND:
						break;
					case Aop::BEFORE:
						break;
				}
			}
		}
	}

	//---------------------------------------------------------------------------------- linkClassAop
	/**
	 * Register callback advices for joinpoints associated to the $joinpoint->getReturnedValue() class name
	 *
	 * This is the joinpoint form of linkClass(), designed to be called at Autoloader::autoload()'s end
	 *
	 * @param $class_name string
	 * @param $result     string
	 */
	public function linkClassAop($class_name, $result)
	{
		if ($result) {
			$this->linkClass($class_name);
		}
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * Register Aop_Dynamics : for each new autoloaded class, jointpoints will be dynamically added
	 * using linkClass()
	 *
	 * @param $register Plugin_Register
	 */
	public function register(Plugin_Register $register)
	{
		$this->links = $register->getConfiguration();
		$dealer = $register->dealer;
		$dealer->afterMethodCall(
			array('SAF\Framework\Autoloader', "includeClass"),
			array($this, "linkClassAop")
		);
	}

}
