<?php
header('Access-Control-Allow-Origin: *');
date_default_timezone_set("Asia/Kolkata");
$telegram_token="1777144788:AAEv-bSSPfEwFgQP9EAHGgcR0FWiis_UYHM";
// echo 1;exit;
function dateFormat($date){
    $months = json_decode('{"01":"Jan","02":"Feb","03":"March","04":"Apr","05":"May","06":"Jun","07":"Jul","08":"Aug","09":"Sep","10":"Oct","11":"Nov","12":"Dec"}',true);
    $date = explode("-",$date);
    $d = $date[0];
    $m = $date[1];
    $y = $date[2];
    
    $d = $d;
    $m = $months[$m];
    return $d."-".$m;
}
function isJson($string) {
    json_decode($string);
    return json_last_error() === JSON_ERROR_NONE;
}
function str_rsplit($string, $length)
{
    // splits a string "starting" at the end, so any left over (small chunk) is at the beginning of the array.
    if ( !$length ) { return false; }
    if ( $length > 0 ) { return str_split($string,$length); }    // normal split
 
    $l = strlen($string);
    $length = min(-$length,$l);
    $mod = $l % $length;
 
    if ( !$mod ) { return str_split($string,$length); }    // even/max-length split
 
    // split
    return array_merge(array(substr($string,0,$mod)), str_split(substr($string,$mod),$length));
}
function tele_send($method, $data)
{
    global $telegram_token;
    $url = "https://api.telegram.org/bot".$telegram_token."/" . $method;

    $curld = curl_init();
    curl_setopt($curld, CURLOPT_POST, true);
    curl_setopt($curld, CURLOPT_CAINFO, "C:\cacert.pem");
    curl_setopt($curld, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curld, CURLOPT_URL, $url);
    curl_setopt($curld, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($curld);
    curl_close($curld);
    return $output;
}

function covid_api($url){
    $url='https://cdn-api.co-vin.in/api/v2/'.$url;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 0);
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CAINFO, "C:\cacert.pem");
    
    $headers = array();
    $headers[] = 'Accept-Language: hi_IN';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    $result = curl_exec($ch);
    if (curl_errno($ch)) {  $result = 'Error:' . curl_error($ch);   }
    curl_close($ch);
    return $result;
}

function vaccine_status_data($pincode){ return covid_api('appointment/sessions/public/calendarByPin?pincode='.$pincode.'&date='.date('d-m-Y',strtotime("0 days"))); }
function vaccine_center_data($lat,$long){   return covid_api('appointment/centers/public/findByLatLong?lat='.$lat.'&long='.$long);  }


$path = "https://api.telegram.org/bot".$telegram_token;

$receied=json_decode(file_get_contents("php://input"),TRUE);

$received_data="\n\n---------------------------------- ".date('d-M-Y H:i:s')." ----------------------------------\n".json_encode($receied,JSON_PRETTY_PRINT);

$callback=0;

if(isset($receied['callback_query'])) {
    $callback=1;
    $update=$receied['callback_query'];
    $name = $update["from"]["first_name"]." ".$update["from"]["last_name"];
} else {
    $update=$receied;
    $name = $update["message"]["from"]["first_name"]." ".$update["message"]["from"]["last_name"];
}
$location=0;
$chatId = $update["message"]["chat"]["id"];
if($callback){
    $command=$update['data'];
    file_put_contents("VaccineBot_Data2.txt",$received_data,FILE_APPEND);
}
else{
    file_put_contents("VaccineBot_Data.txt",$received_data,FILE_APPEND);
    
    $command = $update["message"]["text"];
    if(array_key_exists("location",$update["message"])){
        $location=1;
        $lat=$update["message"]["location"]["latitude"];
        $long=$update["message"]["location"]["longitude"];
    }
}
$parameters=array();
$r=array();

$help_text = "Hi ".$name.", 
i am a bot and programm for reply of vaccine availbility according to your message if you send me six digit numeric pin code. I fetch data from Government Database and serve here without charge.

i can't understant what is your requirment.


Do you know? i can help you to find nearest pin code if you send me your current location. 

Kindly reply with six digit pin code to get vaccination availability status.";

$help_text  = "Hey ".$name.",\n";
$help_text .= " I am a bot and programm for automatic reply for vaccine availbility as per your requirment.\n";
$help_text .= "\n<b><i>How to search?</i></b>\n";
$help_text .= " As i am upgraded and so now you can search for vaccine by multiple way.\n";
$help_text .= " 1. Search by your Six digit area pin code Eg. <code>123456</code>\n";
$help_text .= " 2. Send me your live current location, i will search for pin code available to near you and send you.\n";
$help_text .= " 3. Search by District- Now you can send me text like <code>search by area</code> or <code>search by district</code> to search vaccine slot by area\n";
$help_text .= "\n<b><i>How i work?</i></b>\n";
$help_text .= "i collect data from government authorized API <a>https://apisetu.gov.in/</a> and serve here.\n";
$help_text .= "\n<b><i>Privacy Policy for this bot.</i></b>\n";
$help_text .= "same as government setu API policy <a>https://apisetu.gov.in/api-policy.php</a>\n";
$help_text .= "\n<b><i>My Service charge.</i></b>\n";
$help_text .= "i don't charge for this service but if you happy with my service and want to do something for me, please share this bot link with your family, friend or relatives, with my developer <code>Mr. Harendra Chauhan</code> will be happy.\n";
$help_text .= "\n<b><i>Developer</i></b>\n";
$help_text .= "Harendra Chauhan - Aligarh\n";
$help_text .= "\n<b><i>Version 2.0</i></b>\n";
$help_text .= "\n\n\n";
$help_text .= "Kindly mute notification for this bot in telegram setting, if you don't want to get distrub from this bot.";

// $parameters = array('chat_id' => -1001271560409, 'text' => $help_text, 'parse_mode' => "HTML");$r=tele_send('sendMessage', $parameters);
    
    $command = ltrim($command, '/');
    $command = strtolower($command);
    $pincode=0;preg_match('/[0-9]{6}/', $command,$pc);if(count($pc) > 0){$pincode=$pc[0];}  // Parse Pin Code from Message
    
    if($callback){
        $cmd = explode("_",$command);
        file_put_contents("test2.txt",json_encode($cmd,JSON_PRETTY_PRINT));
        if($cmd[0]=="main"){
            $state_data=covid_api("admin/location/states");
            if(isJson($state_data)){
                $kwrd=array( "inline_keyboard" => null,"resize_keyboard"=>true,"one_time_keyboard"=>true);
                
                $state_data=json_decode($state_data,true);
                $i=0;$n=1;
                foreach($state_data["states"] as $k=>$v){
                    $kwrd["inline_keyboard"][$i][] = array("text" => $v["state_name"], "callback_data" => "/goDist_".$v["state_id"]."-".$v["state_name"]."_".rand(1,99999));
                    if($n%2==0){
                        ++$i;
                    }
                    ++$n;
                }
                $mid=$update['message']['message_id'];
                $encodedKeyboard = json_encode($kwrd);
                $parameters = array('chat_id' => $chatId,'message_id' => $mid, 'text' => "Hey ".$name.", Select your state.", 'reply_markup' => $encodedKeyboard);
                $r=tele_send('editMessageText', $parameters);
                file_put_contents("test2.txt",json_encode($parameters,JSON_PRETTY_PRINT));
            }
            else{
                $parameters = array('chat_id' => $chatId, 'text' => "Sorry, unfortunately server down");$r=tele_send('sendMessage', $parameters);
            }
        }
        if($cmd[0]=="godist"){
            
            $dist_data=covid_api("admin/location/districts/".$cmd[1]);
            $state      = explode("-",$cmd[1]);
            $state_code = $state[0];
            $state_name = $state[1];
            if(isJson($dist_data)){
                $kwrd=array( "inline_keyboard" => null,"resize_keyboard"=>true,"one_time_keyboard"=>true);
                
                $dist_data=json_decode($dist_data,true);
                $i=0;$n=1;
                foreach($dist_data["districts"] as $k=>$v){
                    $kwrd["inline_keyboard"][$i][] = array("text" => $v["district_name"], "callback_data" => "/goAge_".$cmd[1]."_".$v["district_id"]."-".$v["district_name"]."_".rand(1,99999));
                    if($n%2==0){
                        ++$i;
                    }
                    ++$n;
                }
                $kwrd["inline_keyboard"][][] = array("text" => "Change State", "callback_data" => "/main_".rand(1,99999));
                
                $mid=$update['message']['message_id'];
                $encodedKeyboard = json_encode($kwrd);
                $parameters = array('chat_id' => $chatId,'message_id' => $mid, 'text' => "Hey ".$name.", Select your District in ".$state_name.".", 'reply_markup' => $encodedKeyboard);
                $r=tele_send('editMessageText', $parameters);
                file_put_contents("test2.txt",json_encode($parameters,JSON_PRETTY_PRINT));
            }
            else{
                $parameters = array('chat_id' => $chatId, 'text' => "Sorry, unfortunately server down");$r=tele_send('sendMessage', $parameters);
            }
        }
        if($cmd[0]=="goage"){
            
            $state      = explode("-",$cmd[1]);
            $state_code = $state[0];
            $state_name = $state[1];
            
            $dist      = explode("-",$cmd[2]);
            $dist_code = $state[0];
            $dist_name = $state[1];
            $kwrd = array(
                "inline_keyboard" => array(
                    array(
                        array("text" => "18+", "callback_data" => "/search_".$cmd[1]."_".$cmd[2]."_18_1_".rand(1,99999)),
                        array("text" => "45+", "callback_data" => "/search_".$cmd[1]."_".$cmd[2]."_45_1_".rand(1,99999)),
                    ),
                    array(
                        array("text" => "Change District", "callback_data" => "/goDist_".$cmd[1]."_".rand(1,99999)),
                    )
                ),
                "resize_keyboard"=>true,
                "one_time_keyboard"=>true
            );

            $mid=$update['message']['message_id'];
            $encodedKeyboard = json_encode($kwrd);
            $parameters = array('chat_id' => $chatId,'message_id' => $mid, 'text' => "Hey ".$name.", Select your age Group.\n\nPlease note:- in the next page i will show you vaccination center having minimum 1 vaccine slot availabilty.", 'reply_markup' => $encodedKeyboard);
            $r=tele_send('editMessageText', $parameters);
            file_put_contents("test2.txt",json_encode($parameters,JSON_PRETTY_PRINT));
        }
        if($cmd[0]=="search"){
            $age = $cmd[1];
            
            $state      = explode("-",$cmd[1]);
            $state_code = $state[0];
            $state_name = $state[1];
            
            $dist       = explode("-",$cmd[2]);
            $dist_code  = $dist[0];
            $dist_name  = $dist[1];
            
            $age        = intval($cmd[3]);
            $page       = intval($cmd[4]);
            
            $kwrd=array( "inline_keyboard" => null,"resize_keyboard"=>true,"one_time_keyboard"=>true);
            $res_data=covid_api("appointment/sessions/public/calendarByDistrict?district_id=".$dist_code."&date=".date('d-m-Y',strtotime("0 days")));
            if(isJson($res_data)){
                $res_data = json_decode($res_data,true);
                
                $d=null;
                foreach ($res_data["centers"] as $k => $c) {
                    foreach ($c["sessions"] as $key => $s) {
                        if($s["available_capacity"] > 0 && $s["min_age_limit"]==$age){
                            $d[$c["pincode"]][$c["name"]][] = $s;
                        }
                    }
                }
                
                if($d!=null && count($d) > 0){
                    foreach ($d as $pinKey => $pc) {
                        $res.="\n/".$pinKey;
                        foreach ($pc as $k => $c) {
                            $res.="\n   ".$k;
                            foreach ($c as $key => $s) {
                                $res.="\n       ".$s["date"]." - ".$s["vaccine"];
                                $res.="\n           DOSE 1 = ".$s["available_capacity_dose1"];
                                $res.="\n           DOSE 2 = ".$s["available_capacity_dose2"];
                            }
                        }
                    }
                }
                else{
                    $res="Sorry unfortunately there is no vaccine dose available in ".$dist_name." for ".$age."+ age group.\n\n Kindly try after some time or go back and try with other city.";
                }
            }
            else{
                $res="Sorry, unfortunately server down";
            }
            
            
            $a=str_rsplit($res,3800);
            if(count($a) >= $page){
                $res = $a[$page-1];
            }
            else{
                $res = $a[0];
                $page = 1;
            }
            if(count($a)!=1){
                $res .= "\n\nPage ".$page." of ".count($a);
            }
            $res = "hey, here is vaccine dose availability details in ".$dist_name." for ".$age."+ age as per ".date("d-m-Y h:i:sa").".\n\n".$res;
            
            if(count($a) > $page && $page!=1){ 
                $kwrd["inline_keyboard"][] = array(
                    array("text" => "Go to Back page", "callback_data" => "/search_".$cmd[1]."_".$cmd[2]."_".$age."_".($page-1)."_".rand(1,99999)),
                    array("text" => "Go to Next page", "callback_data" => "/search_".$cmd[1]."_".$cmd[2]."_".$age."_".($page+1)."_".rand(1,99999))
                );
            }
            else if(count($a) > $page){
                $kwrd["inline_keyboard"][] = array(
                    array("text" => "Change Age Group", "callback_data" => "/goage_".$cmd[1]."_".$cmd[2]."_".rand(1,99999)),
                    array("text" => "Go to Next page", "callback_data" => "/search_".$cmd[1]."_".$cmd[2]."_".$age."_".($page+1)."_".rand(1,99999))
                );
            }
            else if($page!=1){
                $kwrd["inline_keyboard"][] = array(
                    array("text" => "Go to Back page", "callback_data" => "/search_".$cmd[1]."_".$cmd[2]."_".$age."_".($page-1)."_".rand(1,99999)),
                );
            }
            else{
                $kwrd["inline_keyboard"][] = array(
                    array("text" => "Change Age Group", "callback_data" => "/goage_".$cmd[1]."_".$cmd[2]."_".rand(1,99999)),
                );
            }
            
            $mid=$update['message']['message_id'];
            $encodedKeyboard = json_encode($kwrd);
            $parameters = array('chat_id' => $chatId,'message_id' => $mid, 'text' => $res, 'reply_markup' => $encodedKeyboard);
            $r=tele_send('editMessageText', $parameters);
            file_put_contents("test2.txt",json_encode($parameters,JSON_PRETTY_PRINT));
        }
    }
    else if (strpos($command, "start") !== false || strpos($command, "help") !== false) {
        
        $parameters = array('chat_id' => $chatId, 'text' => $help_text, 'parse_mode' => "HTML");$r=tele_send('sendMessage', $parameters);
        file_put_contents("test2.txt",json_encode($r,JSON_PRETTY_PRINT));
    }
    else if((strpos($command, "address") !== false || strpos($command, "district") !== false || strpos($command, "state") !== false || strpos($command, "area") !== false || strpos($command, "search") !== false) && !strpos($command, "pin code") !== false &&  !strpos($command, "pincode") !== false){
        $state_data=covid_api("admin/location/states");
        if(isJson($state_data)){
            $kwrd=array( "inline_keyboard" => null,"resize_keyboard"=>true,"one_time_keyboard"=>true);
            
            $state_data=json_decode($state_data,true);
            $i=0;$n=1;
            foreach($state_data["states"] as $k=>$v){
                $kwrd["inline_keyboard"][$i][] = array("text" => $v["state_name"], "callback_data" => "/goDist_".$v["state_id"]."-".$v["state_name"]."_".rand(1,99999));
                if($n%2==0){
                    ++$i;
                }
                ++$n;
            }
            $mid=$update['message']['message_id'];
            $encodedKeyboard = json_encode($kwrd);
            $parameters = array('chat_id' => $chatId, 'text' => "Dear ".$name.", \n Select your state.", 'reply_markup' => $encodedKeyboard);
            $r=tele_send('sendMessage', $parameters);
            file_put_contents("test2.txt",json_encode($parameters,JSON_PRETTY_PRINT));
        }
        else{
            $parameters = array('chat_id' => $chatId, 'text' => "Sorry, unfortunately server down");$r=tele_send('sendMessage', $parameters);
        }
    }
    else if(strlen($pincode)==6){
        $vacc_data = vaccine_status_data($pincode);
        $res="";
        if(isJson($vacc_data)){
            $vacc_data=json_decode($vacc_data,true);
            $res  = "Hi ".$name.", Here is Details of Vaccine dose Availability in /".$pincode." as per ".date("d-m-Y H:i:s");
            
            if(count($vacc_data["centers"])==0){
                $res .="\n\nSorry, unfortunately i couldn't find any vaccine dose in this pin code.\nInformation on vaccination center for this PIN CODE will be made available soon.\n\n\nKindly try with another \n\n do you know? i can help you to find nearest pin code if you send me your current location.";
            }
            else{
                
            }
            
            foreach ($vacc_data["centers"] as $d => $v) {
                $res .= "\n\n".$v["name"]." | ".$v["fee_type"].""; // | ".$v["center_id"]."";
                foreach ($v["sessions"] as $k => $lst) {
                    $res.="\n   ".dateFormat($lst["date"])." | ".$lst["min_age_limit"]."+ | Dose 1 = ".$lst["available_capacity_dose1"];
                    if($lst["available_capacity_dose1"]!=$lst["available_capacity"]){
                        //$res.="\n   ".dateFormat($lst["date"])." | ".$lst["min_age_limit"]."+ | Dose 2 = ".$lst["available_capacity_dose2"];
                        $res.="\n                          | Dose 2 = ".$lst["available_capacity_dose2"];
                    }
                    
                    //$res.="\n    ".$lst["date"]." For ".$lst["min_age_limit"]."+ = ".$lst["available_capacity"];
                    //if($lst["available_capacity_dose1"]!=$lst["available_capacity"]){
                    //  $res.="\n                       Dose1: ".$lst["available_capacity_dose1"]." \n                       Dose2: ".$lst["available_capacity_dose2"];;
                    //}
                    // $res.="\n   ".$lst["min_age_limit"]."+ ".$lst["date"]." \n       Dose1: ".$lst["available_capacity_dose1"]." | Dose2: ".$lst["available_capacity_dose2"];
                }
            }
            
            $a=str_rsplit($res,4000);
            if(count($a) > 1){
                foreach ($a as $key => $value) {
                    $a[$key] .= "\n\n    --Message ".($key+1)." of ".count($a);
                    $res=$a[$key];
                    $parameters = array('chat_id' => $chatId, 'text' => $res, 'parse_mode' => "HTML");$r=tele_send('sendMessage', $parameters);
                }
            }
            else{
                $parameters = array('chat_id' => $chatId, 'text' => $res, 'parse_mode' => "HTML");$r=tele_send('sendMessage', $parameters);
            }
        }
        else{
            $parameters = array('chat_id' => $chatId, 'text' => "Sorry, unfortunately server down");$r=tele_send('sendMessage', $parameters);
        }
        file_put_contents("test2.txt",json_encode($r,JSON_PRETTY_PRINT)."\n\n".$res);
    }
    else if($location==1){
        $cent_data=vaccine_center_data($lat,$long);
        if(isJson($cent_data)){
            $cent_data = json_decode($cent_data,true);
            $c = null;
            $res  = "Hi ".$name.", Here is details of nearest vaccine center availability in your area (".$lat.",".$long.") as per ".date("d-m-Y H:i:s");
            
            if(count($cent_data["centers"])==0){
                $res .="\n\nSorry, unfortunately there is no nearest Vaccine Center Availble to your area.\n\nKindly try with another";
            }
            else{
                $res .="\nKindly click on any pin code so that i can send you availability of vaccine.";
            }
            
            foreach($cent_data["centers"] as $v){
                $c[$v["pincode"]][] = $v["name"];
            }
            foreach ($c as $key => $pc) {
                $res .= "\n/".$key;
                foreach ($pc as $k => $cname) {
                    $res .="\n  ".$cname;
                }
            }
            
            $a=str_rsplit($res,4000);
            if(count($a) > 1){
                foreach ($a as $key => $value) {
                    $a[$key] .= "\n\n    --Message ".($key+1)." of ".count($a);
                    $res=$a[$key];
                    $parameters = array('chat_id' => $chatId, 'text' => $res);$r=tele_send('sendMessage', $parameters);
                }
            }
            else{
                $parameters = array('chat_id' => $chatId, 'text' => $res);$r=tele_send('sendMessage', $parameters);
            }
        }
        else{
            $parameters = array('chat_id' => $chatId, 'text' => "Sorry, unfortunately server down");$r=tele_send('sendMessage', $parameters);
        }
    }
    else if(strlen($command)==6  || is_numeric($command)){
        $kwrd=array( "inline_keyboard" => null,"resize_keyboard"=>true,"one_time_keyboard"=>true);
        $kwrd["inline_keyboard"][][] = array("text" => "Search By Area", "callback_data" => "/main_".rand(1,99999));
        $encodedKeyboard = json_encode($kwrd);
        $parameters = array('chat_id' => $chatId, 'text' => "Invalid Pin Code.\n\nKindly reply with text <b><code>HELP</code></b> to get help or choose below button.", 'parse_mode' => "HTML", 'reply_markup' => $encodedKeyboard);$r=tele_send('sendMessage', $parameters);
    }
    else if($command!="") {
        $parameters = array('chat_id' => $chatId, 'text' => "Could not understand.\n\nKindly reply with text <b><code>HELP</code></b> to get help", 'parse_mode' => "HTML");$r=tele_send('sendMessage', $parameters);
    }
    

file_put_contents("test111.txt",json_encode(json_decode($r,TRUE),JSON_PRETTY_PRINT));