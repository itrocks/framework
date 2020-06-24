<?php
if ($argc < 3) {
	die('Use : php init.php vendor_name project_name' . "\n");
}

// project
$vendor_name        = ucwords(str_replace(['.', '-'], '_', $argv[1]), '_');
$project_name       = ucwords(str_replace(['.', '-'], '_', $argv[2]), '_');
$vendor_lower       = strtolower($vendor_name);
$project_lower      = strtolower($project_name);
$vendor_name_human  = str_replace('_', ' ', $vendor_name);
$project_name_human = str_replace('_', ' ', $project_name);
$dir                = getcwd() . '/' . $vendor_lower . '-' . $project_lower;
$project_directory  = $dir . '/' . $vendor_lower . '/' . $project_lower;
$project_password   = uniqid();

// database
$database_name = $vendor_lower . '-' . $project_lower;
$user_name     = substr($database_name, 0, 32);

// files
$application_file     = $project_directory . '/Application.php';
$composer_executable  = $dir . '/composer.phar';
$composer_file        = $dir . '/composer.json';
$composer_setup       = $dir . '/composer-setup.php';
$configuration_file   = $project_directory . '/config.php';
$console_file         = $dir . '/itrocks/framework/console';
$hello_world_template = $project_directory . '/Application_home.html';
$gitignore_file       = $dir . '/.gitignore';
$launcher_file        = substr($dir, 0, strrpos($dir, '/')) . '/' . $project_lower . '.php';
$local_file           = $dir . '/loc.php';
$password_file        = $dir . '/pwd.php';
$update_file          = $dir . '/update';

// directories
$cache_directory     = $dir . '/cache';
$temporary_directory = $dir . '/tmp';

// others
$namespace          = $vendor_name . "\\" . $project_name;
$configuration_name = $vendor_name . '/' . $project_name;

echo 'Initialization of your project ' . $namespace . '...' . "\n";

echo '- Create directory ' . $project_directory . "\n";
if (!is_dir($project_directory)) mkdir($project_directory, 0755, true);

echo '- Create application class file ' . $application_file . "\n";
file_put_contents($application_file, <<<EOT
<?php
namespace $namespace;

use ITRocks\Framework;

/**
 * The $project_name_human application
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
require __DIR__ . '/$vendor_lower-$project_lower/itrocks/framework/index.php';
EOT
);

echo '- create cache directory ' . $cache_directory . "\n";
if (!is_dir($cache_directory)) mkdir($cache_directory, 0700, true);

echo '- create temporary directory ' . $temporary_directory . "\n";
if (!is_dir($temporary_directory)) mkdir($temporary_directory, 0700, true);

echo '- create update file ' . $update_file . "\n";
touch($update_file);

echo '- create composer.json file ' . $composer_file . "\n";
file_put_contents($composer_file, <<<EOT
{
	"authors":     [{ "name": "$vendor_name_human", "email": "your@email.com" }],
	"description": "The $project_name_human project",
	"extra": {
		"installer-paths": {
			"{\$vendor}/":         ["type:itrocks-core"],
			"{\$vendor}/{\$name}/": ["type:itrocks"]
		},
		"installer-types": ["itrocks", "itrocks-core"]
	},
	"license":           "MIT",
	"minimum-stability": "dev",
	"name":              "$vendor_lower/$project_lower",
	"prefer-stable":     true,
	"repositories":      [{ "type": "composer", "url": "https://hub.itrocks.org" }],
	"require":           {
		"itrocks/framework": "dev-master",
		"php":               "^7.1"
	},
	"type": "itrocks-final"
}
EOT
);

echo '- get composer hash' . "\n";
$download_page = file_get_contents('https://getcomposer.org/download/');
$hash_begin    = "hash_file('sha384', 'composer-setup.php') === '";
$hash_end      = "'";
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

echo '- create database ' . $database_name . "\n";
file_put_contents($temporary_directory . '/init.sql', <<<EOT
CREATE DATABASE IF NOT EXISTS `$database_name`;
DROP USER IF EXISTS `$user_name`@localhost;
CREATE USER `$user_name`@localhost IDENTIFIED BY '$project_password';
GRANT ALL PRIVILEGES ON `$database_name`.* TO `$user_name`@localhost;
FLUSH PRIVILEGES;
USE `$database_name`;
CREATE TABLE IF NOT EXISTS `dependencies` (
  `id` bigint(18) unsigned NOT NULL AUTO_INCREMENT,
  `class_name` varchar(255) NOT NULL DEFAULT '',
  `declaration` enum('class','interface','trait','assigned','built-in','installable','property','') NOT NULL DEFAULT '',
  `dependency_name` varchar(255) NOT NULL DEFAULT '',
  `file_name` varchar(255) NOT NULL DEFAULT '',
  `line` bigint(18) unsigned NOT NULL DEFAULT '0',
  `type` enum('bridge_feature','class','compatibility','declaration','extends','feature','implements','namespace_use','new','param','return','set','static','store','use','var','') NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `class_name` (`class_name`),
  KEY `dependency_name` (`dependency_name`),
  KEY `file_name` (`file_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `feature_classes` (
  `id` bigint(18) unsigned NOT NULL AUTO_INCREMENT,
  `class_name` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `class_name` (`class_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `print_models` (
  `id` bigint(18) unsigned NOT NULL AUTO_INCREMENT,
  `class_name` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `id_document` bigint(18) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_document` (`id_document`),
  CONSTRAINT `print_models.id_document` FOREIGN KEY (`id_document`) REFERENCES `feature_classes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
EOT
);
system('sudo mysql -f <' . $temporary_directory . '/init.sql');
unlink($temporary_directory . '/init.sql');

echo '- initialize your application cache...' . "\n";
echo "php $console_file\n";
system('php ' . $console_file);

echo 'Your application ' . $vendor_name . '/' . $project_name . ' is initialized' . "\n";
