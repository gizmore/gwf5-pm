<?php
final class PM_Folders extends GWF_MethodTable
{
	public function isFiltered() { return false; }
	public function isPaginated() { return false; }
	public function isUserRequired() { return true; }
	
	public function getHeaders()
	{
		$table = GWF_PMFolder::table();
		return array(
				GDO_Template::make()->module(Module_PM::instance())->template('folder_link.php')->label('folder'),
				$table->gdoColumn('pmf_count'),
		);
	}
	
	public function getResult()
	{
		$folders = GWF_PMFolder::getFolders(GWF_User::current()->getID());
		return new GDOArrayResult($folders, GWF_PMFolder::table());
	}
}
