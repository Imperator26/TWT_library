<?php

// Load every auxiliary file
require_once("classes/telegram_class.php");
require_once("classes/logger_class.php");
require_once("classes/database_class.php");
require_once("classes/user_class.php");
require_once("classes/terrain_class.php");
require_once("classes/quest_class.php");
require_once("classes/npc_class.php");
require_once("classes/creature_class.php");

/*require_once("database/admin.php");
require_once("database/users.php");
require_once("database/objects.php");
require_once("php/utils.php");*/

// Set the Token
define("BOT_TOKEN", "");
// Set the DEBUG_MODE for the Logger
define("DEBUG_MODE", true);
// Set the MAINTENANCE status
define("MAINTENANCE", true);


// Initialize the Logger and the Main Telegram Class
$Logger = new Logger(DEBUG_MODE);
$Telegram = new Telegram(BOT_TOKEN);
$User = new User();
$Quest = new Quest();


// Standard keyboard
$keyboard = array(
    array("♥️", "🔼", "📜"),
    array("◀️", "👀", "🗣", "▶️"),
    array("🎒", "🔽", "📯"),
    array("⚔️", "🤝"),
    array("✉️", "📰", "⚙️")
);
$keyboard_markup = $Telegram->replyKeyboardMarkup($keyboard, true);


// Let the magic begin
$Logger->logRegular(var_export($Telegram->getData(), true));

if ($Telegram->Message()) {
    $User->setUserId($Telegram->User_Id());
    $User->setUsername($Telegram->Username());
    $User->setLanguageCode($Telegram->Language_Code());

    if (MAINTENANCE && ($User->getUserId() != 118557634 and $User->getUserId() != 23712758)) {
        $text = "Il bot è in manutenzione.";
        $content = array('chat_id' => $User->getUserId(), 'text' => $text);
        $Telegram->sendMessage($content);
        die();
    }
} elseif ($Telegram->Callback_Query()) {
    $User->setUserId($Telegram->Callback_User_Id());
    $User->setUsername($Telegram->Callback_Username());
    $User->setLanguageCode($Telegram->Callback_Language_Code());

    //if ($Telegram->Type() == "group" || $Telegram->Type() == "supergroup") $group_chat_id = $Telegram->Callback_Chat_Id();
} elseif ($Telegram->Inline_Query()) {
    require_once("sendInvitation.php");
    sendInvitation($Logger, $Telegram, $User);
    die();
}

// Get user or add it
$Logger->logMethod("Getting user data.");
if (!$User->getUser()) {
    $Logger->logMethod("Adding new user.");

    $User->addUser();
}

// Check if the username has changed since the last time
if ($Telegram->Username()) checkForUpdatedUsername($User, $Telegram->Username());
else checkForUpdatedUsername($User, $Telegram->Callback_Username());
$Logger->logRegular("Username: ".$User->getUsername());

// Set ethnicity
if (!$User->getEthnicity()) {
	$Logger->logRegular("Loading chooseEthnicity.php");
    require_once("methods/chooseEthnicity.php");
    if ($Telegram->Callback_Query()) updateEthnicity($Logger, $Telegram, $User, $Telegram->Callback_Data(), $keyboard_markup);
	else chooseEthnicity($Logger, $Telegram, $User);
	return;
}

if ($User->getStatus() == "meditating" and $Telegram->Callback_Data() != "meditation_end") {
	$text = "☸️ Al momento stai meditando. Vuoi smettere?";
	$in_keyb_buttons = array(array($Telegram->inlineKeyboardButton("Interrompi ❌", "", "meditation_end", "")));
    $inline_keyb = $Telegram->inlineKeyboardMarkup($in_keyb_buttons);
	$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML", 'reply_markup' => $inline_keyb);
	$Telegram->sendMessage($content);
	return;
}

if ($User->getStatus() == "sleeping" and $Telegram->Callback_Data() != "wakeup") {
	$text = "Stai dormendo.";
	//$in_keyb_buttons = array(array($Telegram->inlineKeyboardButton("Interrompi ❌", "", "meditation_end", "")));
    //$inline_keyb = $Telegram->inlineKeyboardMarkup($in_keyb_buttons);
	$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML"/*', reply_markup' => $inline_keyb*/);
	$Telegram->sendMessage($content);
	return;
}

// Load terrain
$Logger->logMethod("Loading terrain.");
$Terrain = new Terrain($User->getTerrain(null));

// Get number of NPCs than create array of NPCs objects
$NPCs_id = $Terrain->getNPCsId();
$NPCs = [];
for ($i = 0; $i < count($NPCs_id); $i++) $NPCs[$i] = new NPC($NPCs_id[$i]);

// Start the analysis
// Private chat
if ($Telegram->Type() == "private") {
    $Logger->logRegular("Loading analyzePrivateMessage.php");
    require_once("methods/analyzePrivateMessage.php");
    analyzePrivateMessage($Logger, $Telegram, $User, $Terrain, $Quest, $NPCs, $keyboard_markup);
//Groups and supergroups
} elseif ($Telegram->Type() == "group" || $Telegram->Type() == "supergroup") {
	$Logger->logRegular("Loading analyzeGroupMessage.php");
	require_once("methods/analyzeGroupMessage.php");
    analyzeGroupMessage($Logger, $Telegram, $User, $Terrain, $Quest);
// Callbacks
} elseif ($Telegram->Callback_Query()) {
	$Logger->logRegular("Loading analyzeCallbackQuery.php");
    require_once("methods/analyzeCallbackQuery.php");
    analyzeCallbackQuery($Logger, $Telegram, $User, $Terrain, $Quest, $keyboard_markup);
}
// Analize every other message
//require_once("everyOtherMessage.php");
//everyOtherMessage($Logger, $Telegram, $User);

$Logger->logRegular(var_export($Telegram->getReply(), true));

function checkForUpdatedUsername($User, $username) {
	if ($User->getUsername() != $username) {
    	$User->setUsername($username);
    	$User->updateUsername();
	}
}
