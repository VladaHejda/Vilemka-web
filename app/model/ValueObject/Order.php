<?php

namespace Vilemka\ValueObject;

use DatePeriod;

class Order
{

	/** @var DatePeriod */
	public $period;

	/** @var int */
	public $personsCount;

	/** @var string */
	public $name, $email, $phone, $notice;


	public function __construct(DatePeriod $period, $name, $personsCount, $email, $phone, $notice)
	{
		$this->period = $period;
		$this->name = $name;
		$this->personsCount = $personsCount;
		$this->email = $email;
		$this->phone = $phone;
		$this->notice = $notice;
	}

}
