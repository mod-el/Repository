<?php namespace Model\Repository;

use Model\Core\Module;
use Model\Core\Updater;

class Repository extends Module
{
	/** @var Updater */
	private $updater = null;
	/** @var array */
	private $installFiles = [
		'model' => [
			'.htaccess',
			'index.php',
			'app-data/img/uploads/',
			'app/assets/img/',
			'app/modules/Home/Controllers/HomeController.php',
			'app/modules/Home/templates/layoutHeader.php',
			'app/modules/Home/templates/home.php',
			'app/modules/Home/templates/err404.php',
			'app/modules/Home/templates/layoutFooter.php',
			'app/FrontController.php',
			'app/func-extra.php',
		],
		'config' => [
			'app/config/Core/config.php',
			'app/config/config.php',
		]
	];

	/**
	 * @return Updater
	 */
	private function getUpdater(): Updater
	{
		if (!$this->updater)
			$this->updater = new Updater($this->model);
		return $this->updater;
	}

	/**
	 *
	 */
	public function refreshModules()
	{
		$config = $this->retrieveConfig();

		$modules = $this->getUpdater()->getModules(false, $config['path']);

		foreach ($modules as $m) {
			$module_id = $this->model->_Db->select('modules', ['folder' => $m->folder_name], 'id');
			if ($module_id === false)
				$module_id = $this->model->_Db->insert('modules', ['folder' => $m->folder_name, 'name' => $m->name, 'current_version' => $m->version]);
			else
				$this->model->_Db->update('modules', $module_id, ['current_version' => $m->version]);

			$version_id = $this->model->_Db->select('modules_versions', ['module' => $module_id, 'version' => $m->version], 'id');
			if ($version_id) {
				$this->model->_Db->update('modules_versions', $version_id, ['md5' => $m->md5]);
			} else {
				$this->model->_Db->insert('modules_versions', ['module' => $module_id, 'version' => $m->version, 'md5' => $m->md5]);
			}
		}
	}

	/**
	 * @param string $name
	 * @return string
	 */
	public function pullModule(string $name): string
	{
		$config = $this->retrieveConfig();

		if (is_dir(INCLUDE_PATH . $config['path'] . DIRECTORY_SEPARATOR . ucfirst($name)))
			return nl2br(shell_exec("git -C " . INCLUDE_PATH . $config['path'] . DIRECTORY_SEPARATOR . ucfirst($name) . " pull 2>&1"));
		else
			return 'Non existing module';
	}

	/**
	 * @param string $key
	 * @return bool
	 */
	public function checkKey(string $key): bool
	{
		$check = $this->model->_Db->count('repository_users', ['key' => $key]);
		return (bool)($check > 0);
	}

	/**
	 * @param array $filter
	 * @return array
	 */
	public function getModules(array $filter = []): array
	{
		$config = $this->retrieveConfig();

		$modules = $this->getUpdater()->getModules(false, $config['path']);

		if (count($filter) === 0)
			$filter = array_keys($modules);

		$return = [];
		foreach ($filter as $m) {
			$m = explode('|', $m);

			$mod = $this->model->_Db->select('modules', ['folder' => $m[0]]);
			if (!$mod or !isset($modules[$mod['folder']]))
				continue;

			$module = $modules[$mod['folder']];

			$current_version = $this->model->_Db->select('modules_versions', ['module' => $mod['id'], 'version' => $mod['current_version']]);
			if ($mod and $current_version) {
				$old_md5 = false;
				if (isset($m[1])) {
					$old_version = $this->model->_Db->select('modules_versions', ['module' => $mod['id'], 'version' => $m[1]]);
					if ($old_version)
						$old_md5 = $old_version['md5'];
				}

				$return[$m[0]] = [
					'name' => $mod['name'],
					'description' => $module->description,
					'dependencies' => $module->dependencies,
					'current_version' => $mod['current_version'],
					'md5' => $current_version['md5'],
					'old_md5' => $old_md5,
				];
			} else {
				$return[$m[0]] = false;
			}
		}

		return $return;
	}

	/**
	 * @param string $module
	 * @return array
	 */
	public function getModuleFiles(string $module): array
	{
		$config = $this->retrieveConfig();

		$modules = $this->getUpdater()->getModules(false, $config['path']);

		$files = [];

		if (isset($modules[$module])) {
			foreach ($modules[$module]->files as $f) {
				$files[] = [
					'path' => $f['path'],
					'md5' => md5(file_get_contents(INCLUDE_PATH . $config['path'] . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . $f['path'])),
				];
			}
		}

		return $files;
	}

	/**
	 * @param array|null $filter
	 * @return array
	 */
	public function getInstallList(array $filter = null): array
	{
		$config = $this->retrieveConfig();

		$modules = $this->getUpdater()->getModules(false, $config['path']);

		$files = $this->installFiles;
		foreach ($modules as $m) {
			if ($filter !== null and !in_array($m->folder_name, $filter))
				continue;
			foreach ($m->files as $f)
				$files['model'][] = 'model' . DIRECTORY_SEPARATOR . $m->folder_name . DIRECTORY_SEPARATOR . $f['path'];
		}

		return $files;
	}

	/**
	 * Repository controller
	 *
	 * @param array $request
	 * @param string $rule
	 * @return array
	 */
	public function getController(array $request, string $rule): ?array
	{
		return [
			'controller' => 'Repository',
		];
	}
}
