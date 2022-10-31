<?php
namespace ITRocks\Framework\Dao\File\Cluster;

use Files_Cluster;
use Files_Cluster\Configuration;
use Files_Cluster\Configuration\Clusters;
use Files_Cluster\Configuration\Directories;
use ITRocks\Framework\AOP\Joinpoint\After_Method;
use ITRocks\Framework\Dao\File;
use ITRocks\Framework\Dao\Gaufrette;
use ITRocks\Framework\Plugin\Configurable;
use ITRocks\Framework\Plugin\Has_Get;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;

/**
 * A plugin to read files from a files cluster
 *
 * Needs bappli/files-cluster
 * TODO Perhaps should this be into files-cluster.
 * TODO Files_Cluster seems to be a bad link. Should be Bappli/Files_Cluster ?
 */
class Read implements Configurable, Registerable
{
	use Has_Get;

	//-------------------------------------------------------------------------------- $configuration
	/**
	 * The files cluster configuration
	 *
	 * @var ?Configuration
	 */
	public ?Configuration $configuration;

	//--------------------------------------------------------------------------- $configuration_file
	/**
	 * The file can contain the configuration of the files cluster
	 *
	 * @var string
	 */
	public string $configuration_file = '/etc/files-cluster/config.php';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $configuration Configuration|string|null can be a preset configuration, of a file name
	 */
	public function __construct(mixed $configuration = null)
	{
		if (isset($configuration)) {
			$this->configuration = $configuration;
		}
		elseif (is_string($configuration)) {
			$this->configuration_file = $configuration;
		}
		if (!$this->configuration) {
			$this->configuration = file_exists($this->configuration_file)
				? include($this->configuration_file)
				: new Configuration(new Clusters([]), new Directories([]));
		}
	}

	//------------------------------------------------------------------------- afterLinkReadProperty
	/**
	 * @param $joinpoint     After_Method
	 * @param $object        object
	 * @param $property_name string
	 * @return ?string The content read after the files cluster plugin did its work
	 * @see Link::readProperty
	 */
	public function afterLinkReadProperty(
		After_Method $joinpoint, object $object, string $property_name
	) : ?string
	{
		if (is_null($joinpoint->result)) {
			$link         = $joinpoint->object;
			$file_path    = $link->propertyFileName($object, $property_name);
			$cluster_read = new Files_Cluster\Read($this->configuration);
			if (!file_exists(lLastParse($file_path, SL))) {
				mkdir(lLastParse($file_path, SL), 0777, true);
			}
			return $cluster_read->getContent($file_path, false);
		}
		return $joinpoint->result;
	}

	//--------------------------------------------------------------------------------------- getFile
	/**
	 * Gets the file from clusters
	 *
	 * @param $file_path string
	 * @return boolean
	 */
	public function getFile(string $file_path) : bool
	{
		$cluster_read = new Files_Cluster\Read($this->configuration);
		if (!file_exists(lLastParse($file_path, SL))) {
			mkdir(lLastParse($file_path, SL), 0777, true);
		}
		return $cluster_read->get($file_path);
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		$aop = $register->aop;
		$aop->afterMethod([File\Link::class,      'readProperty'], [$this, 'afterLinkReadProperty']);
		$aop->afterMethod([Gaufrette\Link::class, 'readProperty'], [$this, 'afterLinkReadProperty']);
	}

}
