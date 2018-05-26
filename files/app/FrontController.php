<?php

class FrontController extends \Model\Core\Core
{
	function init()
	{
		$this->viewOptions['cache'] = false;
	}
}
