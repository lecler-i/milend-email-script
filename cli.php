<?php
/* Require composer autoloader */
require __DIR__ . DIRECTORY_SEPARATOR . 'vendor'. DIRECTORY_SEPARATOR . 'autoload.php';
require __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Inbox.php';
require __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'EmailSender.php';

$options = parseArguments();
$config = parseConfig('config.ini');

$logger = new Katzgrau\KLogger\Logger(__DIR__ . DIRECTORY_SEPARATOR . 'logs', $options['log-level']);

if (function_exists('imap_open') == false) {
    $logger->error('IMAP functions are not available. Please enable the php module "imap".');
    echo 'IMAP functions are not available.' . PHP_EOL;
    exit(1);
}

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
  $emails = $inbox->scanEmails($config['rules']['from_address'], true);
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

function parseConfig($file) {
  $config = parse_ini_file('config.ini', true);
  return $config;
}

//print_r($emails);