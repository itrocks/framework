<?php
namespace ITRocks\Framework\User\Access_Control;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Uri;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\Reflection\Annotation\Template\Method_Annotation;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\Tools\Set;
use ITRocks\Framework\User\Access_Control;
use ITRocks\Framework\View;

/**
 * @feature Data user access control
 * @priority lowest
 */
class Data implements Registerable
{

	//------------------------------------------------------------------------------- checkDataAccess
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object Access_Control
	 * @param $result boolean
	 * @param $uri    string
	 * @param $get    array
	 * @param $post   array
	 * @param $files  array[]
	 */
	public function checkDataAccess(
		Access_Control $object, bool &$result, string &$uri, array &$get, array &$post, array &$files
	) : void
	{
		$access_control = $object;
		if (!$result) {
			return;
		}
		$uri_object = new Uri(lParse($uri, '?'));
		$class_name = Builder::className(Names::setToClass($uri_object->controller_name));
		// in some rare cases, controllers may exist without a real class (e.g. Mysql/maintain)
		if (!class_exists($class_name)) {
			return;
		}
		/** @noinspection PhpUnhandledExceptionInspection Must be a valid class name */
		$class = new Reflection_Class($class_name);
		/** @var $data_access_control Method_Annotation */
		$data_access_controls = $class->getAnnotations('data_access_control');
		foreach ($data_access_controls as $data_access_control) {
			$object = $uri_object->parameters->getMainObject();
			if ($object instanceof Set) {
				/** @noinspection PhpUnhandledExceptionInspection value element class name */
				$object = Builder::create($object->element_class_name);
			}
			$replacement_link = null;
			$result           = $data_access_control->call(
				$object, [$uri_object->feature_name, &$replacement_link]
			);
			if ($replacement_link) {
				$access_control->setUri($replacement_link, $uri, $get, $post, $files);
				break;
			}
			elseif (!$result) {
				$access_control->setUri(
					View::link(Access_Control::class, Feature::F_DENIED), $uri, $get, $post, $files
				);
				break;
			}
		}
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Register
	 */
	public function register(Register $register) : void
	{
		$aop = $register->aop;
		$aop->afterMethod([Access_Control::class, 'checkFeatures'], [$this, 'checkDataAccess']);
	}

}
