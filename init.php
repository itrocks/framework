<?php
if ($argc < 3) {
	die('Use : php init.php vendor_name project_name' . "\n");
}

// project
$vendor_name       = str_replace(['.', '-'], '_', $argv[1]);
$project_name      = str_replace(['.', '-'], '_', $argv[2]);
$dir               = getcwd() . '/' . strtolower($vendor_name . '-' . $project_name);
$project_directory = $dir . '/' . strtolower($vendor_name . '/' . $project_name);
$project_password  = uniqid();

// database
$database_name = strtolower($vendor_name . '_' . $project_name);
$user_name     = substr($database_name, 0, 16);

// files
$application_file      = $project_directory . '/Application.php';
$composer_executable   = $dir . '/composer.phar';
$composer_file         = $dir . '/composer.json';
$composer_setup        = $dir . '/composer-setup.php';
$configuration_file    = $project_directory . '/config.php';
$console_file          = $dir . '/itrocks/framework/console';
$hello_world_template  = $project_directory . '/Application_home.html';
$gitignore_file        = $dir . '/.gitignore';
$launcher_file         = substr($dir, 0, strrpos($dir, '/')) . '/' . strtolower($project_name) . '.php';
$local_file            = $dir . '/loc.php';
$password_file         = $dir . '/pwd.php';
$update_file           = $dir . '/update';

// directories
$cache_directory       = $dir . '/cache';
$temporary_directory   = $dir . '/tmp';

// others
$namespace          = ucfirst($vendor_name) . "\\" . ucfirst($project_name);
$configuration_name = ucfirst($vendor_name) . '/' . ucfirst($project_name);

echo 'Initialization of your project ' . $namespace . '...' . "\n";

echo '- Create directory ' . $project_directory . "\n";
if (!is_dir($project_directory)) mkdir($project_directory, 0755, true);

echo '- Create application class file ' . $application_file . "\n";
file_put_contents($application_file, <<<EOT
<?php
namespace $namespace;

use ITRocks\Framework;

/**
 * The $project_name application
 */
class Application extends Framework\Application
{

}

EOT
);

echo '- Create local configuration file ' . $local_file . "\n";
file_put_contents($local_file, <<<EOT
<?php
use ITRocks\Framework\Configuration;
use ITRocks\Framework\Configuration\Environment;
use ITRocks\Framework\Dao\Mysql\Link;

\$loc = [
	Configuration::ENVIRONMENT => Environment::DEVELOPMENT,
	Link::class => [
		Link::DATABASE => '$database_name',
		Link::LOGIN    => '$user_name'
	]
];

EOT
);

echo '- Create password file ' . $password_file . "\n";
file_put_contents($password_file, <<<EOT
<?php
use ITRocks\Framework\Dao\Mysql\Link;

\$pwd = [
	Link::class => '$project_password'
];

EOT
);

echo '- Create .gitignore file ' . $gitignore_file . "\n";
file_put_contents($gitignore_file, <<<EOT
/.buildpath
/.git
/.idea
/.project
/.settings

/cache
/itrocks/framework
/tmp
/vendor

/composer.lock
/loc.php
/pwd.php
/update

EOT
);


echo '- Create application configuration file ' . $configuration_file . "\n";
file_put_contents($configuration_file, <<<EOT
<?php
namespace $namespace;

use ITRocks\Framework\Configuration;

global \$loc;
require __DIR__ . '/../../loc.php';
require __DIR__ . '/../../itrocks/framework/config.php';

\$config['$configuration_name'] = [
	Configuration::APP         => Application::class,
	Configuration::ENVIRONMENT => \$loc[Configuration::ENVIRONMENT],
	Configuration::EXTENDS_APP => 'ITRocks/Framework',
];

EOT
);

echo '- Create hello-world home template file ' . $hello_world_template . "\n";
file_put_contents($hello_world_template, <<<EOT
<!DOCTYPE html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Hello, world !</title>
</head>
<body>
<!--BEGIN-->

	Hello, world !

<!--END-->
</body>
</html>

EOT
);

echo '- Create launcher script ' . $launcher_file . "\n";
file_put_contents($launcher_file, <<<EOT
<?php
require __DIR__ . '/$vendor_name-$project_name/itrocks/framework/index.php';
EOT
);

echo '- create cache directory ' . $cache_directory . "\n";
if (!is_dir($cache_directory)) mkdir($cache_directory, 0777, true);
exec('chmod ugo+rwx ' . $cache_directory);

echo '- create temporary directory ' . $temporary_directory . "\n";
if (!is_dir($temporary_directory)) mkdir($temporary_directory, 0777, true);
exec('chmod ugo+rwx ' . $temporary_directory);

echo '- create update file ' . $update_file . "\n";
touch($update_file);
exec('chmod ugo+rwx ' . $update_file);
exec('chmod ugo+rwx ' . $dir);

echo '- create composer.json file ' . $composer_file . "\n";
file_put_contents($composer_file, <<<EOT
{
	"authors":     [{ "name": "$vendor_name",  "email": "your@email.com" }],
	"description": "Description of the $project_name project",
	"extra": {
		"installer-paths": { "{\$vendor}/{\$name}/": ["type:itrocks"] },
		"installer-types": ["itrocks"]
	},
	"license":           "MIT",
	"minimum-stability": "dev",
	"name":              "$vendor_name/$project_name",
	"prefer-stable":     true,
	"repositories":      [{ "type": "composer", "url": "https://hub.itrocks.org" }],
	"require":           { "itrocks/framework": "dev-master" },
	"type": "itrocks-final"
}
EOT
);

echo '- get composer hash' . "\n";
$download_page = file_get_contents('https://getcomposer.org/download/');
$hash_begin = "hash_file('sha384', 'composer-setup.php') === '";
$hash_end = "'";
$hash_position = strpos($download_page, $hash_begin) + strlen($hash_begin);
$hash = substr(
	$download_page, $hash_position, strpos($download_page, $hash_end, $hash_position) - $hash_position
);
echo $hash . "\n";

echo '- download composer into ' . $composer_executable . "\n";
chdir($dir);
copy('https://getcomposer.org/installer', $composer_setup);
if (hash_file('sha384', $composer_setup) === $hash) { echo 'Installer verified'; }
else { echo 'Installer corrupt'; unlink('composer-setup.php'); }
echo PHP_EOL;

system('php ' . $composer_setup);
unlink($composer_setup);

echo '- install composer dependencies' . "\n";
system('php ' . $composer_executable . ' install');

echo '- create database ' . $database_name . " - NEED YOUR DATABASE ROOT PASSWORD\n";
file_put_contents($temporary_directory . '/init.sql', <<<EOT
CREATE DATABASE IF NOT EXISTS $database_name;
DELETE FROM mysql.user WHERE user = '$user_name';
DELETE FROM mysql.db WHERE user = '$user_name';
INSERT INTO mysql.user (host, user, authentication_string)
VALUES ('localhost', '$user_name', PASSWORD('$project_password'));
INSERT INTO mysql.db (host, user, db, select_priv, insert_priv, update_priv, delete_priv, create_priv, drop_priv, references_priv, index_priv, alter_priv, create_tmp_table_priv, lock_tables_priv)
VALUES ('localhost', '$user_name', '$database_name', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y');
FLUSH PRIVILEGES;
EOT
);
system('mysql -uroot -p <' . $temporary_directory . '/init.sql');
unlink($temporary_directory . '/init.sql');

echo '- initialise your application cache...' . " - NEED YOUR SYSTEM ROOT PASSWORD\n";
echo "sudo -uwww-data php $console_file\n";
system('sudo -uwww-data php ' . $console_file);

echo 'Your application ' . $vendor_name . '/' . $project_name . ' is initialized' . "\n";
