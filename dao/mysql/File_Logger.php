<?php
namespace SAF\Framework\Dao\Mysql;

use mysqli_result;
use SAF\Framework;
use SAF\Framework\Plugin\Configurable;
use SAF\Framework\Plugin\Register;
use SAF\Framework\Plugin\Registerable;
use SAF\Framework\Session;
use SAF\Framework\Sql\Builder;
use SAF\Framework\Tools\Contextual_Mysqli;
use Serializable;

/**
 * A logger for mysql queries : logs queries in files, and give information about their results
 */
class File_Logger implements Configurable, Registerable, Serializable
{

	//------------------------------------------------------------------------------------------ PATH
	const PATH = 'path';

	//---------------------------------------------------------------------------------------- REPLAC
	/**
	 * A short 6-characters long version of Builder::REPLACE
	 */
	const REPLAC = 'REPLAC';

	//--------------------------------------------------------------------------------------- $buffer
	/**
	 * @var string
	 */
	public $buffer = '';

	//------------------------------------------------------------------------------------- $database
	/**
	 * @var string
	 */
	private $database = '';

	//----------------------------------------------------------------------------------------- $path
	/**
	 * @var string
	 */
	private $path;

	//----------------------------------------------------------------------------------------- $time
	/**
	 * @var float
	 */
	private $time;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $configuration array [path]
	 */
	public function __construct($configuration = null)
	{
		if (isset($configuration[self::PATH])) {
			$this->path = $configuration[self::PATH];
		}
	}

	//------------------------------------------------------------------------------------ afterQuery
	/**
	 * Called each time after a mysql_query() call is done : log the query and its result
	 *
	 * @param $object Contextual_Mysqli
	 * @param $query string
	 * @param $result mysqli_result|boolean
	 */
	public function afterQuery(Contextual_Mysqli $object, $query, $result)
	{
		$log = '#' . SP . rtrim(join(SP, $this->timeDuration())) . LF;
		if ($object->database !== $this->database) {
			$log .= 'USE ' . BQ . $object->database . BQ . ';' . LF;
			$this->database = $object->database;
		}
		$log .= $query . ';' . LF;
		$log .= ($object->last_error || $object->last_errno)
			? '# ERROR ' . $object->errno . ': ' . $object->error . LF
			: $this->queryResult($object, $query, $result);
		$this->writeBuffer($log);
	}

	//----------------------------------------------------------------------------------- beforeQuery
	/**
	 * Called before each query, to know its duration
	 */
	public function beforeQuery()
	{
		$this->time = microtime(true);
	}

	//------------------------------------------------------------------------------------------ file
	/**
	 * @return resource
	 */
	private function file()
	{
		static $file = null;
		if (empty($file) && ($filename = $this->fileName())) {
			if (!file_exists($path = lLastParse($filename, SL))) {
				mkdir($path, 0777, true);
			}
			$file = fopen($filename, 'w9');
		}
		return $file;
	}

	//-------------------------------------------------------------------------------------- fileName
	/**
	 * @return string
	 */
	private function fileName()
	{
		static $file_name = null;
		if (empty($file_name)) {
			/** @var $logger Framework\Logger */
			$logger = Session::current()->plugins->get(Framework\Logger::class);
			if ($identifier = $logger->getIdentifier()) {
				$path = $this->path . SL . date('Y-m-d');
				$file_name = $path . SL . $identifier . '.sql';
			}
		}
		return $file_name;
	}

	//----------------------------------------------------------------------------------- queryResult
	/**
	 * @param $mysqli Contextual_Mysqli
	 * @param $query  string
	 * @param $result mysqli_result|boolean
	 * @return string
	 */
	private function queryResult(Contextual_Mysqli $mysqli, $query, $result)
	{
		switch (substr($query, 0, 6)) {
			case Builder::DELETE:
			case self::REPLAC:
			case Builder::UPDATE:
				return '#> ' . $mysqli->affected_rows . LF;
			case Builder::INSERT:
				return (($mysqli->affected_rows == 1) && $mysqli->insert_id)
					? '#+ ' . $mysqli->insert_id . LF
					: '#> ' . $mysqli->affected_rows . LF;
			case Builder::SELECT:
				return '#> ' . $result->num_rows . LF;
		}
		return '';
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		$aop = $register->aop;
		$aop->afterMethod ([Contextual_Mysqli::class, 'query'], [$this, 'afterQuery']);
		$aop->beforeMethod([Contextual_Mysqli::class, 'query'], [$this, 'beforeQuery']);
	}

	//------------------------------------------------------------------------------------- serialize
	/**
	 * When the script ends : gzip the log file
	 * This will be called after the output is flushed to the user, so it does not slow him
	 *
	 * @return string
	 */
	public function serialize()
	{
		if ($f = $this->file()) {
			fclose($f);
			$filename = $this->fileName();
			$d = gzopen($filename . '.gz', 'w9');
			$s = fopen($filename, 'rb');
			while (!feof($s)) {
				gzwrite($d, fgets($s));
			}
			fclose($s);
			fclose($d);
			unlink($filename);
		}
		return $this->path;
	}

	//---------------------------------------------------------------------------------- timeDuration
	/**
	 * Get actual time and duration since the last call of beforeQuery()
	 *
	 * $time is the actual time with milliseconds : HH:ii:ss.uuu
	 * $duration is the duration since $this->time microtime with a precision of 3 decimals,
	 * or an empty string if 0.000
	 *
	 * @return array [$time, $duration]
	 */
	private function timeDuration()
	{
		$microtime = microtime(true);
		// the time when the query ends
		list($time, $ms) = strpos($microtime, DOT) ? explode(DOT, $microtime) : [$microtime, 0];
		$now = date('H:i:s', $time) . DOT . str_pad(substr($ms, 0, 3), 3, '0');
		// the query duration
		$duration = $microtime - $this->time;
		$duration = strpos($duration, 'E-')
			? 0
			: ((substr($duration, 0, 2) == '0.') ? substr($duration, 1, 4) : substr($duration, 0, 5));
		if (!($duration + 0)) {
			$duration = '';
		}
		return [$now, $duration];
	}

	//----------------------------------------------------------------------------------- unserialize
	/**
	 * @param $serialized string
	 */
	public function unserialize($serialized)
	{
		$this->path = $serialized;
	}

	//----------------------------------------------------------------------------------- writeBuffer
	/**
	 * Write log buffer into the buffer or file
	 *
	 * @param $log string
	 * @return boolean true if written directly into file, false if it made $this->buffer grow
	 */
	private function writeBuffer($log)
	{
		if ($f = $this->file()) {
			if ($this->buffer) {
				fputs($f, '#' . lParse(rLastParse($this->fileName(), SL), DOT) . LF);
				fputs($f, $this->buffer);
				$this->buffer = '';
			}
			fputs($f, $log);
			return true;
		}
		// if file name is not known, log into buffer
		$this->buffer .= $log;
		return false;
	}

}
