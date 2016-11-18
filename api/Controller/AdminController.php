<?php
namespace Controller;

class AdminController
{
	public function new($request)
	{
		return [
			'ResponseCode' => 200,
			'data' => $request
		];
	}
}