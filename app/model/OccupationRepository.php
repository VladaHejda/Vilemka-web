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
		$occupiedDays = $this->database->fetchAll('SELECT day FROM occupancy WHERE day LIKE ?',
			sprintf('%d-%s%%', $year, str_pad((string) $month, 2, '0', STR_PAD_LEFT)));

		$periods = [];
		$oneDayInterval = new \DateInterval('P1D');
		$firstDay = null; // unnecessary, but IDE

		foreach ($occupiedDays as $occupiedDay) {
			/** @var \Nette\Utils\DateTime $occupiedDay */
			$occupiedDay = $occupiedDay->day;
			if (isset($lastDay) && $lastDay->modify('+1 day') != $occupiedDay) {
				$periods[] = new \DatePeriod($firstDay, $oneDayInterval, $lastDay);
				unset($firstDay);
			}
			if (!isset($firstDay)) {
				$firstDay = clone $occupiedDay;
			}
			$lastDay = $occupiedDay;
		}
		if (isset($lastDay)) {
			$periods[] = new \DatePeriod($firstDay, $oneDayInterval, $lastDay->modify('+1 day'));
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
