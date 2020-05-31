<?php namespace Model\Repository\Migrations;

use Model\Db\Migration;

class Migration_20200531154200_CreateTables extends Migration
{
	public function exec()
	{
		$this->createTable('modules');
		$this->addColumn('modules', 'folder', ['null' => false]);
		$this->addColumn('modules', 'name', ['type' => 'varchar(100)', 'null' => false]);
		$this->addColumn('modules', 'current_version', ['type' => 'varchar(15)', 'null' => false]);

		$this->createTable('modules_versions');
		$this->addColumn('modules_versions', 'module', ['type' => 'int', 'null' => false]);
		$this->addColumn('modules_versions', 'version', ['type' => 'varchar(15)', 'null' => false]);
		$this->addColumn('modules_versions', 'md5', ['type' => 'char(32)', 'null' => false]);
		$this->addIndex('modules_versions', 'modules_versions_idx1', ['module']);
		$this->addForeignKey('modules_versions', 'modules_versions_ibfk_1', 'module', 'modules', 'id', ['on-delete' => 'CASCADE']);

		$this->createTable('users');
		$this->addColumn('users', 'username', ['null' => false]);
		$this->addColumn('users', 'password', ['type' => 'char(40)', 'null' => false]);
		$this->addColumn('users', 'key', ['null' => false]);
	}

	public function check(): bool
	{
		return $this->tableExists('modules');
	}
}
