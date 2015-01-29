<?php

namespace Vilemka\ValueObject;

use DateTime;

class Order extends \Nette\Object
{

	/** @var DateTime */
	private $from, $to;

	/** @var int */
	private $personsCount;

	/** @var string */
	private $name, $email, $phone, $notice;


	public function __construct(DateTime $from, DateTime $to, $name, $personsCount, $email, $phone, $notice)
	{
		$this->from = $from;
		$this->to = $to;
		$this->name = $name;
		$this->personsCount = $personsCount;
		$this->email = $email;
		$this->phone = $phone;
		$this->notice = $notice;
	}


	/**
	 * @return DateTime
	 */
	public function getFrom()
	{
		return $this->from;
	}


	/**
	 * @return DateTime
	 */
	public function getTo()
	{
		return $this->to;
	}


	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}


	/**
	 * @return int
	 */
	public function getPersonsCount()
	{
		return $this->personsCount;
	}


	/**
	 * @return string
	 */
	public function getEmail()
	{
		return $this->email;
	}


	/**
	 * @return string
	 */
	public function getPhone()
	{
		return $this->phone;
	}


	/**
	 * @return string
	 */
	public function getNotice()
	{
		return $this->notice;
	}

}
