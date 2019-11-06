#!/usr/bin/php
<?php

$cmdDig = '/usr/bin/dig';
$acme = '_acme-challenge.';
$dmgdev = '._acme-dns.dmgdev.com.';

if (file_exists($cmdDig)) {
    $digCheck = true;
} else {
    echo "\n*** ERROR *** ||| $cmdDig not found, please install dig\n" . PHP_EOL;
    exit("CODE (1)");
}

function validDom($domain) {
    if(!preg_match("/^(?!\-)(?:[a-zA-Z\d\-]{0,62}[a-zA-Z\d]\.){1,126}(?!\d+)[a-zA-Z\d]{1,63}$/i", $domain) ) {
    return false;
    }
return $domain;
}//end validDom function.

echo "What domain are we validating? ";

while(validDom($domain) == false) {
    // take input
    $domain = rtrim(fgets(STDIN));
    if(validDom($domain) == false) {
        echo "Please enter a valid domain. Do not include the protocol (http, https): ";
    }
}

if($digCheck) {
    $acmeCheck = "$cmdDig @1.1.1.1 ".$acme.$domain." cname +short";
    $strLookup = `$acmeCheck`;
    $strLookup = trim($strLookup);
} 

if ($strLookup == $domain.$dmgdev) {
    echo "\n\nSUCCESS! ".$domain." HAS A VALID ACME-CHALLENGE RECORD!\n\nRETURNED HOST: ".$acme.$domain."\nEXPECTED HOST: ".$acme.$domain."\n\nRETURNED TARGET: ".$strLookup."\nEXPECTED TARGET: ".$domain.$dmgdev."\n\n".$acme.$domain." is an alias for ".$domain.$dmgdev."\n\n";
} elseif ($strLookup != $domain.$dmgdev) {
    $acmeHostCheck = "$cmdDig @1.1.1.1 ".$acme.$domain.".".$domain." cname +short";
    $strLookupHost = `$acmeHostCheck`;
    $strLookupHost = trim($strLookupHost);
//    print_r($strLookupHost."\n");
//    print_r($strLookup);
    if ($strLookupHost == $domain.$dmgdev) {
        echo "\nFAILED! ".$domain." HAS AN INVALID ACME-CHALLENGE RECORD!\nPLEASE CHECK THE HOST PORTION OF THE RECORD!\n\nEXPECTED HOST: ".$acme.$domain."\nRETURNED HOST: ".$acme.$domain.".".$domain."\n\nIn many cases, the customer has pasted the *ENTIRE* HOST record into their DNS registrar.\nHave them enter the HOST portion as: _acme-challenge\n";
    }

}

    /*
elseif ($strLookup = null ) {
            $acmeHostCheck = "$cmdDig @1.1.1.1 ".$acme.$domain.".".$domain." cname +short";
            $strLookupHost = `$acmeHostCheck`;
            $strLookupHost = trim($strLookupHost);
}

/*
elseif {
    echo "\n*** ERROR *** ||| $cmdDig not found, please install dig" . PHP_EOL;
    exit();
}
//print_r($strLookup);
*/
echo "\n";