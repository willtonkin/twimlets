<?php

    /*
    Copyright (c) 2012 Twilio, Inc.

    Permission is hereby granted, free of charge, to any person
    obtaining a copy of this software and associated documentation
    files (the "Software"), to deal in the Software without
    restriction, including without limitation the rights to use,
    copy, modify, merge, publish, distribute, sublicense, and/or sell
    copies of the Software, and to permit persons to whom the
    Software is furnished to do so, subject to the following
    conditions:

    The above copyright notice and this permission notice shall be
    included in all copies or substantial portions of the Software.

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
    EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
    OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
    NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
    HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
    WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
    FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
    OTHER DEALINGS IN THE SOFTWARE.
    */

    require "twilio-lib.php";
    
    // initiate response library
    $response = new Response();

    // init as array, if it's not
    if(!is_array($_REQUEST['PhoneNumbers']))
        $_REQUEST['PhoneNumbers'] = array($_REQUEST['PhoneNumbers']);

    // remove empty entries from PhoneNumbers
    $_REQUEST['PhoneNumbers'] = @array_filter($_REQUEST['PhoneNumbers']);
    
    // verify no more than 10 numbers given
    if(count($_REQUEST['PhoneNumbers']) > 10)
        $_REQUEST['PhoneNumbers'] = array_splice($_REQUEST['PhoneNumbers'], 10);
        
    // if The Dial flag is present, it means we're returning from an attempted Dial
    if(isset($_REQUEST['Dial']) && ($_REQUEST['DialStatus'] == "answered" || $_REQUEST['DialCallStatus'] == "completed")) {
        
        // answered call, so just hangup
        $response->addHangup();
        
    } else {
        
        // No dial flag, or anything other than "answered", roll on to the next (or first, as it may be) number

        // get the next number of the array
        if(!$nextNumber = @array_shift($_REQUEST['PhoneNumbers'])) {
    
            // if no phone numbers left, redirect to the FailUrl
            
            // FailUrl found, so redirect and kill the cookie
            if(strlen($_REQUEST["FailUrl"])) {
                header("Location: {$_REQUEST["FailUrl"]}");
                die;
            } else {
    
                // no FailUrl found, so just end the call
                $response->addHangup();
                
            }
            
        } else {
        
            // re-assemble remaining numbers into a QueryString, shifting the 0th off the array
            $qs = "FailUrl=".urlencode($_REQUEST['FailUrl'])."&Timeout=".urlencode($_REQUEST['Timeout'])."&Message=".urlencode($_REQUEST['Message']);
            foreach($_REQUEST['PhoneNumbers'] AS $number)
                $qs .= "&PhoneNumbers%5B%5D=" . urlencode($number);
                
            // add a dial to the response
            $dial = $response->addDial(array("action"=>"{$_SERVER['SCRIPT_URL']}?Dial=true&$qs", "timeout"=>$_REQUEST['Timeout'] ? $_REQUEST['Timeout'] : 60));
            
            // add the number to dial
            $dial->addNumber($nextNumber, array("url"=>"whisper?Message=".urlencode($_REQUEST['Message']) . "&HumanCheck=1"));
                
        }
        
    } 
    
    // send the response
    $response->Respond();