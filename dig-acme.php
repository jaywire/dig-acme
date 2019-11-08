#!/usr/bin/php
<?php

// defininig variables
$cmdDig = '/usr/bin/dig';
$acme = '_acme-challenge.';
$dmgdev = '._acme-dns.dmgdev.com.';
$dnsDefault = '1.1.1.1';

// checking that dig exists on machine
if (file_exists($cmdDig)) {
    $digCheck = true;
} else {
    echo "\n*** ERROR *** ||| $cmdDig not found, please install dig\n" . PHP_EOL;
    exit();
}
// validate $domain provided by user is a valid domain
function validDom($domain) {
    if(!preg_match("/^(?!\-)(?:[a-zA-Z\d\-]{0,62}[a-zA-Z\d]\.){1,126}(?!\d+)[a-zA-Z\d]{1,63}$/i", $domain) ) {
    return false;
    }
return $domain;
}//end validDom function.

echo "What domain are we validating? ";

// Give error and take input again if $domain fails regex check and loop back through validDom function
while(validDom($domain) == false) {
    $domain = rtrim(fgets(STDIN));
    if(validDom($domain) == false) {
        echo "***ERROR!* Please enter a valid domain. Do not include the protocol (http, https): ";
    }
}

// validate $dnsIP provided by user is a valid IPV4 address
function validIP($dnsIP) {
    if(!preg_match("/^(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])\.(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])\.(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])\.(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])$/i", $dnsIP) ) {
    return false;
    }
return $dnsIP;
}//end validIP function.

echo "What DNS server are we using? To use the default, hit Enter: ";

// Give error and take input again if $dnsIP fails regex check and loop back through validIP function
// If user does not want to provide their own DNS server, use $dnsDefault
while(validIP($dnsIP) == false) {
    $dnsIP = rtrim(fgets(STDIN));
    if ($dnsIP == null) {
        echo "\Used default DNS (".$dnsDefault.") for this check.\n";
        $dnsIP = $dnsDefault;
    } elseif(validIP($dnsIP) == false) {
        echo "***ERROR!*** Please enter a valid IPV4 address. If you'd like to use the default, hit ENTER: ";
    }
}

// running dig command if dig exists and domain passes validation
if($digCheck) {
    $acmeCheck = "$cmdDig @".$dnsIP." ".$acme.$domain." cname +short";
    $strLookup = `$acmeCheck`;
    $strLookup = trim($strLookup);
} 
// checking dig response for expected response and and returning detailed errors
if ($strLookup == $domain.$dmgdev) {
    echo "\n\nSUCCESS! ".$domain." HAS A VALID ACME-CHALLENGE RECORD!\n\nRETURNED HOST: ".$acme.$domain."\nEXPECTED HOST: ".$acme.$domain."\n\nRETURNED TARGET: ".$strLookup."\nEXPECTED TARGET: ".$domain.$dmgdev."\n\n".$acme.$domain." is an alias for ".$domain.$dmgdev."\n\n";
} elseif ($strLookup != $domain.$dmgdev) {
    $acmeHostCheck = "$cmdDig @".$dnsIP." ".$acme.$domain.".".$domain." cname +short";
    $strLookupFail = `$acmeHostCheck`;
    $strLookupFail = trim($strLookupFail);
    if ($strLookupFail == $domain.$dmgdev) {
        echo "\nFAILED! ".$domain." HAS AN INVALID ACME-CHALLENGE RECORD!\nPLEASE CHECK THE HOST PORTION OF THE RECORD!\n\nEXPECTED HOST: ".$acme.$domain."\nRETURNED HOST: ".$acme.$domain.".".$domain."\n\nIn many cases, the customer has pasted the *ENTIRE* HOST record into their DNS registrar.\nHave them enter the HOST portion as: _acme-challenge\n\n";
        exit();
    } elseif ($strLookup != $domain.$dmgdev && $strLookupFail == null && $strLookup != $domain."." && $strLookup != null) {
        echo "\nFAILED! ".$domain." HAS AN INVALID ACME-CHALLENGE RECORD!\nPLEASE CHECK THE TARGET PORTION OF THE RECORD!\n\nEXPECTED TARGET: ".$domain."._acme-dns.dmgdev.com\nRETURNED TARGET: ".$strLookup."\n\n";
        exit();
    } elseif ($strLookup == null && $strLookupFail == null) {
        echo "\nFAILED! ".$domain." HAS NO VALID ACME-CHALLENGE RECORD!\nPLEASE CHECK THE DOMAIN ENTRIES AND TRY AGAIN!\n\nEXPECTED HOST: ".$acme.$domain."\nEXPECTED TARGET: ".$domain."._acme-dns.dmgdev.com\n\n";
        exit();
    } elseif ($strLookup == $domain."." && $strLookupFail == $domain.".") {
        echo "\nFAILED! ".$domain." IS SHOWING A WILDCARD RESPONSE. PLEASE CHECK HOST ENTRY OF THE CNAME RECORD.\n\n";
        exit();
    }
}

echo "\n";

?>