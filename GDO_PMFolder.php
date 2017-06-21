<?php
final class GDO_PMFolder extends GDO_Select
{
	public function __construct()
	{
		$this->name('folder');
		$this->label('folder');
	}
	
	public function renderCell()
	{
		return $this->render();
	}
	
	public function user(GWF_User $user)
	{
		$this->gdo($user);
		$this->emptyChoice('choose_folder_move');
		$this->choices($this->userChoices($user));
		return $this;
	}
	
	private function userChoices(GWF_User $user)
	{
		$choices = [];
		foreach (GWF_PMFolder::getFolders($user->getID()) as $folder)
		{
			$choices[$folder->getName()] = $folder->getName();
		}
		return $choices;
	}
}