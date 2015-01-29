<?php

namespace Vilemka\ValueObject;

use Nette\Http\Request;
use Nette\Utils\Validators;

class EmailAddress extends \Nette\Object
{

	/** @var string */
	private $email, $name;

	public function __construct(Request $request, $email, $name = null)
	{
		$email = str_replace('<host>', $request->getUrl()->getHost(), $email);
		if (!Validators::isEmail($email)) {
			throw new \InvalidArgumentException(sprintf('%s is not valid e-mail.', $email));
		}

		$this->email = $email;
		$this->name = $name;
	}


	public function getEmail()
	{
		return $this->email;
	}


	public function getName()
	{
		return $this->name;
	}


	public function getComplete()
	{
		return [$this->email, $this->name];
	}

}
