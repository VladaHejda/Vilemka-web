<?php

namespace Vilemka\Components;

use Nette\Application\UI\Form;
use Nette\Http\Request;
use Tracy\Debugger;
use Vilemka\ValueObject\EmailAddress;
use Vilemka\MessageRepository;
use Vilemka\UserMessageCopyNotifier;
use Vilemka\AdminMessageNotifier;

class FooterControl extends \Nette\Application\UI\Control
{

	/** @var \stdClass */
	protected $templateVars;

	/** @var MessageRepository */
	protected $messageRepository;

	/** @var UserMessageCopyNotifier */
	protected $userMessageCopyNotifier;

	/** @var AdminMessageNotifier */
	protected $adminMessageNotifier;

	/** @var Request */
	protected $request;


	/**
	 * @param array $templateVars
	 * @param MessageRepository $messageRepository
	 * @param UserMessageCopyNotifier $userMessageCopyNotifier
	 * @param AdminMessageNotifier $adminMessageNotifier
	 * @param Request $request
	 */
	public function __construct(array $templateVars, MessageRepository $messageRepository, UserMessageCopyNotifier $userMessageCopyNotifier,
		AdminMessageNotifier $adminMessageNotifier, Request $request)
	{
		parent::__construct();
		$this->messageRepository = $messageRepository;
		$this->userMessageCopyNotifier = $userMessageCopyNotifier;
		$this->adminMessageNotifier = $adminMessageNotifier;
		$this->request = $request;

		$this->templateVars = (object) $templateVars;
	}


	public function createComponentContactForm()
	{
		$form = new ContactForm;
		$form->onSuccess[] = [$this, 'sendMessage'];
		return $form;
	}


	public function render()
	{
		$this->template->vars = $this->templateVars;
		$this->template->setFile(__DIR__ . '/footer.latte');
		$this->template->render();
	}


	/**
	 * @param Form $form
	 */
	public function sendMessage(Form $form)
	{
		$values = $form->getValues();

		$recipient = $values->email ? new EmailAddress($this->request, $values->email, $values->name ?: null) : null;

		try {
			$this->adminMessageNotifier->notify($values->message, $values->name, $recipient);
		} catch (\Nette\InvalidStateException $e) {
			Debugger::log($e, Debugger::ERROR);
		}

		$this->messageRepository->insert($values->message, $values->name, $recipient);

		if ($recipient) {
			try {
				$this->userMessageCopyNotifier->notify($recipient, $values->message);
			} catch (\Nette\InvalidStateException $e) {
				Debugger::log($e, Debugger::ERROR);
			}
		}

		$this->flashMessage(sprintf('Zpráva odeslána.%s Děkujeme!', $recipient ? ' Brzy se Vám ozveme.' : ''));
		$this->redirect(303, 'this#kontakt');
	}

}
