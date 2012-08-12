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

    // Use the Twilio Response library to construct our XML response
    require "twilio-lib.php";

    function endsWith( $str, $sub ) {
        return ( substr( $str, strlen( $str ) - strlen( $sub ) ) == $sub );
    }
    
    // initiate response library
    $response = new Response();

    // require an S3 bucket
    if(!strlen($_GET['Bucket'] = trim($_GET['Bucket']))) {
        $response->addSay("An S 3 bucket is required.");
        $response->Respond();
        die;
    }

    // use Curl to get the contents of the bucket
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_TIMEOUT,5);
    curl_setopt($ch, CURLOPT_URL, "http://{$_GET['Bucket']}.s3.amazonaws.com");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
    
    // do the fetch
    if(!$output = curl_exec($ch)) {
        $response->addSay("Failed to fetch the hold music.");
        $response->Respond();
        die;
    }

    // parse as XML
    $xml = new SimpleXMLElement($output);
    
    // construct an array of URLs
    $urls = array();
    foreach($xml->Contents as $c) 
        // add any mp3, wav or ul to the urls array
        if((endsWith($c->Key, ".mp3")) ||(endsWith($c->Key, ".wav")) ||(endsWith($c->Key, ".ul"))) 
            $urls[]=$c->Key;

    // if no songs where found, then bail
    if(!count($urls)) {
        $response->addSay("Failed to fetch the hold music.");
        $response->Respond();
        die;
    }

    // and let's shuffle
    shuffle($urls);
    
    // Play each URL        
    foreach($urls as $url){

        // Play each url
        $response->addPlay("http://{$_GET['Bucket']}.s3.amazonaws.com/".urlencode($url));
        
        // if a message was given, then output it between music
        // first, check to see if we have an http URL (simple check)
        if(strtolower(substr(trim($_GET['Message']), 0, 4)) == "http")
            $response->addPlay($_GET['Message']);

        // read back the message given
        elseif(strlen($_GET['Message']))
            $response->addSay(stripslashes($_GET['Message']));
        
    }
    
    // and loop
    $response->addRedirect();

    // send response
    $response->Respond();