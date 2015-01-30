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


	protected function createFields()
	{
		$today = new DateTime;
		$datePattern = '(0?[0-9]|[12][0-9]|3[01])\\s*\\.\\s*(0?[0-9]|1[0-2])\\s*\\.\\s*[2-9][0-9]{3}';
		$dateParsePattern = '([0-9]+)\\s*\\.\\s*([0-9]+)\\s*\\.\\s*([0-9]+)';
		$dateFormatHelp = 'den. měsíc. rok';
		$labelFrom = 'Od';
		$labelTo = 'Do';

		$this->addText('name', 'Vaše jméno:')
			->setRequired()
		;

		$this->addText('from', "$labelFrom:")
			->setAttribute('placeholder', $dateFormatHelp)
			->setRequired()

			->addRule(self::PATTERN, sprintf('Uveďte prosím datum "%s" ve formátu "%s".', $labelFrom, $dateFormatHelp), $datePattern)

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
				return $dateFrom > $today;
			}, 'Datum "od" je v minulosti. Nelze rezerovovat termín v minulosti :)')

			->addRule(function() use (& $dateFrom) {
				return $dateFrom->format('w') == 6;
			}, sprintf('Lze rezervovat pouze turnusy od soboty do soboty. %s není sobota.', '%value'))
		;

		$this->addText('to', "$labelTo:")
			->setAttribute('placeholder', $dateFormatHelp)
			->setRequired()

			->addRule(self::PATTERN, sprintf('Uveďte prosím datum "%s" ve formátu "%s".', $labelTo, $dateFormatHelp), $datePattern)

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
				return $dateTo > $dateFrom;
			}, sprintf('Datum "%s" musí následovat až po datu "%s".', $labelTo, $labelFrom))

			->addRule(function() use (& $dateTo) {
				return $dateTo->format('w') == 6;
			}, sprintf('Lze rezervovat pouze turnusy od soboty do soboty. %s není sobota.', '%value'))

			->addRule(function() use (& $dateTo, & $dateFrom) {
				return $this->occupationRepository->isPeriodFree($dateFrom, $dateTo);
			}, sprintf('Lze rezervovat pouze turnusy od soboty do soboty. %s není sobota.', '%value'))
		;

		$this->addText('personCount', 'Počet osob:')
			->setType('number')
			->setRequired()
			->addRule(self::INTEGER, $message = 'Prosím, zadejte počet osob od %d do %d.', [1, $this->maxPersonsCapacity])
			->addRule(self::RANGE, $message, [1, $this->maxPersonsCapacity])
			->setAttribute('placeholder', sprintf('1 - %d', $this->maxPersonsCapacity))
		;

		$this->addText('email', 'E-mailová adresa:')
			->setType('email')
			->setDefaultValue('@')
			->addCondition(self::FILLED)
				->addRule(self::EMAIL, 'E-mailová adresa není správně.')
		;

		$this->addText('phone', 'Telefonní číslo:')
			->setRequired()
			->addCondition(self::FILLED)
				->addRule(self::PATTERN, 'Telefonní číslo není správně.', "^(\\+\\s*[0-9]{1,3}[- ]?)?[-0-9 ]{1,18}$")
		;

		$this->addTextArea('notice', 'Poznámka:');

		$this->addSubmit('send', 'Zaslat objednávku');
	}


	public function getValues($asArray = FALSE)
	{
		$values = parent::getValues($asArray);
		$values->from = $this->dateFrom;
		$values->to = $this->dateTo;
		return $values;
	}

}
