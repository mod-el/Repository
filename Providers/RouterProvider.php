<?php namespace Model\Repository\Providers;

use Model\Router\AbstractRouterProvider;

class RouterProvider extends AbstractRouterProvider
{
	public static function getRoutes(): array
	{
		return [
			[
				'pattern' => '',
				'controller' => 'Repository',
			],
		];
	}
}
