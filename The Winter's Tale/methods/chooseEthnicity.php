<?php

function chooseEthnicity($Logger, $Telegram, $User)
{
	$Logger->logMethod("Choosing ethnicity.");
	
	switch ($Telegram->Text()) {
		case "Hellish":
		case "Torwars":
		case "Sphenixidis":
		case "Magixians":
		case "Wingans":
		case "Pyrabors":
		case "Quharani":
		case "Oniwans":
		case "Altimos":
			$result = $User->executeQuery("SELECT * FROM wt_ethnicities WHERE name = ?", "s", $Telegram->Text());
			$ethnicity = $result->fetch_array();
			
			$text = "<b>".$ethnicity["name"]."</b>\n";
			$text .= $ethnicity["description"]."\n\n";
			
			$text .= "♥️ Stamina: <b>".$ethnicity["stamina"]."</b>\n";
			$text .= "⚡️ Mana: <b>".$ethnicity["mana"]."</b>\n";
			$text .= "🗡 Danno: <b>".$ethnicity["damage"]."</b>\n";
			$text .= "🛡 Difesa: <b>".$ethnicity["defense"]."</b>\n";
			$text .= "‼️ Critico: <b>".($ethnicity["critical"]/100)."%</b>\n";
			$text .= "🏋️‍♀️ Peso trasportabile: <b>".$ethnicity["weight"]."</b>\n";
			
			$in_keyb_buttons = array(array($Telegram->inlineKeyboardButton("Scegli! ✔️", "", $Telegram->Text(), "")));
            $inline_keyb = $Telegram->inlineKeyboardMarkup($in_keyb_buttons);
			
			$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML", "reply_markup" => $inline_keyb);
    		$Telegram->sendMessage($content);
			break;
		
		default:
			$text = "Seleziona l'etnia a cui vuoi appartenere: ";
	
			$result = $User->executeQuery("SELECT * FROM wt_ethnicities", "", null);
	
			$keyboard = [];
			while ($row = $result->fetch_array()) $keyboard[] = array($row["name"]);
			$keyboard_markup = $Telegram->replyKeyboardMarkup($keyboard, true);
	
    		$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML", "reply_markup" => $keyboard_markup);
    		$Telegram->sendMessage($content);
			break;
	}
}

function updateEthnicity($Logger, $Telegram, $User, $name, $keyboard_markup)
{
	$Logger->logMethod("Updating ethnicity.");
	
	$content = array('callback_query_id' => $Telegram->Callback_Id());
    $Telegram->answerCallbackQuery($content);
    
	$result = $User->executeQuery("SELECT * FROM wt_ethnicities WHERE name = ?", "s", $name);
	$row = $result->fetch_array();
	
	$User->executeQuery(
		"UPDATE wt_users SET ethnicity = ?, stamina = ?, stamina_max = ?, mana = ?, mana_max = ?, damage = ?, defense = ?, critical = ?, weight = ?, weight_max = ? WHERE user_id = ?",
		"iiiiiiiiiii",
		array(
			$row["ethnicity_id"],
			$row["stamina"],
			$row["stamina"],
			$row["mana"],
			$row["mana"],
			$row["damage"],
			$row["defense"],
			$row["critical"],
			$row["weight"],
			$row["weight"],
			$User->getUserId()
		)
	);
	
	$text = "Benvenuto nella provincia di <b>Eskon</b>, avventuriero!\n\nUtilizza le frecce per muoverti.";
    $content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML", "reply_markup" => $keyboard_markup);
    $Telegram->sendMessage($content);
}
