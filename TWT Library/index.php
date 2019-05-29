<?php

// Load every auxiliary file
require_once("classes/telegram_class.php");
require_once("classes/logger_class.php");
require_once("classes/database_class.php");

// Set the Token
define("BOT_TOKEN", "");
// Set the DEBUG_MODE for the Logger
define("DEBUG_MODE", true);
// Set the MAINTENANCE status
define("MAINTENANCE", false);


// Initialize the Logger and the Main Telegram Class
$Logger = new Logger(DEBUG_MODE);
$Telegram = new Telegram(BOT_TOKEN);


// Let the magic begin
$Logger->logRegular(var_export($Telegram->getData(), true));

if ($Telegram->Message()) {
    if (MAINTENANCE && $Telegram->User_Id() != 118557634) {
        $text = "Il bot è in manutenzione.";
        $content = array('chat_id' => $Telegram->User_Id(), 'text' => $text);
        $Telegram->sendMessage($content);
        die();
    }
}

// Start the analysis
// Private chat
if ($Telegram->Type() == "private") {
    $Logger->logRegular("Loading analyzePrivateMessage.php");
    require_once("methods/analyzePrivateMessage.php");
    analyzePrivateMessage($Logger, $Telegram);
//Groups and supergroups
} elseif ($Telegram->Type() == "group" || $Telegram->Type() == "supergroup") {
	$Logger->logRegular("Loading analyzeGroupMessage.php");
	require_once("methods/analyzeGroupMessage.php");
    analyzeGroupMessage($Logger, $Telegram);

// Callbacks
} elseif ($Telegram->Callback_Query()) {
	$Logger->logRegular("Loading analyzeCallbackQuery.php");
    require_once("methods/analyzeCallbackQuery.php");
    analyzeCallbackQuery($Logger, $Telegram);
}
// Analize every other message
//require_once("everyOtherMessage.php");
//everyOtherMessage($Logger, $Telegram, $Editor);

$Logger->logRegular(var_export($Telegram->getReply(), true));
