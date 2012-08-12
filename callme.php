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
    if(isset($_REQUEST['Dial'])    && (strlen($_REQUEST['DialStatus']) || strlen($_REQUEST['DialCallStatus']))) {

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

        // Add the FailUrl to the action param, if specified
        $failParam="";
        if (isset($_REQUEST['FailUrl']))
            $failParam="&FailUrl=" . urlencode($_REQUEST['FailUrl']);
        
        // we made it to here, so just dial the number, with the optional Timeout given
        $dial = $response->addDial(array("action"=>"{$_SERVER['SCRIPT_URL']}?Dial=true$failParam", "timeout"=>$_REQUEST['Timeout'] ? $_REQUEST['Timeout'] : 20));
        
        // add number attribute
        $dial->addNumber($_GET['PhoneNumber'], array("url"=>"whisper?Message=" . urlencode($_GET['Message'])));
        
    }
    
    // send response
    $response->Respond();