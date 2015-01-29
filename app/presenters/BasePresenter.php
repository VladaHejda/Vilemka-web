<?php

namespace Vilemka\Presenters;

use Nette\Utils\Validators;

abstract class BasePresenter extends \Nette\Application\UI\Presenter
{

	/** @var string */
	protected $debugEmail;


	public function setDebugEmail($email)
	{
		if (!Validators::isEmail($email)) {
			throw new \InvalidArgumentException(sprintf('"%s" is not valid e-mail.', $email));
		}
		$this->debugEmail = $email;
	}

}
