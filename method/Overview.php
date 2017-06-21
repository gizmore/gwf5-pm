<?php
/**
 * Main PM Functionality / Navigation
 * @author gizmore
 */
final class PM_Overview extends GWF_Method
{
	use GWF_PMMethod;
	
	public function isUserRequired() { return true; }
	
	public function execute()
	{
		if (isset($_REQUEST['delete']))
		{
			return $this->pmNavbar()->add($this->onDelete())->add($this->pmOverview());
		}
		elseif (isset($_REQUEST['move']))
		{
			return $this->pmNavbar()->add($this->onMove())->add($this->pmOverview());
		}
		return $this->pmNavbar()->add($this->pmOverview());
	}
	
	private function pmOverview()
	{
		$tVars = array(
			'folder' => $this->execMethod('Folder'),
			'folders' => $this->execMethod('Folders'),
		);
		return $this->templatePHP('overview.php', $tVars);
	}
	
	##############
	### Delete ###
	##############
	private function onDelete()
	{
		if ($ids = $this->getRBX())
		{
			$user = GWF_User::current();
			$now = GWF_Time::getDate();
			GWF_PM::table()->update()->set("pm_deleted_at='$now'")->where("pm_owner={$user->getID()} AND pm_id IN($ids)")->exec();
			$affected = GDODB::instance()->affectedRows();
			GWF_PM::updateOtherDeleted();
			return $this->message('msg_pm_deleted', [$affected]);
		}
	}
	
	private function onMove()
	{
		$user = GWF_User::current();
		if (!($folder = GWF_PMFolder::getByIdAndUser(Common::getFormString('folder'), $user)))
		{
			return $this->error('err_pm_folder');
		}
		if ($ids = $this->getRBX())
		{
			GWF_PM::table()->update()->set("pm_folder={$folder->getID()}")->where("pm_owner={$user->getID()} AND pm_id IN($ids)")->exec();
			$affected = GDODB::instance()->affectedRows();
			return $this->message('msg_pm_moved', [$affected, $folder->displayName()]);
		}
	}
}
