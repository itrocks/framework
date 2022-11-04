<?php
namespace ITRocks\Framework\Tools\OS\Asynchronous_Task;

use ITRocks\Framework\Application;
use ITRocks\Framework\Tools\OS\Asynchronous_Task;

/**
 * OS asynchronous task scheduler
 *
 * - linux : crontab (user files stored into /var/spool/cron/crontabs or /var/spool/cron)
 */
class Linux_Cron
{

	//----------------------------------------------------------------------------------------- $task
	/**
	 * @var Asynchronous_Task
	 */
	public Asynchronous_Task $task;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $task Asynchronous_Task
	 */
	public function __construct(Asynchronous_Task $task)
	{
		$this->task = $task;
	}

	//------------------------------------------------------------------------------------------- add
	public function add()
	{
		$this->remove();
		$lines   = explode(LF, $this->readFromFile());
		$task    = $this->task;
		$lines[] = join(SP, [
			$task->minute,
			$task->hour,
			$task->day_of_month,
			$task->month,
			$task->day_of_week,
			$task->command
		]);
		sort($lines);
		$this->writeToFile($lines);
	}

	//---------------------------------------------------------------------------------- readFromFile
	/**
	 * @return string
	 */
	protected function readFromFile() : string
	{
		exec('crontab -l', $content);
		$content = join(LF, $content);
		return str_starts_with($content, 'no crontab') ? '' : str_replace(CR, '', $content);
	}

	//---------------------------------------------------------------------------------------- remove
	public function remove()
	{
		$buffer   = $this->readFromFile();
		$position = strpos($buffer, $this->task->command);
		if (!$position) {
			return;
		}
		$key   = substr_count(substr($buffer, 0, $position), LF);
		$lines = explode(LF, $buffer);
		unset($lines[$key]);
		$this->writeToFile($lines);
	}

	//----------------------------------------------------------------------------------- writeToFile
	/**
	 * @param $content string|string[]
	 */
	protected function writeToFile(array|string $content)
	{
		if (is_array($content)) {
			$content = join(LF, $content);
		}
		$file_name = Application::current()->getTemporaryFilesPath() . SL . 'crontab-' . uniqid();
		file_put_contents($file_name, trim($content) . LF);
		exec('crontab ' . $file_name);
		unlink($file_name);
	}

}
