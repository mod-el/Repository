<?php namespace Model\Repository\Controllers;

use Model\Core\Controller;

class RepositoryController extends Controller
{
	function index()
	{
		if (!isset($_GET['act']))
			die('Unknown action.');

		switch ($_GET['act']) {
			case 'refresh-modules':
				$this->model->_Repository->refreshModules();
				die('Done');
				break;
			case 'pull-module':
				$payload = json_decode($_POST['payload'], true);
				if ($payload and isset($payload['repository']['name'])) {
					echo $this->model->_Repository->pullModule($payload['repository']['name']);
				} else {
					echo 'Decoding error';
				}
				$this->model->_Repository->refreshModules();
				die();
				break;
			case 'installer':
				header("Content-Transfer-Encoding: Binary");
				header("Content-disposition: attachment; filename=\"zkinstall.php\"");
				$config = $this->model->_Repository->retrieveConfig();
				echo file_get_contents(INCLUDE_PATH . $config['path'] . DIRECTORY_SEPARATOR . 'zkinstall.php');
				die();
				break;
			case 'get-modules':
				$this->checkKey();

				if (isset($_GET['modules'])) {
					$specific = explode(',', $_GET['modules']);
				} else {
					$specific = [];
				}

				$modules = $this->model->_Repository->getModules($specific);
				$this->model->sendJSON($modules, false);
				break;
			case 'get-files':
				$this->checkKey();

				if (isset($_GET['module'])) {
					$files = $this->model->_Repository->getModuleFiles($_GET['module']);
					$this->model->sendJSON($files, false);
				} else {
					die('Invalid data.');
				}
				break;
			case 'get-install-list':
				$this->checkKey();

				if (!isset($_GET['modules']))
					die('Invalid data. #1');

				$installModules = $_GET['modules'] ? explode(',', $_GET['modules']) : [];
				if (!in_array('Core', $installModules))
					$installModules[] = 'Core';
				$files = $this->model->_Repository->getInstallList($installModules);

				$this->model->sendJSON($files, false);
				break;
			case 'get-file':
				$this->checkKey();

				if (!isset($_GET['file']) or strpos($_GET['file'], '..') !== false) {
					header("HTTP/1.1 401 Unauthorized");
					die('Invalid data. #1');
				}

				$file = trim($_GET['file']);
				if ($file{0} == '/') {
					header("HTTP/1.1 401 Unauthorized");
					die('Invalid data. #2');
				}

				$files = $this->model->_Repository->getInstallList();

				if ((!isset($_GET['install']) or (!in_array($_GET['file'], $files['model']) and !in_array($_GET['file'], $files['config']))) and (!isset($_GET['module']) or strpos($_GET['module'], '/') !== false)) {
					header("HTTP/1.1 401 Unauthorized");
					die('Invalid data. #3');
				}

				$config = $this->model->_Repository->retrieveConfig();

				if (isset($_GET['install'])) {
					if (substr($file, 0, 6) === 'model' . DIRECTORY_SEPARATOR) {
						$full_path = INCLUDE_PATH . $config['path'] . DIRECTORY_SEPARATOR . substr($file, 6);
					} else {
						$full_path = INCLUDE_PATH . 'model' . DIRECTORY_SEPARATOR . 'Repository' . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . $file;
					}
				} else {
					$full_path = INCLUDE_PATH . $config['path'] . DIRECTORY_SEPARATOR . $_GET['module'] . DIRECTORY_SEPARATOR . $file;
				}

				if (!file_exists($full_path)) {
					header("HTTP/1.1 401 Unauthorized");
					die('File not found');
				}
				if (!is_file($full_path)) {
					header("HTTP/1.1 401 Unauthorized");
					die('Not a file');
				}

				readfile($full_path);
				die();
				break;
			default:
				die('Unknwon action');
				break;
		}
	}

	private function checkKey()
	{
		if (!isset($_GET['key']) or !$this->model->_Repository->checkKey($_GET['key'])) {
			header("HTTP/1.1 401 Unauthorized");
			die('Invalid key.');
		}
	}
}
