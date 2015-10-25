<?php
/**
 * Telegram Bot Example whitout WebHook.
 * It uses getUpdates Telegram's API.
 * @author Gabriele Grillo <gabry.grillo@alice.it>
 * Modify by Enrico Speranza <enrico.speranza@gmail.com>
 */

include("/usr/share/vrylbotdaemon/libs/Telegram.php");
include("/usr/share/vrylbotdaemon/config.php");
require '/usr/share/vrylbotdaemon/libs/rest_curl_client.php';
require '/usr/share/vrylbotdaemon/libs/IXR_Library.php';
require_once('/usr/share/vrylbotdaemon/libs/TwitterAPIExchange.php');

//Keys
$API_KEY = API_KEY;
$bot_id = BOT_ID;

/** Set access tokens here - see: https://dev.twitter.com/apps/ **/
$settings = array(
    'oauth_access_token' => OAUTH_ACCESS_TOKEN,
    'oauth_access_token_secret' => OAUTH_ACCESS_TOKEN_SECRET,
    'consumer_key' => CONSUMER_KEY,
    'consumer_secret' => CONSUMER_SECRET
);

//Objects
$telegram = new Telegram($bot_id);

// The worker will execute every X seconds:
$seconds = 2;

// We work out the micro seconds ready to be used by the 'usleep' function.
$micro = $seconds * 1000000;

// Function start with, end with
// From: http://theoryapp.com/string-startswith-and-endswith-in-php/
// $start = 'http';
// $end = 'com';
// $str = 'http://google.com';
// str_starts_with($str, $start); // TRUE
// str_ends_with($str, $end); // TRUE 
function str_starts_with($haystack, $needle)
{
    return strpos($haystack, $needle) === 0;
}
function str_ends_with($haystack, $needle)
{
    return strrpos($haystack, $needle) + strlen($needle) ===
        strlen($haystack);
}

// Return @romamobilita Tweet
function get_romamobilita($settings)
{
	$url = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
	$getfield = '?screen_name=romamobilita';
	$requestMethod = 'GET';

	$twitter = new TwitterAPIExchange($settings);
	$response = $twitter->setGetfield($getfield)
		->buildOauth($url, $requestMethod)
		->performRequest();

	$arr_buffer = json_decode($response, true);
	//print_r($arr_buffer);
	$reply = "";
	for ($x = 0; $x <= 5; $x++) {
		if ($arr_buffer[$x]['in_reply_to_screen_name'] == NULL)
		{
			$datebuff = strtotime($arr_buffer[$x]['created_at']);
			$reply = $reply." ".date('d-m-Y',$datebuff)." ".$arr_buffer[$x]['text']."\n";
		}
	}
	return $reply;
}

// Return all data for palina
function get_palina($palina_id, $api_key)
{
	// mi autentico per ottenere il token
	$client = new IXR_Client("http://muovi.roma.it/ws/xml/autenticazione/1");
	if (!$client->query(
				'autenticazione.Accedi'
				, $api_key
				, ''
				)
		) {
			echo('<br>An error occurred - '.$client->getErrorCode().":".$client->getErrorMessage());      
		}
	$token = $client->getResponse();
	// mostra il token
	//echo "<br><br>TOKEN-> ".$token;

	// richiamo il ws per la ricerca del percorso
	$client = new IXR_Client("http://muovi.roma.it/ws/xml/paline/7");
	if (!$client->query(
				'paline.Previsioni'
				,$token // token
				, (int)$palina_id
				, "it"  // Codice della lingua in cui si vogliono esprimere le indicazioni
				)
		) {
			echo('<br>An error occurred - '.$client->getErrorCode().":".$client->getErrorMessage());      
		}
	$response = $client->getResponse();
	return $response;
}

while(true){
	// This is the code you want to loop during the service...
	$myFile = "/home/enrico/vrylbotdaemon.log";
	$fh = fopen($myFile, 'a') or die("Can't open file");
	
	// Get all the new updates and set the new correct update_id
	$req = $telegram->getUpdates();
	for ($i = 0; $i < $telegram-> UpdateCount(); $i++) {
		// You NEED to call serveUpdate before accessing the values of message in Telegram Class
		$telegram->serveUpdate($i);
		$text = $telegram->Text();
		$chat_id = $telegram->ChatID();
		
		$stringData = "Log input: ".$text." ".time(). "\n";
		fwrite($fh, $stringData);
		
		if (str_starts_with($text,"/palina")) {
			$arr = explode(" ", $text);
			if (count($arr) == 1) {
				$reply = "Comando: /palina <num_palina>";
				$content = array('chat_id' => $chat_id, 'text' => $reply);
				$telegram->sendMessage($content);
				$stringData = $chat_id." at: " . time(). "\n";
				fwrite($fh, $stringData);
			}
			if (count($arr) == 2)
			{
				//$reply = "Palina Num.: ".$arr[1];
				//Call API muoviroma etc...
				$buffer = get_palina($arr[1], $API_KEY);
				if (ctype_digit($arr[1]))
				{
					$reply = "";
					foreach($buffer['risposta']['arrivi'] as $vettura)  
					{
						$reply = $reply." ".$vettura['linea']." ". $vettura['annuncio']."\n";
					}
					$stringData = $reply." at: " . time(). "\n";
					fwrite($fh, $stringData);
					$content = array('chat_id' => $chat_id, 'text' => $reply);
					$telegram->sendMessage($content);
					$stringData = $chat_id." at: " . time(). "\n";
					fwrite($fh, $stringData);
				}
				else
				{
					$reply = "Comando: /palina <num_palina>";
					$content = array('chat_id' => $chat_id, 'text' => $reply);
					$telegram->sendMessage($content);
					$stringData = $chat_id." at: " . time(). "\n";
					fwrite($fh, $stringData);
				}
			}	
		}
		if ($text == "/romamobilita") {
			$reply = get_romamobilita($settings);
			$content = array('chat_id' => $chat_id, 'text' => $reply);
			$telegram->sendMessage($content);
			$stringData = $chat_id." at: " . time(). "\n";
			fwrite($fh, $stringData);
		}
		if ($text == "/start") {
			$reply = "Working";
			$content = array('chat_id' => $chat_id, 'text' => $reply);
			$telegram->sendMessage($content);
			$stringData = $chat_id." at: " . time(). "\n";
			fwrite($fh, $stringData);
		}
		if ($text == "/test") {
			if ($telegram->messageFromGroup()) {
				$reply = "Chat Group";
			} else {
				$reply = "Private Chat";
			}
			// Create option for the custom keyboard. Array of array string
			$option = array( array("A", "B"), array("C", "D") );
			// Get the keyboard
			$keyb = $telegram->buildKeyBoard($option);
			$content = array('chat_id' => $chat_id, 'reply_markup' => $keyb, 'text' => $reply);
			$telegram->sendMessage($content);
		}
		if ($text == "/almanac") {
			$c = new RestCurlClient();
				try {
					$url = "http://almanac.alwaysdata.net/openalmanac/getAlmanac/";
					$res = $c->get($url);
					$obj = json_decode($res, true);
					setlocale(LC_TIME, 'ita', 'it_IT');
					$mesi = array(1=>'gennaio', 'febbraio', 'marzo', 'aprile',
                	'maggio', 'giugno', 'luglio', 'agosto',
                	'settembre', 'ottobre', 'novembre','dicembre');

					$giorni = array('domenica','lunedì','martedì','mercoledì',
					                'giovedì','venerdì','sabato');

					list($sett,$giorno,$mese,$anno) = explode('-',date('w-d-n-Y'));
					$str_output = "Oggi è: ".$giorni[$sett].' '.$giorno.' '.$mesi[$mese].' '.$anno."\n";
					//setlocale(LC_TIME, 'ita', 'it_IT');
					//echo "Oggi è: ".strftime("%A %d %B %Y");
					$str_output = $str_output . "Giorno: ".(date("z")+1)." di 365\n";
					$str_output = $str_output ."Settimana numero: ".(date("W"))."\n";
					$str_output = $str_output ."Data Stellare: ".$obj[almanac][stardatecalendar]."\n";
					$str_output = $str_output ."Calendario Maya: ".$obj[almanac][mayancalendar]."\n";
					$str_output = $str_output ."Calendario Islamico: ".$obj[almanac][hijricalendar]."\n";
					$str_output = $str_output ."Calendario Cinese: Giorno ".$obj[almanac][chinesecalendar][2]." Mese ".$obj[almanac][chinesecalendar][1]."\n";
					$str_output = $str_output ."Calendario Ebraico: ".$obj[almanac][hebrewcalendar]."\n";
					$str_output = $str_output ."Fase lunare: ".$obj[almanac][moonphase][moonphase]." Illuminazione: ".$obj[almanac][moonphase][percentilluminated]."\n";
					$tmsp = strtotime($obj[almanac][easter])."\n";
					$str_output = $str_output ."Ricorrenza della Pasqua: ".date('d-m-Y',$tmsp)."\n";
					$str_output = $str_output ."Santo del giorno: ".$obj[almanac][saintofday][0][Name]." (".$obj[almanac][saintofday][0][Description].")"."\n";
					$str_output = $str_output ."Proverbio del giorno: ".$obj[almanac][proverbofday]."\n";
					//var_dump($obj);
					$reply = $str_output;
					// Build the reply array
					$content = array('chat_id' => $chat_id, 'text' => $reply);
					$telegram->sendMessage($content);
				}
				catch (Exception $e) {
    				echo 'Eccezione rilevata: ',  $e->getMessage(), "\n";
				}
		}
		if ($text == "/git") {
			$reply = "Check me on GitHub: https://github.com/Eleirbag89/TelegramBotPHP";
			// Build the reply array
			$content = array('chat_id' => $chat_id, 'text' => $reply);
			$telegram->sendMessage($content);
		}
	}
	
	$stringData = "File updated at: " . time(). "\n";
	fwrite($fh, $stringData);
	fclose($fh);
	
	// Now before we 'cycle' again, we'll sleep for a bit...
	usleep($micro);
}

?>
