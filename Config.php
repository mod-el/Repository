<?php namespace Model\Repository;

use Model\Core\Module_Config;

class Config extends Module_Config
{
	/**
	 * @throws \Model\Core\Exception
	 */
	protected function assetsList()
	{
		$this->addAsset('config', 'config.php', function () {
			return "<?php\n\$config = ['path'=>'app" . DIRECTORY_SEPARATOR . "assets" . DIRECTORY_SEPARATOR . "repository'];\n";
		});
	}

	/**
	 * @param array $data
	 * @return bool
	 */
	public function init(?array $data = null): bool
	{
		$this->model->_Db->query('CREATE TABLE `modules` IF NOT EXISTS (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `folder` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `current_version` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');

		$this->model->_Db->query('CREATE TABLE `modules_versions` IF NOT EXISTS (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module` int(11) NOT NULL,
  `version` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `md5` char(32) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `module` (`module`),
  CONSTRAINT `modules_versions_ibfk_1` FOREIGN KEY (`module`) REFERENCES `modules` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');

		$this->model->_Db->query('CREATE TABLE `users` IF NOT EXISTS (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `password` char(40) COLLATE utf8_unicode_ci NOT NULL,
  `key` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');

		return true;
	}

	/**
	 * Rule for the repository
	 *
	 * @return array
	 */
	public function getRules(): array
	{
		return [
			'rules' => [
				'',
			],
			'controllers' => [
				'Repository',
			],
		];
	}
}
