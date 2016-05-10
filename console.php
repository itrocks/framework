#!/usr/bin/php
<?php
error_reporting(E_ALL);

$tmp_dir = __DIR__ . '/tmp';

if (!is_dir($tmp_dir)) mkdir($tmp_dir, 0755, true);
exec('chmod ugo+rwx ' . $tmp_dir);

$_sfkgroup_flag = $tmp_dir . '/' . (str_replace('/', '_', substr($argv[1], 1)) ?: 'admin');
$output = [];

exec(
	"ps -aux | grep \"$argv[1] $argv[2] " . (empty($argv[3]) ?: $argv[3]) . "\" | grep -v grep",
	$outputs
);

$count = 0;
foreach ($outputs as $output) {
	if (strpos($output, "$argv[1] $argv[2] $argv[3]") && strpos($output, '/usr/bin/php')) {
		$count++;
	}
}

if ($count > 1) {
	echo "Opération deja en cours ($argv[1] $argv[2] $argv[3])\n";
	print_r($outputs);
}
else {
	touch($_sfkgroup_flag);
	unset($count);
	unset($output);
	unset($outputs);

	if (empty($_GET)) {
		//chdir('/home/bappli/www');
		$_GET = ['as_widget' => true];
		foreach ($argv as $k => $v) {
			if ($k > 1) {
				list($k, $v) = explode('=', $v, 2);
				$_GET[$k] = $v;
			}
		}
		$_SERVER['HTTPS'] = true;
		$_SERVER['PATH_INFO'] = $argv[1];
		$_SERVER['SCRIPT_NAME'] = __DIR__ . '/../../../../../sfkgroup.php';
		include __DIR__ . '/../../../../../sfkgroup.php';
	}

	@unlink($_sfkgroup_flag);
}
