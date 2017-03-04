#!/usr/bin/php
<?php

/**
* (c) 2017 Volker Kettenbach
*
* See LICENSE for license details
*
* Commandline tool for sending XML screens to Snom phones
* Tested on Snom 370 - use option "-l long" for this phone
* Tested on Snom 320 - use option "-l short" for this phone
*
**/

require_once('PhpSIP.class.php');

$options = getopt("l:d:n:c:e:");
if ( !isset($options['l']) | !isset($options['d']) | !isset($options['n']) | !isset($options['c']) | !isset($options['e'])) {
	print_help_and_exit($argv);
}
if ( $options['l']!="long" & $options['l']!="short"){
	print "Invalid option: ". $options['l']  ."\n";
	print_help_and_exit($argv);
}

try
{
	$api = new PhpSIP('');
  #$api->setDebug(true);;
  $api->addHeader('Asterisk PBX');
  $api->addHeader('Event: xml');
  $api->setMethod('NOTIFY');
	$api->setFrom('sip:asterisk@asterisk.kettenbach-it.de');
  $api->addHeader('Content-Type: application/snomxml');

	$api->setUri("sip:".$options['d']);
	if ($options['l'] == "long") {
  	$api->setBody('
			<?xml version="1.0" encoding="UTF-8"?>
			<SnomIPPhoneText>
				<Title>Eingehender Anruf</Title>
				<Text>
					An: '. $options['e'] . '<br/>
					Von: ' . $options['n'] . '<br/> 
					Nummer: ' . $options['c'] . '
			</Text>
			</SnomIPPhoneText>
		');
	} 
	if ($options['l'] == "short") {
  	$api->setBody('
			<?xml version="1.0" encoding="UTF-8"?>
			<SnomIPPhoneText>
				<Text>
					Von: ' . $options['n'] .' ' . $options['c'] . '
			</Text>
			</SnomIPPhoneText>
		');
	}
  $res = $api->send();

  echo "response: $res\n";
  
} catch (Exception $e) {
  
  echo $e;
}

function print_help_and_exit($argv) {
	print "Usage: $argv[0] -l <short|long> -d <destination-uri w/o sip> -n <name of caller>  -c <callerid number> -e <extension called>";
	exit;
}

?>
