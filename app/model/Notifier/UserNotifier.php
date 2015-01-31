<?php

namespace Vilemka;

use Nette\Http\Request;

abstract class UserNotifier extends Notifier
{

	/** @var string */
	protected $signature = '';


	/**
	 * @param array $signature
	 * @param Request $request
	 */
	public function setSignature(array $signature, Request $request)
	{
		$this->signature = str_replace(['<host>', '<sender>'],
			[$request->getUrl()->getHost(), $this->sender ? $this->sender->getEmail() : ''],
			implode("\n", $signature));
	}

}
