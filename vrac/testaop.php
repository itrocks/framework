<?php
ini_set("xdebug.scream", true);
echo 1;

/**
 * School case for AOP-PHP
 *
 * Requirements :
 * - mysql database "testaop"
 * - mysql user "testaop", password "testaop", with full access to database "testaop"
 * - a "testaop.names" table 
 * CREATE TABLE names (
 *  id bigint(18) NOT NULL AUTO_INCREMENT,
 *  name varchar(255) NOT NULL,
 *  city varchar(255) NOT NULL,
 *  PRIMARY KEY (id)
 * );
 * INSERT INTO names (id, name, city)
 * VALUES (1, 'Julien', 'Lyon'), (2, 'Gerald', 'Lyon'), (3, 'Baptiste', 'Ploermel'); 
 */

//############################################################################### THE MAIN SOFTWARE
/**
 * This is the main software
 *
 * It has :
 * - a main class that executes the main process
 * - a business object linked to data stored in database
 *
 * This sample software reads three names from the database, and displays the name records
 */

//====================================================================================== Main_Class
abstract class Main_Class
{

	//------------------------------------------------------------------------------------------ init
	/**
	 * Main process initialisation : do some general stuff here
	 * - connect to database
	 */
	private static function init()
	{
		mysql_connect("localhost", "testaop", "testaop");
		mysql_select_db("testaop");
	}

	//------------------------------------------------------------------------------------------ main
	/**
	 * The main process does not know anything about the possibly applied extensions
	 * It simply reads three Name objects and display them into the web browser
	 */
	public static function main()
	{
		self::init();
		echo Name::read(1) . "<br>\n";
		echo Name::read(2) . "<br>\n";
		echo Name::read(3) . "<br>\n";
	}

}

//============================================================================================ Name
/**
 * This is a business object
 * Data is stored into a mysql table
 */
class Name
{

	//------------------------------------------------------------------------------------------- $id
	/**
	 * @var integer
	 */
	public $id;

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @var string
	 */
	public $name;

	//------------------------------------------------------------------------------------------ read
	/**
	 * Name builder that read it from database
	 *
	 * @param integer $id
	 * @return Name
	 */
	public static function read($id)
	{
		$result = mysql_query("SELECT * FROM names WHERE id = $id");
		$record = mysql_fetch_object($result, "Name");
		return $record;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * How to display a name
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->id . ": " . $this->name;
	}

}

//######################################################################## A MYSQL LOGGER EXTENSION
/**
 * This extensions is intended to log mysql queries into a file
 * In this sample we prefer log them into the web browser, for a visual example
 */

//==================================================================================== Mysql_Logger
/**
 * A mysql logger class
 */
abstract class Mysql_Logger
{

	//------------------------------------------------------------------------------------------- log
	/**
	 * The query logger itself
	 * 
	 * @param string $query
	 */
	public static function log($query)
	{
		// imagine this is a write into a log file
		echo "<div>" . $query . "</div>\n";
	}

}

//========================================================================== Mysql_Logger_Extension
abstract class Mysql_Logger_Extension
{

	//-------------------------------------------------------------------------------------- register
	/**
	 * Registers the extension
	 */
	public static function register()
	{
		aop_add_before("mysql_query()", array("Mysql_Logger_Extension", "onMysqlQuery"));
		/*
		aop_add_before("mysql_query()", function(AopJoinpoint $joinpoint) {
			Mysql_Logger::log($joinpoint->getArguments()[0]);
		});
		*/
	}

	//---------------------------------------------------------------------------------- onMysqlQuery
	/**
	 * The query logger advice
	 *
	 * @param AopJoinpoint $joinpoint
	 */
	public static function onMysqlQuery(AopJoinpoint $joinpoint)
	{
		Mysql_Logger::log($joinpoint->getArguments()[0]);
	}

}

//######################################################### A BUSINESS OBJECT IMPROVEMENT EXTENSION
/**
 * Name_With_City_Extension
 *
 * This will enable the application to get city from the database in addition to names
 * In all the main application, Name objects build from database will be replaced by extended
 * Name_With_City objects
 */

//================================================================================== Name_With_City
/**
 * The business class Name_With_City is a Name with one more property : $city
 */
class Name_With_City extends Name
{

	//----------------------------------------------------------------------------------------- $city
	/**
	 * @var string
	 */
	public $city;

	//------------------------------------------------------------------------------------------ read
	/**
	 * Name_With_City builder that read it from database
	 *
	 * @param integer $id
	 * @return Name_With_City
	 */
	public static function read($id)
	{
		$result = mysql_query("SELECT * FROM names WHERE id = $id");
		$record = mysql_fetch_object($result, "Name_With_City");
		return $record;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * How to display a name : now with city
	 *
	 * @return string
	 */
	public function __toString()
	{
		return parent::__toString() . " (" . $this->city . ")";
	}

}

//======================================================================== Name_With_City_Extension
abstract class Name_With_City_Extension
{

	//-------------------------------------------------------------------------------------- register
	/**
	 * Registers the extension
	 */
	public static function register()
	{
		aop_add_around("Name->read()", array("Name_With_City_Extension", "onNameRead"));
		/*
		aop_add_around("Name->read()", function(AopJoinpoint $joinpoint) {
			return Name_With_City::read($joinpoint->getArguments()[0]);
		});
		*/
	}

	//------------------------------------------------------------------------------------ onNameRead
	/**
	 * This advice makes Name::read read a Name_With_City instead of a Name
	 *
	 * @param AopJoinpoint $joinpoint
	 * @return Name_With_City
	 */
	private static function onNameRead(AopJoinpoint $joinpoint)
	{
		return Name_With_City::read($joinpoint->getArguments()[0]);
	}

}

//#################################################################################################
/**
 * The process caller decides which extensions will be activated
 *
 * Here are some call samples (final program will only use one of them, of course)
 */

//---------------------------------------------------------------------------------------------- #1

echo "\n<h2>----- #1 Main Process without extension</h2>\n";
Main_Class::main();

/*
The main process, alone, displays :

1: Julien
2: Gerald
3: Baptiste
*/

//---------------------------------------------------------------------------------------------- #2

echo "\n<h2>----- #2 Main Process with extension Mysql_Logger</h2>\n";
Mysql_Logger_Extension::register();
Main_Class::main();

/*
With a mysql logger, we have :

SELECT * FROM names WHERE id = 1
1: Julien
SELECT * FROM names WHERE id = 2
2: Gerald
SELECT * FROM names WHERE id = 3
3: Baptiste

Cool !
*/

//---------------------------------------------------------------------------------------------- #3

echo "\n<h2>----- #3 Main Process with both extensions Mysql_Logger and Name_With_City</h2>\n";
Name_With_City_Extension::register();
Main_Class::main();

/*
Now with the Name_With_City extension AND the mysql logger, we should have :

SELECT * FROM names WHERE id = 1
1: Julien (Lyon)
SELECT * FROM names WHERE id = 2
2: Gerald (Lyon)
SELECT * FROM names WHERE id = 3
3: Baptiste (Ploermel)

But we only have that. Because of lock on recursivity the mysql logger don't work into the
Name_With_City extension context :

1: Julien (Lyon)
2: Gerald (Lyon)
3: Baptiste (Ploermel)

*/
