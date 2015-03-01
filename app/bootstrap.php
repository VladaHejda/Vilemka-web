<?php

require __DIR__ . '/../vendor/autoload.php';

$configurator = new Nette\Configurator;

$configurator->setDebugMode([]);
$configurator->enableDebugger(__DIR__ . '/../log', 'hejdav@centrum.cz');
$configurator->setTempDirectory(__DIR__ . '/../temp');

$configurator
	->addConfig(__DIR__ . '/config/.parameters.neon')
	->addConfig(__DIR__ . '/config/.services.neon')
	->addConfig(__DIR__ . '/config/.emails.neon')
	->addConfig(__DIR__ . '/config/.secure.neon', $configurator::AUTO)
;

$container = $configurator->createContainer();
$container->getService('application')->run();
