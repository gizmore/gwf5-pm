<?php
/**
 * Trashcan features restore, delete, and empty bin.
 * 
 * @author gizmore
 *
 */
final class PM_Trashcan extends GWF_MethodQueryTable
{
	use GWF_PMMethod;
	
	public function isUserRequired() { return true; }
	
	public function getGDO() { return GWF_PM::table(); }
	
	public function init()
	{
		$this->module->includeClass('GDO_PMFromTo');
	}
	
	public function execute()
	{
		if (isset($_REQUEST['delete']))
		{
			return $this->pmNavbar()->add($this->onDelete())->add(parent::execute());
		}
		elseif (isset($_REQUEST['restore']))
		{
			return $this->pmNavbar()->add($this->onRestore())->add(parent::execute());
		}
		elseif (isset($_REQUEST['trash']))
		{
			return $this->pmNavbar()->add($this->onEmpty())->add(parent::execute());
		}
		return $this->pmNavbar()->add(parent::execute());
	}
	
	public function getHeaders()
	{
		return array(
			GDO_RowNum::make(),
			GDO_PMFromTo::make(),
			GDO_Anchor::make('show'),
		);
	}
	
	public function getQuery()
	{
		$user = GWF_User::current();
		return GWF_PM::table()->query()->from('gwf_pm')->where('pm_owner='.$user->getID())->where("pm_deleted_at IS NOT NULL");
	}
	
	public function getResult()
	{
		return $this->filterQuery($this->getQueryPaginated())->select('*')->exec();
	}
	
	public function onDecorateTable(GDO_Table $table)
	{
		$table->rawlabel(t('name_trashcan'));
		$table->navbar()->addFields(array(
			GDO_Submit::make('restore')->label('btn_restore'),
			GDO_Submit::make('delete')->label('btn_delete'),
			GDO_Submit::make('trash')->label('btn_empty'),
		));
	}
	
	###############
	### Actions ###
	###############
	public function onDelete()
	{
		if ($ids = $this->getRBX())
		{
			$user = GWF_User::current();
			GWF_PM::table()->deleteWhere("pm_owner={$user->getID()} AND pm_id IN($ids)")->exec();
			$affected = GDODB::instance()->affectedRows();
			return $this->message('msg_pm_destroyed', [$affected]);
		}
	}
	
	public function onRestore()
	{
		if ($ids = $this->getRBX())
		{
			$user = GWF_User::current();
			GWF_PM::table()->update()->set("pm_deleted_at = NULL")->where("pm_owner={$user->getID()} AND pm_id IN($ids)")->exec();
			$affected = GDODB::instance()->affectedRows();
			GWF_PM::updateOtherDeleted();
			return $this->message('msg_pm_restored', [$affected]);
		}
	}
	
	public function onEmpty()
	{
		$user = GWF_User::current();
		GWF_PM::table()->deleteWhere("pm_owner={$user->getID()} AND pm_deleted_at IS NOT NULL")->exec();
		$affected = GDODB::instance()->affectedRows();
		return $this->message('msg_pm_destroyed', [$affected]);
	}
	
}
