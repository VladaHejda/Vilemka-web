<?php

namespace Vilemka;

use Nette\Database\Context;
use Vilemka\ValueObject\EmailAddress;

class MessageRepository extends \Nette\Object
{

	/** @var Context */
	protected $database;


	public function __construct(Context $database)
	{
		$this->database = $database;
	}


	public function insert($message, $name, EmailAddress $email = null)
	{
		// todo
	}

}
