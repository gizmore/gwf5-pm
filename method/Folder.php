<?php
final class PM_Folder extends GWF_MethodQueryList
{
	public function isUserRequired() { return true; }
	
	public function gdoTable() { return GWF_PM::table(); }
	
	/**
	 * @var GWF_PMFolder
	 */
	private $folder;
	
	public function init()
	{
		$this->module->includeClass('GDO_PMFromTo');
		$this->module->includeClass('GDO_PMFolder');
		$this->folder = GWF_PMFolder::table()->find(Common::getRequestInt('folder', 1));
	}
	
	public function getFilters()
	{
		$table = GWF_PM::table();
		return array(
// 			GDO_RowNum::make(),
// 			GDO_Template::make()->module($this->module)->template('cell_pmunread.php'),
// 			GDO_PMFromTo::make('frmto'),
// 			$table->gdoColumn('pm_title'),
// 			GDO_Button::make('show'),
		);
	}
	
	public function gdoQuery()
	{
		$user = GWF_User::current();
		return GWF_PM::table()->select('*')->where('pm_owner='.$user->getID())->where('pm_folder='.$this->folder->getID())->where("pm_deleted_at IS NULL");
	}
	
	public function gdoDecorateList(GDO_List $list)
	{
		$list->rawlabel($this->folder->display('pmf_name'));
		$list->href(href('PM', 'Overview'));
// 		$list->actions()->addFields(array(
// 			GDO_Submit::make('delete')->label('btn_delete'),
// 			GDO_Submit::make('move')->label('btn_move'),
// 			GDO_PMFolder::make('folder')->user(GWF_User::current()),
// 		));
	}
}
