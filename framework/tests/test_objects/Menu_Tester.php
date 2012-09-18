<?php
namespace SAF\Framework\Tests;

use SAF\Framework\Aop;
use SAF\Framework\Menu_Block;
use SAF\Framework\Menu_Item;

abstract class Menu_Tester
{

	//---------------------------------------------------------------------------------- menuInitDemo
	/**
	 * Initialises a demo menu object
	 * 
	 * @param AopJoinPoint $joinpoint
	 */
	public static function menuInitDemo($joinpoint)
	{
		static $antiloop = false;
		if ($antiloop) return;
		$antiloop = true;
		$menu = $joinpoint->getObject();
		if ($menu->title) return;
		$menu->title = "Menu";
		$block = new Menu_Block(); $block->title = "Sample menu"; $block->color = "black";
		$item = new Menu_Item(); $item->caption = "Login"; $item->link = "/User/login";
		$block->items[] = $item;
		$item = new Menu_Item(); $item->caption = "Sales order Nr 1"; $item->link = "/Sales_Order/1";
		$block->items[] = $item;
		$menu->blocks[] = $block;
		$block = new Menu_Block(); $block->title = "Sales"; $block->color = "green";
		$item = new Menu_Item(); $item->caption = "Quotes"; $item->link = "/Sales_Quotes";
		$block->items[] = $item;
		$item = new Menu_Item(); $item->caption = "Orders"; $item->link = "/Sales_Orders";
		$block->items[] = $item;
		$item = new Menu_Item(); $item->caption = "Invoices"; $item->link = "/Sales_Invoices";
		$block->items[] = $item;
		$menu->blocks[] = $block;
		$antiloop = false;
	}

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		Aop::registerBefore("read SAF\\Framework\\Menu->blocks", __CLASS__ . "::menuInitDemo");
		Aop::registerBefore("read SAF\\Framework\\Menu->title",  __CLASS__ . "::menuInitDemo");
	}

}

Menu_Tester::register();
