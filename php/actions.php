<?php
require_once 'config.php';

//= signup new lead in lottosend system
function signUpViaAPI( $first_name, $last_name, $prefix, $phone, $email, $address, $country, $passwd, $a_aid ){
    global $cfg;

    $arrParams = array(
        'web_user' => array(
            'email' => $email,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'phone' => $prefix . $phone,
            'password' => $passwd,
            'country' => $country,
            'address' => $address
        )
    );
    if ( $a_aid != '' ) {
        $arrParams['web_user']['aid'] = $a_aid;
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$cfg['web_signup_url']);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Authorization: Token token="' . $cfg['web_signup_token'] . '"'
    ));
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode( $arrParams ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $server_output = curl_exec ($ch);
    return $server_output;
}

//= return array with latest lottery draws, jackpots, and dates
function getLotteriesInfoFromAPI(){
    global $cfg;

    $xml = @file_get_contents( $cfg['lottery_api_url'] );
    $arrLotteries = xml2array($xml);
    $arrRet = array();
    foreach ( $arrLotteries['array'] as $lottery ) {
        $arrUnit = array();
        $arrUnit['currency'] = $lottery['array'][0]['currency'];
        $arrUnit['draw-time'] = $lottery['array'][0]['draw-time'];
        $arrUnit['id'] = $lottery['array'][0]['id'];
        $arrUnit['name'] = $lottery['array'][0]['name'];
        $arrUnit['time-zone'] = $lottery['array'][0]['time-zone'];
        $arrUnit['date'] = $lottery['array'][1]['array']['date'];
        $arrUnit['jackpot'] = $lottery['array'][1]['array']['jackpot'];
        $offsetTimezone = getTimezoneOffset( $arrUnit['time-zone'] );
        $lottery_timestamp = strtotime( $arrUnit['date'] . ' ' . $arrUnit['draw-time'] );
        $arrUnit['lottery-base-timestamp'] = $lottery_timestamp;
        $arrUnit['lottery-timestamp'] = $lottery_timestamp;
        $arrUnit['system-time'] = time() + $offsetTimezone * 3600;
        if ( $arrUnit['discount-amount'] > 0 )
            $arrRet[] = $arrUnit;
    }
    usort($arrRet, 'sortByOrder');

    return $arrRet;
}

//= obtain token for exsting user
function obtainToken($user_id){
    global $cfg;
    
    $end_url = $cfg['lottosend_api_url'] . $user_id . '/token';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $end_url );
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Token token="' . $cfg['web_signup_token'] . '"'
    ));
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $server_output = curl_exec($ch);
    $json = json_decode($server_output,true);
    if ($json['message'] == null){ 
        $json = $json['token']; 
    }

    // if you wish to return the value replace this line with return $json    
    $newURL= $cfg['auto_login_url'] . '?auth_token=' . $json . '&location=deposit';
    header('Location: '.$newURL);
    die();
}

// get all users info
function GetUsersInfo(){
    global $cfg;
    // you can add limit to the query also
    // timestamp UNIX time
    $end_url = $cfg['lottosend_api_url'] . '?last_synced_timestamp=1';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $end_url );
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Token token="' . $cfg['web_signup_token'] . '"'
    ));
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $server_output = curl_exec($ch);
    $json = json_decode($server_output,true);
    return $json;
}

// get all users info
function GetUsersTransactions(){
    global $cfg;
    // you can add limit to the query also
    // timestamp UNIX time
    $end_url = $cfg['lottosend_api_url'] . '/transactions' . '?last_synced_timestamp=1';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $end_url );
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Token token="' . $cfg['web_signup_token'] . '"'
    ));
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $server_output = curl_exec($ch);
    $json = json_decode($server_output,true);
    return $json;
}

//= helpers
function xml2array ( $xml ){
    $xml = simplexml_load_string($xml);
    $json = json_encode($xml);
    $array = json_decode($json,TRUE);
    return $array;
}
function sortByOrder($a, $b) {
    return $b['jackpot'] - $a['jackpot'];
}
function init_timezone(){
    date_default_timezone_set('GMT');
}
function getTimezoneOffset( $timezone ){
    $timezone = str_replace('(', '', $timezone);
    $timezone = str_replace(')', '', $timezone);
    $arrTemp = explode(' ', $timezone);
    $timezoneOffset = str_replace('GMT', '', $arrTemp[0]);
    return (int)$timezoneOffset;
}