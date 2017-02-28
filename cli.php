<?php
/* Require composer autoloader */
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Inbox.php';
require __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'EmailSender.php';

$options = parseArguments();
$config = parse_ini_file('config.ini', true);

$logger = new Katzgrau\KLogger\Logger(__DIR__ . DIRECTORY_SEPARATOR . 'logs', $options['log-level']);

$inbox = new Inbox(
  $config['inbox']['imap'],
  $config['inbox']['username'],
  $config['inbox']['password'],
  $options
);

$emailSender = new EmailSender(
  $config['outgoing']['host'],
  $config['outgoing']['username'],
  $config['outgoing']['password'],
  $inbox,
  $options
);

if ($inbox->loggedIn() && $emailSender->loggedIn()) {

  echo 'Scanning inbox...';
  $emails = $inbox->scanEmails('/.*/', true);
  echo ' Done !' . PHP_EOL;

  echo count($emails) . ' email(s) to process ...';
  $successCount = $emailSender->processEmails($emails);
  echo ' Done !' . PHP_EOL;
  echo $successCount . '/' . count($emails) . ' email(s) sent' . PHP_EOL;

} else {
  echo 'An error happened (check the logs)' . PHP_EOL;
}


function parseArguments() {
  $cli = new Commando\Command();

  $cli->option('l')
    ->aka('log-level')
    ->describedAs('log Level (info, warning, debug)')
    ->must(function($val) {
        $levels = ['info', 'warning', 'debug'];
        return in_array($val, $levels);
    })
    ->default('info');

  $cli->option('n')
    ->aka('dry-run')
    ->describedAs('perform a trial run without sending emails')
    ->boolean()
    ->default(false);

  $options = [
    'dry-run' => $cli['dry-run'],
    'log-level' => $cli['log-level']
  ];
  return $options;
}

//print_r($emails);