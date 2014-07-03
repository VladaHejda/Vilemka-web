<?php

namespace Vilemka\Presenters;

use Nette\Application\BadRequestException;
use Tracy\Debugger;

class ErrorPresenter extends BasePresenter
{

	/**
	 * @param  Exception
	 */
	public function renderDefault($exception)
	{
		$reportSent = FALSE;

		if ($exception instanceof BadRequestException) {
			$code = $exception->getCode();
			$this->setView(in_array($code, array(403, 404, 500)) ? $code : '4xx');

			$referer = $this->getHttpRequest()->getHeader('referer');
			$host = $this->getHttpRequest()->getRemoteHost(); // todo

			// when 404 occurred after pass through anchor, it could be bad link in application (not user's typo) - send report
			if ($code === 404 && $referer && strpos($referer, $host)) {
				Debugger::log($exception, Debugger::ERROR);
				$reportSent = TRUE;
			} else {
				Debugger::log("HTTP $code: {$exception->getMessage()} in {$exception->getFile()}:{$exception->getLine()}", 'access');
			}

		} else {
			$this->setView('500');
			Debugger::log($exception, Debugger::ERROR);
			$reportSent = TRUE;
		}

		if ($this->isAjax()) {
			$this->payload->error = TRUE;
			$this->terminate();
		}

		$this->template->reportSent = $reportSent;
	}

}
