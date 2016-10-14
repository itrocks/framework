<?php
if ($argc < 3) {
	die('Use : php init.php vendor_name project_name' . "\n");
}

// project
$vendor_name       = str_replace('.', '_', $argv[1]);
$project_name      = str_replace('.', '_', $argv[2]);
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
$console_file          = $dir . '/saf/framework/console.php';
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

use SAF\Framework;

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
\$loc = [
	'database'    => '$database_name',
	'environment' => 'development',
	'login'       => '$user_name'
];

EOT
);

echo '- Create password file ' . $password_file . "\n";
file_put_contents($password_file, <<<EOT
<?php
\$pwd = [
	'$user_name' => '$project_password',
	'saf_demo' => ''
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
/saf/framework
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

use SAF\Framework;
use SAF\Framework\Configuration;
use SAF\Framework\Dao;
use SAF\Framework\Dao\Mysql\Link;
use SAF\Framework\Plugin\Priority;

global \$loc, \$pwd;
require __DIR__ . '/../../loc.php';
require __DIR__ . '/../../pwd.php';
require __DIR__ . '/../../saf/framework/config.php';

\$config['$configuration_name'] = [
	Configuration::APP         => Application::class,
	Configuration::ENVIRONMENT => \$loc['environment'],
	Configuration::EXTENDS_APP => 'SAF/Framework',

	Priority::NORMAL => [
		Dao::class => [
			Link::DATABASE => \$loc[Link::DATABASE],
			Link::LOGIN    => \$loc[Link::LOGIN],
			Link::PASSWORD => \$pwd[\$loc[Link::LOGIN]]
		]
	]
];

EOT
);

echo '- Create hello-world home template file ' . $hello_world_template . "\n";
file_put_contents($hello_world_template, <<<EOT
<!DOCTYPE html>
<html>
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
require __DIR__ . '/$vendor_name-$project_name/saf/framework/index.php';
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
	"authors": [{ "name": "$vendor_name",  "email": "your@email.com" }],
	"description": "Description of the $project_name project",
	"extra": {
		"installer-paths": { "{\$vendor}/{\$name}/": ["type:itrocks"] },
		"installer-types": ["itrocks"]
	},
	"name": "$vendor_name/$project_name",
	"repositories": [{ "type": "composer", "url": "https://packages.bappli.com" }],
	"require": {
		"saf/framework": "dev-master"
	}
}
EOT
);

echo '- download composer into ' . $composer_executable . "\n";
chdir($dir);
copy('https://getcomposer.org/installer', $composer_setup);
if (hash_file('SHA384', $composer_setup) === 'e115a8dc7871f15d853148a7fbac7da27d6c0030b848d9b3dc09e2a0388afed865e6a3d6b3c0fad45c48e2b5fc1196ae') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;
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
