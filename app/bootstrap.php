<?php

require __DIR__ . '/../vendor/autoload.php';

$configurator = new Nette\Configurator;

$configurator->setDebugMode(['localhost']);
$configurator->enableDebugger(__DIR__ . '/../log');
$configurator->setTempDirectory(__DIR__ . '/../temp');

$environment = $configurator->isDebugMode() ? 'dev' : 'production';
$configurator
	->addConfig(__DIR__ . '/config/.parameters.neon')
	->addConfig(__DIR__ . '/config/.services.neon')
	->addConfig(__DIR__ . '/config/.emails.neon')
	->addConfig(__DIR__ . '/config/.secure.neon', $environment)
;

$container = $configurator->createContainer();
$container->getService('application')->run();
