<?php

function checkPlace($Telegram, $User, $Terrain) {
	if ($Terrain->getType() == "hotel") {
		if ($User->getHotelBookedUntil() > time()) return true;
		
		$text = "Prenota una stanza per dormire!";
		$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML"); 
		$Telegram->sendMessage($content);
		return false;
	} else if ($Terrain->getType() == "house") {// check if the house has been bought
		$text = "Compra la casa per dormire!";
		$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML"); 
		$Telegram->sendMessage($content);
		return false;
	} else {
		$text = "Trova una stanza per dormire!";
		$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML"); 
		$Telegram->sendMessage($content);
		return false;
	}
	return true;
}

function startSleeping($Telegram, $User, $Terrain, $tiredness, $wake_up_time) {
	if ($User->getStatus() == "sleeping") {
		$text = "Stai già dormendo.";
		//$in_keyb_buttons = array(array($Telegram->inlineKeyboardButton("Interrompi ❌", "", "meditation_end", "")));
 	    //$inline_keyb = $Telegram->inlineKeyboardMarkup($in_keyb_buttons);
		$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML"/*, 'reply_markup' => $inline_keyb*/);
		$Telegram->sendMessage($content);
		return;
	}
	
	if (!checkPlace($Telegram, $User, $Terrain)) return;
	
	$User->updateStatus("sleeping");
	
	if ($tiredness < 0) $tiredness = 0;
	$User->executeQuery("UPDATE wt_users SET status_time = ?, tiredness = ? WHERE user_id = ?", "iii", array($wake_up_time, $tiredness, $User->getUserId()));
    
	$text = "Ti sdrai, chiudi gli occhi e aspetti che il sonno ti rapisca.\nTi sveglierai verso le: <b>".date("H:i", $wake_up_time)."</b>";
	//$in_keyb_buttons = array(array($Telegram->inlineKeyboardButton("Interrompi ❌", "", "meditation_end", "")));
    //$inline_keyb = $Telegram->inlineKeyboardMarkup($in_keyb_buttons);
	$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML"/*, 'reply_markup' => $inline_keyb*/); 
	$Telegram->sendMessage($content);
}

function wakeUp() {
	return;
}
