<?php
final class PM_Delete extends GWF_Method
{
	use GWF_PMMethod;
	
	public function execute()
	{
		if ( (!($pm = GWF_PM::getById(Common::getRequestString('pm')))) || 
				($pm->gdoHashcode() !== Common::getRequestString('token')) )
		{
			return $this->pmNavbar()->add($this->error('err_pm'))->add($this->execMethod('Overview'));
		}
		return $this->pmNavbar()->add($this->onDelete($pm))->add($this->execMethod('Overview'));
	}
	
	public function deletePM(GWF_PM $pm)
	{
		$pm->saveVar('pm_deleted_at', time());
		$pm->getOtherPM()->saveVar('pm_other_deleted', '1');
		return $this->message('msg_pm_deleted');
	}
}
