<?php
trait GWF_PMMethod
{
	public function pmNavbar()
	{
		return Module_PM::instance()->templatePHP('navbar.php');
	}
}
