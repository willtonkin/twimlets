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
    
    // if The Dial flag is present, it means we're returning from an attempted Dial
    if(isset($_REQUEST['Dial']) && (strlen($_REQUEST['DialStatus']) || strlen($_REQUEST['DialCallStatus']))) { 

        if($_REQUEST['DialCallStatus'] == "completed" || $_REQUEST['DialStatus'] == "answered" || !strlen($_REQUEST['FailUrl'])) {

            // answered, or no failure url given, so just hangup
            $response->addHangup();
            
        } else {

            // DialStatus was not answered, so redirect to FailUrl
            header("Location: {$_REQUEST['FailUrl']}");
            die;
            
        }

    } else {
        
        // No dial flag, means it's our first run through the script
        
        // if an array of Allowed Callers is provided and populated, determine if we're allowed to be forwarded
        if(is_array($_GET['AllowedCallers']) && count(array_filter($_GET['AllowedCallers']))) {
        
            // normalize all numbers, removing any non-digits
            foreach($_GET['AllowedCallers'] AS &$phone) {
                $phone = preg_replace('/[^0-9]/', '', $phone);

                if($_REQUEST['ApiVersion'] == '2008-08-01' && strlen($phone) == 11 && substr($phone, 0, 1) == "1") {
                    $phone = substr($phone, 1);
                }
            }
            
            // grab the to and from phone numbers
            $from = strlen($_REQUEST['From']) ? $_REQUEST['From'] : $_REQUEST['Caller'];
            $to = strlen($_REQUEST['To']) ? $_REQUEST['To'] : $_REQUEST['Called'];

            // figure out if we're allowed to call or not
            $isAllowed = (in_array(preg_replace('[^0-9]', '', $from), $_GET['AllowedCallers']) || in_array(preg_replace('[^0-9]', '', $to), $_GET['AllowedCallers']));
        
        } else {
            // no allowed callers given, so just forward the call
            $isAllowed = true; 
        }
        
        if(!$isAllowed) {
            // forwarding is being restricted and we are not allowed to abort with a message
            $response->addSay("Sorry, you are calling from a restricted number. Good bye.");
        }
        else {
            // we made it to here, so just dial the number, with the optional Timeout given
            $actionUrl = $_SERVER['SCRIPT_URL'] .
                "?Dial=true" .
                  ($_REQUEST['FailUrl'] ?
                  "&FailUrl=".urlencode($_REQUEST['FailUrl']) : "");
            $response->addDial($_GET['PhoneNumber'],
                array("action"=>$actionUrl,
                "timeout"=>$_REQUEST['Timeout'] ? $_REQUEST['Timeout'] : 20));
        }
    }
    
    // send response
    $response->Respond();