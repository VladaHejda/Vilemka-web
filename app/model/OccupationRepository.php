<?php

namespace Vilemka;

use DateTime;
use Nette\Database\Context;

class OccupationRepository
{

	/** @var Context */
	protected $database;


	public function __construct(Context $database)
	{
		$this->database = $database;
	}


	public function getBookedPeriods($month, $year)
	{
		// todo
		$bookings = [
			'2014-07-19',
			'2014-08-02',
			'2014-08-30',
			'2014-10-11',
			'2014-10-18',
			'2015-01-03',
			'2015-03-21',
			'2015-04-25',
		];
		$interval = 6;

		$periods = [];
		foreach ($bookings as $booking) {
			$periods[] = new \DatePeriod(new DateTime($booking), new \DateInterval('P1D'), $interval);
		}

		return $periods;
	}


	public function isPeriodFree(DateTime $from, DateTime $to)
	{
		// todo
		return true;
	}


	public function insert(ValueObject\Order $order)
	{
		// todo
	}

}
