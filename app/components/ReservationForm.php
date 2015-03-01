<?php

namespace Vilemka\Components;

use Nette\Forms\Controls\TextBase;
use Nette\Utils\DateTime;
use Nette\Utils\Strings;
use Vilemka\OccupationRepository;

class ReservationForm extends \Nette\Application\UI\Form
{

	/** @var int */
	protected $maxPersonsCapacity;

	/** @var OccupationRepository */
	protected $occupationRepository;

	/** @var DateTime */
	protected $dateFrom, $dateTo;


	/**
	 * @param int $maxPersonsCapacity
	 * @param OccupationRepository $occupationRepository
	 */
	public function __construct($maxPersonsCapacity, OccupationRepository $occupationRepository)
	{
		parent::__construct();
		$this->maxPersonsCapacity = $maxPersonsCapacity;
		$this->occupationRepository = $occupationRepository;
		$this->createFields();
	}


	public function setSelectedWeek($year, $week)
	{
		$date = (new DateTime)->setISODate($year, $week)->modify('-2 days');
		$from = $date->format('j. n. Y');
		$to = $date->modify('+7 days')->format('j. n. Y');

		$this->getComponent('from')->setDefaultValue($from);
		$this->getComponent('to')->setDefaultValue($to);
	}


	protected function createFields()
	{
		$today = new DateTime;
		$datePattern = '(0?[0-9]|[12][0-9]|3[01])\\s*\\.\\s*(0?[0-9]|1[0-2])\\s*\\.\\s*[2-9][0-9]{3}';
		$dateParsePattern = '([0-9]+)\\s*\\.\\s*([0-9]+)\\s*\\.\\s*([0-9]+)';
		$dateFormatHelp = 'den. měsíc. rok';

		$this->addText('name', 'Vaše jméno:')
			->setRequired('Prosím, zadejte jméno.')
			->addRule(self::MAX_LENGTH, 'Prosím, zkraťte jméno na %d znaků, delší se nám nevejde do databáze :(', 255)
		;

		$this->addText('from', "Od:")
			->setHtmlId('reservation-from')
			->setAttribute('placeholder', $dateFormatHelp)
			->setRequired('Prosím, zadejte termín.')

			->addRule(self::PATTERN, sprintf('Uveďte prosím datum ve formátu "%s".', $dateFormatHelp), $datePattern)

			->addRule(function (TextBase $control) use (& $dateFrom, $dateParsePattern) {
				list(, $day, $month, $year) = Strings::match($control->getValue(), "/$dateParsePattern/");
				if (checkdate($month, $day, $year)) {
					$dateFrom = DateTime::from(mktime(0, 0, 0, $month, $day, $year));
					$this->dateFrom = $dateFrom;
					return true;
				}
				return false;
			}, 'Datum %value neexistuje...')

			->addRule(function () use (& $dateFrom, $today) {
				if (!$dateFrom) {
					return true;
				}
				return $dateFrom > $today;
			}, 'Nelze rezerovovat termín v minulosti :)')

			->addRule(function() use (& $dateFrom) {
				if (!$dateFrom) {
					return true;
				}
				return (int) $dateFrom->format('w') === 6;
			}, 'Lze rezervovat pouze turnusy od soboty do soboty.')
		;

		$this->addText('to', "Do:")
			->setHtmlId('reservation-to')
			->setAttribute('placeholder', $dateFormatHelp)
			->setRequired('Prosím, zadejte termín.')

			->addRule(self::PATTERN, sprintf('Uveďte prosím datum ve formátu "%s".', $dateFormatHelp), $datePattern)

			->addRule(function (TextBase $control) use (& $dateTo, $dateParsePattern) {
				list(, $day, $month, $year) = Strings::match($control->getValue(), "/$dateParsePattern/");
				if (checkdate($month, $day, $year)) {
					$dateTo = DateTime::from(mktime(0, 0, 0, $month, $day, $year));
					$this->dateTo = $dateTo;
					return true;
				}
				return false;
			}, 'Datum %value neexistuje...')

			->addRule(function() use (& $dateTo, & $dateFrom) {
				if (!$dateFrom || !$dateTo) {
					return true;
				}
				return $dateTo > $dateFrom;
			}, 'Data musí následovat po sobě.')

			->addRule(function() use (& $dateTo) {
				if (!$dateTo) {
					return true;
				}
				return (int) $dateTo->format('w') === 6;
			}, 'Lze rezervovat pouze turnusy od soboty do soboty.')

			->addRule(function() use (& $dateTo, & $dateFrom) {
				if (!$dateFrom || !$dateTo) {
					return true;
				}
				return $this->occupationRepository->isPeriodFree($dateFrom, $dateTo);
			}, 'Termín bohužel není volný :-(. Zkontrolujte kalendář výše.')
		;

		$this->addText('personCount', 'Počet osob:')
			->setType('number')
			->setRequired('Prosím, zadejte počet osob.')
			->addRule(self::INTEGER, $message = 'Prosím, zadejte počet osob od %d do %d.', [1, $this->maxPersonsCapacity])
			->addRule(self::RANGE, $message, [1, $this->maxPersonsCapacity])
			->setAttribute('placeholder', sprintf('1 - %d', $this->maxPersonsCapacity))
			->setAttribute('class', 'text')
		;

		$this->addText('email', 'E-mailová adresa:')
			->setType('email')
			->setAttribute('class', 'text')
			->addCondition(self::FILLED)
				->addRule(self::EMAIL, 'E-mailová adresa není správně.')
		;

		$this->addText('phone', 'Telefonní číslo:')
			->setRequired('Prosím, zadejte telefonní číslo.')
			->addCondition(self::FILLED)
				->addRule(self::PATTERN, 'Telefonní číslo není správně.', "^(\\+\\s*[0-9]{1,3}[- ]?)?[-0-9 ]{1,18}$")
		;

		$this->addTextArea('notice', 'Poznámka:')
			->setAttribute('class', 'text')
		;

		$this->addSubmit('send', 'Zaslat objednávku');
	}


	public function getValues($asArray = FALSE)
	{
		$values = parent::getValues($asArray);
		$values->from = $this->dateFrom;
		$values->to = $this->dateTo;
		$values->phone = $values->phone ? preg_replace('/\\s+/', ' ', $values->phone) : $values->phone;
		return $values;
	}

}
