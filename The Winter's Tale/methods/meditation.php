<?php

function startMeditation($Telegram, $User, $Terrain)
{
	if ($User->getStatus() == "meditating") {
		$text = "Stai già meditando.";
		$in_keyb_buttons = array(array($Telegram->inlineKeyboardButton("Interrompi ❌", "", "meditation_end", "")));
 	    $inline_keyb = $Telegram->inlineKeyboardMarkup($in_keyb_buttons);
		$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML", 'reply_markup' => $inline_keyb); 
		$Telegram->sendMessage($content);
		return;
	}
	
	if ($Terrain->getType() != "meditation") {
		$text = "Non puoi meditare qui!";
		$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML"); 
		$Telegram->sendMessage($content);
		return;
	}
	
	$User->updateStatus("meditating");
	
	$User->executeQuery("UPDATE wt_users SET status_time = ? WHERE user_id = ?", "ii", array(time(), $User->getUserId()));
    
	$text = "☸️ Ti siedi e liberi la mente da ogni pensiero.";
	$in_keyb_buttons = array(array($Telegram->inlineKeyboardButton("Interrompi ❌", "", "meditation_end", "")));
    $inline_keyb = $Telegram->inlineKeyboardMarkup($in_keyb_buttons);
	$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML", 'reply_markup' => $inline_keyb); 
	$Telegram->sendMessage($content);
}

function endMeditation($Telegram, $User)
{
	if ($User->getStatus() != "meditating") {
		$text = "Non stai meditando al momento.";
		$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML"); 
		$Telegram->sendMessage($content);
		return;
	}
	
	$User->updateStatus("none");
	
	$result = $User->executeQuery("SELECT * FROM wt_users WHERE user_id = ?", "i", $User->getUserId());
    $user = $result->fetch_array();
    
    $points = $user["meditation_points"] + time() - $user["status_time"];
    
    if ($points >= 150*3600) {
    	$User->executeQuery("UPDATE wt_users SET icy_diamonds = ? WHERE user_id = ?", "ii", array($user["icy_diamonds"]+1, $User->getUserId()));
    	
    	$points -= 150*3600;
    	
    	$text = "Hai fatto un passo in più verso il raggiungimento del Nirvana! Hai guadagnato un 💎.";
		$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML"); 
		$Telegram->sendMessage($content);
    }
    
    /*else*/$User->executeQuery("UPDATE wt_users SET meditation_points = ? WHERE user_id = ?", "ii", array($points, $User->getUserId()));
    
	$text = "☸️ Lentamente apri gli occhi e ti alzi. Ti senti rinvigorito/a!";
	$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML"); 
	$Telegram->sendMessage($content);
}
