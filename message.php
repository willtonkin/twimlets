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
    
    // create an array of messagse if not already
    if(!is_array($_GET['Message']))
        $_GET['Message'] = array($_GET['Message']);
        
    // foreach message, output it
    foreach($_GET['Message'] AS $msg) {

        // figure out the message
        // first, check to see if we have an http URL (simple check)
        if(strtolower(substr(trim($msg), 0, 4)) == "http")
            $response->addPlay($msg);
            
        // check if we have any message, if so, read it back 
        elseif(strlen(trim($msg)))
            $response->addSay(stripslashes($msg));
    
    }            
    
    // send response
    $response->Respond();