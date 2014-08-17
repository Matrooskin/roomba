<?php

/**
 * Sends data to the modem
 */
function send_data($f, $string) {
//    $send = '';
//    for ($i = 0; $i < strlen($string); $i++) {
//        $send .= ord($string[$i]) . ' ';
//    }
//    $length = strlen($string);
//    echo "< $send\r\n$length\r\n";

    $bytes = fwrite($f, $string);
//    echo PHP_EOL . "sended $bytes" .PHP_EOL;
}

/**
 * Reads lines from the serial port.  It reads until a non blank line is
 * received then returns it.
 */
function get_line($f) {
    do {
        $date = fgets($f);
        // remove carriage returns, line feeds and excess white space
        $response = trim(str_replace(array("\r\n","\r","\n"), '', $date));
    } while ($response == '');

    return $response;
}

function get_binary($f, $endTime){
    do {
        $date = fgetc($f);
        // $response = '';
        // if(strlen($date) !== 0){
        //     var_dump('>>>>>>>> ' . strlen($date));
        // }
        // for ($i = 0; $i < strlen($date); $i++) {
        //     $response .= ord($date[$i]) . ' ';
        // }
        $time = time();
    } while ($date == '' && ($time < $endTime));

    return $date;
}

/**
 * Waits for OK to be sent back by the modem.
 */
function wait_for_ok($f) {
    do {
        $response = get_line($f);
        echo "> $response\n";
    } while ($response != "OK");
}

// MAIN CODE

// Create the context
$c = stream_context_create(array('dio' =>
        array('data_rate' => 115200,
            'data_bits' => 8,
            'stop_bits' => 1,
            'parity' => 0,
            'flow_control' => 0,
            'is_blocking' => 0,
            'canonical' => 1)));

// Open the port
$f = fopen("dio.serial:///dev/ttyS1", "r+", false, $c);
//$f = fopen("output.txt", "r+", false, $c);
//$f = fopen("dio.serial://COM2", "r+", false, $c);

$maxLength = 0;
if ($f) {
    // Re-enable blocking
    stream_set_blocking($f, false);
    echo "begin".PHP_EOL;
    // send_data($f,chr(128));
    // send_data($f,chr(164).chr(65).chr(66).chr(67).chr(68)); //led ABCD
    //164] [65] [66] [67] [68 LED abcd
    send_data($f,chr(142).chr(100));
//    send_data($f,chr(13));
   // send_data($f,chr(135)); //clean
//    send_data($f,chr(133));
//    [137] [255] [56] [1] [244] chr

    // Set SMS text entry mode
    //send_data($f, "$$$");
//    wait_for_ok($f);
    $startTime = time();
    $endTime = $startTime + 15;
    $time = time();

    $sum = 0;
    while ($time < $endTime){
        // var_dump('>>>> '.time(), time()-$startTime, time()-$startTime < $timeOut, '<<<<<<<');
        $line = get_binary($f, $endTime);
        $sum += strlen($line);
        var_dump($sum . ' ' . ord($line));
        //$maxLength = max(strlen($line), $maxLength);
        //echo "\x0D".str_pad($line, $maxLength, ' ');
        $time = time();
    }
    // Send an SMS
//    send_data($f, "AT+CMGS=\"" . PHONE_NUMBER . "\"");
//    send_data($f, SMS_TEXT);
    // Send CTRL-Z to end SMS text entry
//    fprintf($f, chr(26));
//    wait_for_ok($f);

    // Close the port
    fclose($f);
}
echo PHP_EOL . "end". PHP_EOL;