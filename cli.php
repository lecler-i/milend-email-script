<?php
/* Require composer autoloader */
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Inbox.php';
require __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'EmailSender.php';

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

var_dump($options);

$logger = new Katzgrau\KLogger\Logger(__DIR__ . DIRECTORY_SEPARATOR . 'logs', $options['log-level']);

$inbox = new Inbox(
  '{imap.gmail.com:993/imap/ssl}INBOX',
  'ttest@milend.com',
  'thomas1234',
  $options
);

$emailSender = new EmailSender(
  'smtp.gmail.com',
  'ttest@milend.com',
  'thomas1234',
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


//print_r($emails);