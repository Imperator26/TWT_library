<?php

require_once("classes/telegram_class.php");
require_once("classes/logger_class.php");
require_once("classes/database_class.php");

// Set the Token
define("BOT_TOKEN", "370558726:AAFNCXXhNjdJ2exQUdP5kNIkA8rlWI2evHQ");
// Set the DEBUG_MODE for the Logger
define("DEBUG_MODE", true);

// Initialize the Logger and the Main Telegram Class
$Logger = new Logger(DEBUG_MODE);
$Telegram = new Telegram(BOT_TOKEN);
$Database = new Database();

$users = $Database->executeQuery("SELECT * FROM wt_users");
while ($user = $users->fetch_array()) {
	$user = $Database->executeQuery("SELECT * FROM wt_users WHERE user_id = ?", "i", $user["user_id"]);
	$user = $user->fetch_array();
	
	// Increase tiredness
	if ($user["status"] == "sleeping" or $user["status"] == "meditating") $tiredness = $user["tiredness"];
	else $tiredness = $user["tiredness"] + 10;
	
	if ($tiredness >= 1000) {
		$tiredness = rand(250, 350);
		
		$Database->executeQuery("UPDATE wt_users SET status = ?, status_time = ? WHERE user_id = ?", "sii", array("sleeping", time()+rand(45, 75)*60, $user["user_id"]));
		
		$text = "Sei crollato a terra per la stanchezza! Ti risveglierai tra un po'.";
		$content = array('chat_id' => $user["user_id"], 'text' => $text, 'parse_mode' => "HTML"); 
		$Telegram->sendMessage($content);
	}
	
	// Increase sobriety
	$sobriety = $user["sobriety"] + 5;
	if ($sobriety > 100) $sobriety = 100;
	
	$Database->executeQuery("UPDATE wt_users SET tiredness = ?, sobriety = ? WHERE user_id = ?", "iii", array($tiredness, $sobriety, $user["user_id"]));
}
