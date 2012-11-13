<?php
//---------------------------------------------------------------------------------- ADVICE RECURSE

class MyClass {
	public $property;
}

function defaultGetter(AopJoinpoint $joinpoint)
{
	$var = $joinpoint->getPropertyName();
	$object = $joinpoint->getObject();
	if (!isset($object->$var)) {
		$object->$var = "default value";
	}
	return $object->$var;
}

aop_add_around("read MyClass->property", "defaultGetter");

$myObject = new MyClass();
echo $myObject->property;

die();

function one() { echo "one "; }
function two() { echo "two "; }
function three() { echo "three "; }

function advice2() { one(); }
function advice3() { two(); }

aop_add_before("two()", "advice2");
aop_add_before("three()", "advice3");

three();

/*

class One
{

	public function aFunction()
	{
		echo "one ";
	}

}

class Two
{

	public function aFunction()
	{
		echo "two ";
	}

}

class Three
{

	public function aFunction()
	{
		echo "three";
	}

}

function advice2() { (new One())->aFunction(); }
function advice3() { (new Two())->aFunction(); }

aop_add_before("Two->aFunction()", "advice2");
aop_add_before("Three->aFunction()", "advice3");

(new Three())->aFunction();

/*
die();

function night() { echo "night "; }
function day() { echo "day "; }
function wake() { echo "wake "; }

aop_add_before("day()", "night");
aop_add_before("wake()", "day");
wake();

die();

class Night {
	public $name = "NIGHT ";
	private static function beforeEnd() { echo (new Night())->name; }
	public function end() { Night::beforeEnd(); echo "the night ends "; }
}

class Day {
	public $name = "DAY ";
	private static function beforeBegin() { echo (new Day())->name; }
	public function begin() { Day::beforeBegin(); echo "the sun rises "; }
}

class Man {
	public $name = "MAN ";
	private function beforeWakeup() { echo $this->name; }
	public function wakeup() { $this->beforeWakeup(); echo "the man wakes up "; }
}

aop_add_before("Day->beforeBegin()", array("Night", "end"));
aop_add_before("Man->beforeWakeup()", array("Day", "begin"));

$me = new Man();
$me->wakeup();
// devrait écrire : NIGHT the night ends DAY the sun rises MAN the man wakes up
// mais n'écrit que : DAY the sun rises MAN the man wakes up

function getNightName(AopJoinpoint $joinpoint)
{
	echo "tallest ";
	//$var = $joinpoint->getObject()->name;
	//echo "got $var";
}

function getDayName(AopJoinpoint $joinpoint)
{
	echo "up " . (new Night())->name;
	//$var = $joinpoint->getObject()->name;
	//echo "got $var";
}

function getManName(AopJoinpoint $joinpoint)
{
	echo "up " . (new Day())->name;
	// $property = $joinpoint->getPropertyName();
	// $var = $joinpoint->getObject()->name;
	//echo "got $var";
}

aop_add_before("Night->name", "getNightName");
aop_add_before("Day->name", "getDayName");
aop_add_before("Man->name", "getManName");

echo "<p>";

echo $me->name;

die();

//------------------------------------------------------------------------------------ FETCH_OBJECT
require "framework/classes/locale/Translation.php";

$mysqli = new mysqli("localhost", "root", "ft6y7u8i", "my_business");

$res = $mysqli->query("SELECT * FROM translations WHERE id = 1");
$object = $res->fetch_object("SAF\\Framework\\Translation");
$res->free();
echo "<pre>" . print_r($object, true) . "</pre>";

die();

abstract class Une
{

	public $prop;

	public static function setProp()
	{
		echo "set prop " . get_called_class() . "::prop<br>";
	}

}

echo "add after : ";
aop_add_after("Une->prop", array("Une", "setProp"));
echo "OK<br>";

$une = new Une();
$une->prop = 1;

die();

//------------------------------------------------------------------------------------------ STATIC
// static::$xxx la propriété pour la classe de plus haut niveau par rapport à la classe appelée
// Si la propriété n'est pas surchargée, la donnée est liée à la classe où elle est déclarée
// Si la propriété est surchargée, la donnée est liée à liée à la classe où a lieu la surcharge
// Si la propriété est déclarée dans un Trait, la donnée est liée à la classe qui utilise le trait

// self::$xxx la propriété pour la classe où a lieu l'appel à self
// Si l'appel à self a lieu dans un Trait, la donnée sera liée à la classe qui utilise le trait

// Classe::$xxx la propriété pour la classe où a lieu l'appel à self
// Si l'appel à self à lieu dans un Trait, la donnée sera liée au trait ! 
 
// trait et static

trait Current
{

	static $current = null;

	public static function current($set_current = null)
	{
		if ($set_current) {
			self::$current = $set_current;
		}
		return self::$current;
	}

}

class User
{
	use Current;

	static $current = null;
}

class Business_User extends User
{
	static $current = null;
}

class Main_Organisation
{
	use Current;

	static $current = null;
}

User::current("user");
Main_Organisation::current("main_organisation");

echo "user current() = " . User::current() . "<br>";
echo "business user current() = " . Business_User::current() . "<br>";
echo "main organisation current() = " . Main_Organisation::current() . "<br>";

echo "current current = " . Current::$current . "<br>";
echo "user current = " . User::$current. "<br>";
echo "business user current = " . Business_User::$current. "<br>";
echo "main organisation current = " . Main_Organisation::$current. "<br>";

die();



echo "<h3></h3>";

$b = null;

echo "isset(\$a) = " . isset($a) . "<br>";
echo "isset(\$b) = " . isset($b) . "<br>";

die();

// mes classes

class Doc
{
	public $doc= "doc";
	public function doc() {}
}

class Vente extends Doc
{
	public $ven = "ven";
	public function vendu() {}
}

class Com extends Doc
{
	public $com = "commande";
	public function com() {}
}

class Com_Vente extends Vente
{
	
}

// héritage multiple : Com_Vente doit également hériter de Com

$com = new ReflectionClass("Com");
foreach ($com->getMethods() as $method) {
	$name = $method->name;
	
}

// test

$class = new ReflectionClass("Com_Vente");
echo print_r($class->getMethods(), true) . "<br>";

die();

//-------------------------------------------------------------------------------------------------

echo "<H3>Test 0 : ajout d'advice dans un advice</H3>";

function function1()
{
	echo "function1 ";
}

function function2()
{
	echo "function2 ";
}

function advice1()
{
	echo "advice 1 ";
	echo "done ";
}

function advice2()
{
	echo "advice 2 ";
	echo "done ";
}

aop_add_before("function1()", "advice1");
function1();
function2();

die();

//-------------------------------------------------------------------------------------------------

echo "<H3>Test 1 : héritage</H3>";

class MyServices
{

	public function monTest($param)
	{
		echo "<pre>mon test " . print_r ($param, true) . "</pre>";
	}

}

class HeritedService extends MyServices
{
}

function aFaireAvantMonTest($joinpoint)
{
	echo "<pre>capturé !<br>" . print_r ($joinpoint, true) . "</pre>";
	$args = $joinpoint->getArguments();
	$args [0] = "très bidon";
	$joinpoint->setArguments($args);
	echo "method = " . $joinpoint->getMethodName() . "<br>";
}

function aFaireAvantHeritedTest()
{
	echo "AVANT HERITED TEST<br>";
}

aop_add_before ("MyServices->monTest()", "aFaireAvantMonTest");
aop_add_before ("HeritedService->monTest()", aFaireAvantHeritedTest);

$services = new HeritedService();
$services->monTest("bidon");

//-------------------------------------------------------------------------------------------------

echo "<H3>Test 2 : annule exécution d'origine</H3>";

class Une_Classe
{

	public function monTest()
	{
		echo "On exécute un truc<br>";
		return "chose";
	}

}

function interditMonTest($joinpoint)
{
	echo "INTERDICTION<br>";
	$joinpoint->setReturnedValue(null);
}

aop_add_around("Une_Classe->monTest()", "interditMonTest");

$un_objet = new Une_Classe();
echo $un_objet->monTest();

//-------------------------------------------------------------------------------------------------

echo "<H3>Test 3 : modifier objet renvoyé (ça on peut pas)</H3>";

class Une_Classe_Parente
{

	public function __construct()
	{
		echo "construction d'une classe parente<br>";
	}

	public function monTest()
	{
		echo "C'est le parent qui bosse<br>";
	}

}

class Une_Classe_Heritee extends Une_Classe_Parente
{

	public function __construct()
	{
		echo "construction d'une classe héritée<br>";
	}

	public function monTest()
	{
		echo "Finalement on teste autre chose, c'est l'héritée qui bosse<br>";
	}

}

function redirClasse($joinpoint)
{
	$joinpoint->process();
	$joinpoint->setReturnedValue(new Une_Classe_Heritee());
}

aop_add_around("Une_Classe_Parente->__construct()", "redirClasse");

$un_objet = new Une_Classe_Parente();
echo "classe = " . get_class($un_objet) . "<br>";
echo $un_objet->monTest();

//-------------------------------------------------------------------------------------------------

echo "<H3>Test 4 : getter implicite (souci = gérer double appel)</H3>";

class Un_Truc
{

	public $propriete = 5;

	public function getPropriete()
	{
		return $this->propriete * 2;
	}

}

function getterCall($joinpoint)
{
	echo "- un appel<br>";
	$object = $joinpoint->getObject();
	static $antiboucle = 1;
	if (!--$antiboucle) {
		$valeur = $object->propriete;
		$joinpoint->setReturnedValue($valeur * 2);
		echo "la valeur c'est $valeur mais on la double<br>";
		$antiboucle++;
		return $valeur;
	}
	//echo "value = " . $joinpoint->propriete . "<br>";
}

aop_add_after("read Un_Truc->propriete", getterCall);

$un_truc = new Un_Truc();
echo "propriete = $un_truc->propriete<br>";

//-------------------------------------------------------------------------------------------------

echo "<H3>Test 5 : via la réflexion</H3>";

class Un_Autre
{

	public $propriete = 18;

}

class Advice_Class
{

	public static function unAppel()
	{
		echo "ca marche<br>";
	}

}
aop_add_before("read Un_Autre->propriete", "Advice_Class::unAppel");

$un_autre = new Un_Autre();
$prop = new ReflectionProperty("Un_Autre", "propriete");
echo "valeur = " . $prop->getValue($un_autre);

//-------------------------------------------------------------------------------------------------

echo "<H3>Test 6 : manipulation de propriété</H3>";

class A_Class
{

	public $prop;

}

function advice($joinpoint)
{
	static $continue = true;
	if ($continue) {
		$continue = false;
		$name = $joinpoint->getPropertyName();
		$value = $joinpoint->getObject()->$name;
		echo "- read advice called for $name<br>";
		echo "pointcut is " . print_r($value, true) . "<br>";
		$continue = true;
	}
	else {
		echo "- stopped<br>";
	}
}

aop_add_before("read A_Class->prop", "advice");

$object = new A_Class();
$object->prop = 1;
echo $object->prop;

*/
