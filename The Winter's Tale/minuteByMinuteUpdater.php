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
	
	// Wake up
	if ($user["status"] == "sleeping" and $user["status_time"] <= time()) {
		$Database->executeQuery("UPDATE wt_users SET status = ? WHERE user_id = ?", "si", array("none", $user["user_id"]));
	
		$text = "Ti sei svegliato/a finalmente! ⏰\nProsegui la tua avventura!";
		$content = array('chat_id' => $user["user_id"], 'text' => $text, 'parse_mode' => "HTML"); 
		$Telegram->sendMessage($content);
	}
}
