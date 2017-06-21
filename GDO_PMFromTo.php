<?php
final class GDO_PMFromTo extends GDO_Blank
{
	public function renderCell()
	{
		return Module_PM::instance()->templatePHP('cell_pmfromto.php', ['field'=>$this, 'pm'=>$this->gdo]);
	}
	
	public function renderFilter()
	{
		return Module_PM::instance()->templatePHP('filter_pmfromto.php', ['field'=>$this, 'pm'=>$this->gdo]);
	}
}
