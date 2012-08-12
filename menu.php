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

    // init Options as array, if it's not already
    if(!is_array($_REQUEST['Options']))
        $_REQUEST['Options'] = array($_REQUEST['Options']);

    // remove empty entries from PhoneNumbers
    $_REQUEST['Options'] = array_filter($_REQUEST['Options']);
    
    // if DialStatus was sent, it means we got here after a Dial attempt
    if(strlen($_REQUEST['Digits'])) {
        
        // if valid option given, the redirect
        if(strlen($location = $_REQUEST['Options'][$_REQUEST['Digits']])) {
            
            header("Location: $location");
            die;
            
        } else
        
            // answered call, so just hangup
            $response->addSay("I'm sorry, that wasn't a valid option.");
        
    } 
    
    // calculate the max number of digits we need to wait for
    $maxDigits = 1;
    foreach($_REQUEST['Options'] AS $key=>$value)
        $maxDigits = max($maxDigits, strlen($key));
    
    // add a gather with numDigits
    $gather = $response->addGather(array("numDigits"=>$maxDigits));
        
    // play the greeting while accepting digits
    // figure out the message
    // first, check to see if we have an http URL (simple check)
    if(strtolower(substr(trim($_GET['Message']), 0, 4)) == "http")
        $gather->addPlay($_GET['Message']);
        
    // read back the message given
    elseif(strlen($_GET['Message']))
        $gather->addSay(stripslashes($_GET['Message']));
        
    // add a redirect if nothing was pressed
    $response->addRedirect();
    
    // send the response
    $response->Respond();