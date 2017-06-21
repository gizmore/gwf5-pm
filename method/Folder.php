<?php
final class PM_Folder extends GWF_MethodQueryTable
{
	public function isUserRequired() { return true; }
	
	/**
	 * @var GWF_PMFolder
	 */
	private $folder;
	
	public function getGDO() { return GWF_PM::table(); }
	
	public function init()
	{
		$this->module->includeClass('GDO_PMFromTo');
		$this->module->includeClass('GDO_PMFolder');
		$this->folder = GWF_PMFolder::table()->find(Common::getRequestInt('folder', 1));
	}
	
	public function getHeaders()
	{
		$table = $this->getGDO();
		return array(
			GDO_RowNum::make(),
			GDO_Template::make()->module($this->module)->template('cell_pmunread.php'),
			GDO_PMFromTo::make('frmto'),
			$table->gdoColumn('pm_title'),
			GDO_Button::make('show'),
		);
	}
	
	public function getQuery()
	{
		$user = GWF_User::current();
		return GWF_PM::table()->query()->from('gwf_pm')->where('pm_owner='.$user->getID())->where('pm_folder='.$this->folder->getID())->where("pm_deleted_at IS NULL");
	}
	
	public function getResult()
	{
		return $this->filterQuery($this->getQueryPaginated())->select('*')->exec();
	}
	
	public function onDecorateTable(GWF_Table $gwfTable)
	{
		$gwfTable->title($this->folder->display('pmf_name'));
		$gwfTable->navbar()->addFields(array(
			GDO_Submit::make('delete')->label('btn_delete'),
			GDO_Submit::make('move')->label('btn_move'),
			GDO_PMFolder::make('folder')->user(GWF_User::current()),
		));
	}
}
