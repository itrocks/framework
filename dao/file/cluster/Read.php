<?php
namespace ITRocks\Framework\Dao\File\Cluster;

use Files_Cluster;
use Files_Cluster\Configuration;
use ITRocks\Framework\AOP\Joinpoint\After_Method;
use ITRocks\Framework\Dao\File\Link;
use ITRocks\Framework\Email;
use ITRocks\Framework\Plugin\Configurable;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;

/**
 * A plugin to read files from a files cluster
 *
 * Needs bappli/files-cluster
 */
class Read implements Configurable, Registerable
{

	//-------------------------------------------------------------------------------- $configuration
	/**
	 * The files cluster configuration
	 *
	 * @var Configuration
	 */
	public $configuration;

	//--------------------------------------------------------------------------- $configuration_file
	/**
	 * The file can contain the configuration of the files cluster
	 *
	 * @var string
	 */
	public $configuration_file = '/etc/files-cluster/config.php';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $configuration Configuration|string can be a preset configuration, of a file name
	 */
	public function __construct($configuration = null)
	{
		if (isset($configuration)) {
			$this->configuration = $configuration;
		}
		elseif (is_string($configuration)) {
			$this->configuration_file = $configuration;
		}
		if (!$this->configuration && file_exists($this->configuration_file)) {
			/** @noinspection PhpIncludeInspection dynamic include */
			$this->configuration = include($this->configuration_file);
		}
	}

	//------------------------------------------------------------------------- afterLinkReadProperty
	/**
	 * @param $joinpoint     After_Method
	 * @param $object        Email
	 * @param $property_name string
	 * @return string The content read after the files cluster plugin did its work
	 * @see Link::readProperty
	 */
	public function afterLinkReadProperty(After_Method $joinpoint, Email $object, $property_name)
	{
		if (is_null($joinpoint->result)) {
			$email        = $object;
			$link         = $joinpoint->object;
			$file_path    = $link->propertyFileName($email, $property_name);
			$cluster_read = new Files_Cluster\Read($this->configuration);
			$result       = $cluster_read->getContent($file_path);
			return $result;
		}
		return $joinpoint->result;
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * Registration code for the plugin
	 *
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		$aop = $register->aop;
		$aop->afterMethod([Link::class, 'readProperty'], [$this, 'afterLinkReadProperty']);
	}

}
