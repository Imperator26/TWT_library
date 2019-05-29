<?php

function describeTerrain($Logger, $Telegram, $User, $Terrain, $keyboard_markup)
{
	$text = "<b>".$Terrain->getName()."</b>\n";
    $text .= $Terrain->getDescription()."\n\n";
                        
    // Switch with new array then add punctuation in the middle
    $terrains = $Terrain->getTerrain("all");
	if ($terrains["north"] or $terrains["east"] or $terrains["south"] or $terrains["west"]) {
		$text .= "Passaggi percorribili:<b>";
		if ($terrains["north"]) $text .= " Nord";
		if ($terrains["west"]) $text .= " Ovest";
		if ($terrains["east"]) $text .= " Est";
		if ($terrains["south"]) $text .= " Sud";
		$text .= "</b>.\n";
	} else $text .= "Nessun passaggio praticabile.\n";


	// Update NPCs
	$NPCs_id = $Terrain->getNPCsId();
	$NPCs = [];
	for ($i = 0; $i < count($NPCs_id); $i++) $NPCs[$i] = new NPC($NPCs_id[$i]);
	
	if (count($NPCs_id)) {//switch
		$text .= "Puoi parlare con:";
		for ($i = 0; $i < count($NPCs_id); $i++) $text .= " <b>".$NPCs[$i]->getName()."</b>";
		$text .= "\n";
	}
	
	
	// Other players present
	$Logger->logMethod("Avventurieri vicini.");
    $result = $User->executeQuery("SELECT * FROM wt_users WHERE terrain = ? AND user_id <> ?", "ii", array($Terrain->getTerrain(null), $User->getUserId()));
    $text .= "Avventurieri vicini: <b>".($result->num_rows)."</b>\n";
	
	
	// Spawn creatures and print them
	$Logger->logMethod("Creature vicine.");
	$creatures = $User->executeQuery("SELECT * FROM wt_spawned_creatures WHERE terrain_id = ?", "i", $Terrain->getTerrain(null));
	$no_creatures = $creatures->num_rows;
	if ($no_creatures < 5 and $Terrain->getProbability() > rand(0, 100)) {
		if ($Terrain->getCreatureLevel()) $creature_level = $Terrain->getCreatureLevel();
		else $creature_level = $User->getLevel();
		
		$Terrain->spawnCreature(abs(rand($creature_level-2, $creature_level+3)));
		
		$no_creatures++;
	}
    $text .= "Creature presenti: <b>".($no_creatures)."</b>";
    
    $parameters = explode("_", $Terrain->getType());
    
    switch ($parameters[0]) {
    	case "regular":
			$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML", 'reply_markup' => $keyboard_markup); 
			$Telegram->sendMessage($content);
			break;
		
    	case "tavern":
    		$in_keyb_buttons = array(array($Telegram->inlineKeyboardButton("🍺 Unisciti alla conversazione!", "https://t.me/joinchat/BxELwkF3nsjvK2FLBr71Gw", "", "")));
            $inline_keyb = $Telegram->inlineKeyboardMarkup($in_keyb_buttons);
    		$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML", 'reply_markup' => $inline_keyb); 
			$Telegram->sendMessage($content);
			break;
			
		case "meditation":
			$in_keyb_buttons = array(array($Telegram->inlineKeyboardButton("Medita ☸️", "", "meditation_start", "")));
            $inline_keyb = $Telegram->inlineKeyboardMarkup($in_keyb_buttons);
    		$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML", 'reply_markup' => $inline_keyb); 
			$Telegram->sendMessage($content);
			break;
		
		case "hotel":
			if ($User->getTerrain() == $User->getHotelTerrain() and $User->getHotelBookedUntil() >= time()) {
				$in_keyb_buttons = array(
					array($Telegram->inlineKeyboardButton("✨ Fai un Pisolino 1h +25% 💤", "", "nap", "")),
					array($Telegram->inlineKeyboardButton("🛏 Dormi profondamente 4h 100% 💤", "", "sleep", ""))
				);
			} else $in_keyb_buttons = array(array($Telegram->inlineKeyboardButton("Alloggia 🏨", "", "hotel_prices", "")));
			
			$inline_keyb = $Telegram->inlineKeyboardMarkup($in_keyb_buttons);
    		$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML", 'reply_markup' => $inline_keyb); 
			$Telegram->sendMessage($content);
			break;
		
		case "house":
			if (false) {
				$in_keyb_buttons = array(array($Telegram->inlineKeyboardButton("Compra", "", "hotel_nights", "")));
				$inline_keyb = $Telegram->inlineKeyboardMarkup($in_keyb_buttons);
				$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML", 'reply_markup' => $inline_keyb); 
				$Telegram->sendMessage($content);
			}
			break;
		
		default:// Useless
			$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML", 'reply_markup' => $keyboard_markup); 
			$Telegram->sendMessage($content);
			break;
	}
}
