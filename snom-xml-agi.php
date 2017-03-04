#!/usr/bin/php
<?php

/**
* (c) 2017 Volker Kettenbach
*
* See LICENSE for license details
*
* AGI tool for asterisk to send SIP-notifys with dialog-info in xml format
* to Snom phones. Tested in Snon 370 and 320.
* Use in asterisk as follows:
*
*	exten => 82,1,AGI(snom-xml-agi.php, <identify to notify on other phone>)
*	exten => 82,n,AGI(snom-xml-agi.php, <another identity>)
*	exten => 82,n,Dial(.....)
*
*
**/

require_once('PhpSIP.class.php');

$agivars = array();
while (!feof(STDIN)) {
    $agivar = trim(fgets(STDIN));
    if ($agivar === '') {
        break;
    }
    $agivar = explode(':', $agivar);
    $agivars[$agivar[0]] = trim($agivar[1]);
}
extract($agivars);

#The info passed by Asterisk is:
#agi_request - The filename of your script
#agi_channel - The originating channel (your phone)
#agi_language - The language code (e.g. "en")
#agi_type - The originating channel type (e.g. "SIP" or "ZAP")
#agi_uniqueid - A unique ID for the call
#agi_callerid - The caller ID number (or "unknown")
#agi_calleridname - The caller ID number (or "unknown")
#agi_callingpres - The presentation for the callerid in a ZAP channel
#agi_callingani2 - The number which is defined in ANI2 see Asterisk Detailed Variable List (only for PRI Channels)
#agi_callington - The type of number used in PRI Channels see Asterisk Detailed Variable List
#agi_callingtns - An optional 4 digit number (Transit Network Selector) used in PRI Channels see Asterisk Detailed Variable List
#agi_dnid - The dialed number id
#agi_rdnis - The referring DNIS number
#agi_context - Origin context in extensions.conf
#agi_extension - The called number
#agi_priority - The priority it was executed as in the dial plan
#agi_enhanced - The flag value is 1.0 if started as an EAGI script
#agi_accountcode - Account code of the origin channel

try
{
	$api = new PhpSIP('');
  #$api->setDebug(true);;
  $api->addHeader('Asterisk PBX');
  $api->addHeader('Event: xml');
  $api->setMethod('NOTIFY');
	$api->setFrom('sip:asterisk@asterisk.kettenbach-it.de');
  $api->addHeader('Content-Type: application/snomxml');

	if ($agi_calleridname == $agi_callerid) {
		$text = $agi_callerid;
	} else {
	  $text = $agi_calleridname .  ' ' . $agi_callerid;
  }
	$api->setUri("sip:".$agi_arg_1);
 	$api->setBody('
			<?xml version="1.0" encoding="UTF-8"?>
			<SnomIPPhoneText>
				<Text>
					Von: ' . $text . ' <br/>
					An: '  . $agi_extension . ' <br/>
				  Um: ' . date("D M j G:i:s") . '
			</Text>
			</SnomIPPhoneText>
		');
  $res = $api->send();

  #echo "response: $res\n";
  
} catch (Exception $e) {
  
  echo $e;
}

?>
