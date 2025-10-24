<?php namespace Model\Repository;

use Model\Core\Module_Config;

class Config extends Module_Config
{
	/**
	 * @throws \Model\Core\Exception
	 */
	protected function assetsList(): void
	{
		$this->addAsset('config', 'config.php', function () {
			return "<?php\n\$config = ['path' => 'app" . DIRECTORY_SEPARATOR . "assets" . DIRECTORY_SEPARATOR . "repository'];\n";
		});
	}

	public function getConfigData(): ?array
	{
		return [];
	}
}
