<?php namespace Model\Repository\Migrations;

use Model\Db\Migration;

class Migration_20200531155400_RenameUsersTable extends Migration
{
	public function exec()
	{
		$this->renameTable('users', 'repository_users');
	}

	public function check(): bool
	{
		return $this->tableExists('repository_users');
	}
}
