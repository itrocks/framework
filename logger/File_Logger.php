<?php
namespace SAF\Framework\Logger;

use SAF\Framework\Dao;
use SAF\Framework\Logger;
use SAF\Framework\Plugin\Configurable;
use SAF\Framework\Session;

/**
 * Features common to files loggers plugins
 */
class File_Logger implements Configurable
{

	//-------------------------------------------------------------------------------- FILE_EXTENSION
	const FILE_EXTENSION = 'log';

	//-------------------------------------------------------------------------------------------- GZ
	/**
	 * Override this with true if the file has to be opened using gzopen
	 */
	const GZ = false;

	//------------------------------------------------------------------------------------------ PATH
	const PATH = 'path';

	//----------------------------------------------------------------------------------------- $file
	/**
	 * @var resource
	 */
	private $file = null;

	//------------------------------------------------------------------------------------ $file_name
	/**
	 * @var string
	 */
	private $file_name = null;

	//----------------------------------------------------------------------------------------- $path
	/**
	 * @var string
	 */
	protected $path;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $configuration array [path]
	 */
	public function __construct($configuration = null)
	{
		if (isset($configuration) && isset($configuration[self::PATH])) {
			$this->path = $configuration[self::PATH];
		}
	}

	//------------------------------------------------------------------------------------------ file
	/**
	 * @return resource
	 */
	protected function file()
	{
		if (empty($this->file) && ($filename = $this->fileName())) {
			if (!file_exists($path = lLastParse($filename, SL))) {
				mkdir($path, 0777, true);
				// patch : mkdir's set mode does not work (debian 8)
				chmod($path, 0777);
			}
			$this->file = static::GZ ? gzopen($filename, 'wb9') : fopen($filename, 'wb');
		}
		return $this->file;
	}

	//-------------------------------------------------------------------------------------- fileName
	/**
	 * Caching only when there is no $identifier
	 *
	 * @param $entry Entry if set, forces the file name to match to an existing entry
	 * @return string
	 */
	protected function fileName(Entry $entry = null)
	{
		if ($entry) {
			$identifier = Dao::getObjectIdentifier($entry);
		}
		if (empty($this->file_name) || !empty($identifier)) {
			if (empty($identifier)) {
				/** @var $logger Logger */
				$logger = Session::current()->plugins->get(Logger::class);
				$identifier = $logger->getIdentifier();
			}
			if ($identifier) {
				$file_path = $this->path . SL
					. ($entry ? $entry->start->format('Y-m-d') : date('Y-m-d')) . SL
					. $identifier . DOT . static::FILE_EXTENSION . (static::GZ ? '.gz' : '');
				if (!isset($logger)) {
					return $file_path;
				}
				$this->file_name = $file_path;
			}
		}
		return $this->file_name;
	}

	//------------------------------------------------------------------------------- readFileContent
	/**
	 * Read file content for a given entry
	 *
	 * @param $entry Entry
	 * @return string
	 */
	public function readFileContent(Entry $entry)
	{
		$filename = $this->fileName($entry);
		// file may have been gzipped outside of the default static::GZ behaviour
		if (!static::GZ && !file_exists($filename) && file_exists($filename . '.gz')) {
			$filename .= '.gz';
		}
		// get file content
		return file_exists($filename)
			? (
				(substr($filename, -3) == '.gz')
					? join(LF, gzfile($filename))
					: file_get_contents($filename)
			)
			: ('no file ' . $filename)
		;
	}

}
