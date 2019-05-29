<?php

// Load every auxiliary file
require_once("classes/telegram_class.php");
require_once("classes/logger_class.php");
require_once("classes/database_class.php");
require_once("classes/editor_class.php");
require_once("classes/terrain_class.php");
require_once("classes/quest_class.php");
require_once("classes/npc_class.php");

/*require_once("database/admin.php");
require_once("database/users.php");
require_once("database/objects.php");
require_once("php/utils.php");*/

// Set the Token
define("BOT_TOKEN", "");
// Set the DEBUG_MODE for the Logger
define("DEBUG_MODE", true);
// Set the MAINTENANCE status
define("MAINTENANCE", false);


// Initialize the Logger and the Main Telegram Class
$Logger = new Logger(DEBUG_MODE);
$Telegram = new Telegram(BOT_TOKEN);
$Editor = new Editor();
$Quest = new Quest();


// Standard keyboard
$keyboard = array(
    array("", "🔼", ""),
    array("◀️", "👀", "▶️"),
    array("", "🔽", "")
);
$keyboard_markup = $Telegram->replyKeyboardMarkup($keyboard, true);


// Let the magic begin
$Logger->logRegular(var_export($Telegram->getData(), true));

if ($Telegram->Message()) {
    $Editor->setEditorId($Telegram->User_Id());
    $Editor->setUsername($Telegram->Username());

    if (MAINTENANCE && $Editor->getEditorId() != 118557634) {
        $text = "Il bot è in manutenzione.";
        $content = array('chat_id' => $Editor->getEditorId(), 'text' => $text);
        $Telegram->sendMessage($content);
        die();
    }
} elseif ($Telegram->Callback_Query()) {
    $Editor->setEditorId($Telegram->Callback_User_Id());
    $Editor->setUsername($Telegram->Callback_Username());

    //if ($Telegram->Type() == "group" || $Telegram->Type() == "supergroup") $group_chat_id = $Telegram->Callback_Chat_Id();
}

// Get user or add it
$Logger->logMethod("Getting user data.");
if (!$Editor->getEditor()) {
    $Logger->logMethod("Adding new user.");

    $Editor->addEditor();

    $text = "Per poter diventare un <i>editor</i> devi prima essere stato accettato dal <b>Gran Pinguino</b>.";
    $content = array('chat_id' => $Editor->getEditorId(), 'text' => $text, 'parse_mode' => "HTML");
    $Telegram->sendMessage($content);
}

// Check if the username has changed since the last time
if ($Editor->getUsername() != $Telegram->Username()) {
    $Editor->setUsername($Telegram->Username());
    $Editor->updateUsername();
}
$Logger->logRegular("Username: ".$Editor->getUsername());

// Load terrain
$Logger->logMethod("Loading terrain.");
$Terrain = new Terrain($Editor->getTerrain());
// Get number of NPCs than create array of NPCs objects
$NPCs_id = $Terrain->getNPCsId();
$NPCs = [];
for ($i = 0; $i < count($NPCs_id); $i++) $NPCs[$i] = new NPC($NPCs_id[$i]);

// Start the analysis
// Private chat
if ($Telegram->Type() == "private") {
    $Logger->logRegular("Loading analyzePrivateMessage.php");
    require_once("methods/analyzePrivateMessage.php");
    analyzePrivateMessage($Logger, $Telegram, $Editor, $Terrain, $Quest, $NPCs, $keyboard_markup);
//Groups and supergroups
} elseif ($Telegram->Type() == "group" || $Telegram->Type() == "supergroup") {
	$Logger->logRegular("Loading analyzeGroupMessage.php");
	require_once("methods/analyzeGroupMessage.php");
    analyzeGroupMessage($Logger, $Telegram, $Editor, $Terrain, $Quest);

// Callbacks
} elseif ($Telegram->Callback_Query()) {
	$Logger->logRegular("Loading analyzeCallbackQuery.php");
    require_once("methods/analyzeCallbackQuery.php");
    analyzeCallbackQuery($Logger, $Telegram, $Editor, $Terrain, $Quest, $keyboard_markup);
}
// Analize every other message
//require_once("everyOtherMessage.php");
//everyOtherMessage($Logger, $Telegram, $Editor);

$Logger->logRegular(var_export($Telegram->getReply(), true));
