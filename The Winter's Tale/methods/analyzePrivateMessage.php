<?
function analyzePrivateMessage($Logger, $Telegram, $User, $Terrain, $Quest, $NPCs, $keyboard_markup) {
	$Logger->logMethod("Analyzing text from private.");

    // Status: none
    switch ($User->getStatus()) {
        case "none":
        	
        	
            $words = explode(" ", $Telegram->Text(), 2);
            switch ($words[0]) {
                case "👀":
                	require_once("methods/describeTerrain.php");
                	describeTerrain($Logger, $Telegram, $User, $Terrain, $keyboard_markup);
                    return;
                    
                case "🔼":
                    $where = "north";
                    $from = "south";
                case "▶️":
                    if (!isset($where)) { $where = "east"; $from = "west"; }
                case "🔽":
                    if (!isset($where)) { $where = "south"; $from = "north"; }
                case "◀️":
                    if (!isset($where)) { $where = "west"; $from = "east"; }
                    
                    $new_terrain = $Terrain->getTerrain($where);
                    if ($new_terrain) {
                        $User->updateTerrain($new_terrain);
                        $Terrain->loadTerrain($new_terrain);
                        
                        if ($text = $Terrain->getWelcomeMessage("wlc_msg_".$from)) {
                            $content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML");
                            $Telegram->sendMessage($content);
                        }
                        
                        require_once("methods/describeTerrain.php");
                        describeTerrain($Logger, $Telegram, $User, $Terrain, $keyboard_markup);
                    } else {
                        $text = "Non c'è nessun passaggio in quella direzione.";
                        $content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML", 'reply_markup' => $keyboard_markup); 
                        $Telegram->sendMessage($content);
                    }
                    return;
                    
                case "📜":
                	if ($User->getActiveQuest()) {
                		$Quest->loadQuest($User->getActiveQuest());
                    	$text = $Quest->getSummary();
                    	
                    	$in_keyb_buttons = array(array($Telegram->inlineKeyboardButton("Rinuncia ❌", "", "quest_withdraw", "")));                    
                    	$inline_keyb = $Telegram->inlineKeyboardMarkup($in_keyb_buttons);
                    
                    	$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML", 'reply_markup' => $inline_keyb); 
                    	$Telegram->sendMessage($content);
                    } else {
                    	$result = $User->executeQuery("SELECT * FROM wt_quests WHERE que_number = ? AND is_main = ?", "ii", array($User->getQuestQueNumber(), True));
                        if ($result->num_rows) {
                        	$quest = $result->fetch_array();
                        	
                        	$text = "Vai da <b>".NPCIdToName($User, $quest["issuer"])."</b> ha una quest da assegnarti.";
                        } else $text = "Non hai quest in corso. Guardati in giro, qualcuno di sicuro ha bisogno di un avventuriero.";
                    }
                	
                    $content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML", 'reply_markup' => $keyboard_markup); 
                    $Telegram->sendMessage($content);
                    return;
                    
                case "♥️":
                	$text = "<b>Statistiche generali:</b>\n";
                	$text .= "🎖 Livello: <b>".$User->getLevel()."</b>\n";
                	$text .= "🎓 Esp: <b>".number_format($User->getExperience(), 0, ",", ".")."</b>/".number_format($User->experienceNeededToLevelUp($User->getLevel()), 0, ",", ".")."\n";
                	$text .= "💠 Winterly: <b>".number_format($User->getMoney(), 0, ",", ".")."</b>\n";
                	$text .= "💎 Glaciali: <b>".$User->getIcyDiamonds()."</b>\n\n";
                	
                	$text .= "<b>Statistiche di stato:</b>\n";
                	$text .= "♥️ Stamina: <b>".$User->getStamina()."</b>/".$User->getStaminaMax()."\n";
                	$text .= "⚡️ Mana: <b>".$User->getMana()."</b>/".$User->getManaMax()."\n";
                	$text .= "💤 Stanchezza: <b>".($User->getTiredness()/10)."%</b>\n";
                	$text .= "🍺 Sobrietà: <b>".$User->getSobriety()."%</b>\n\n";
                	
                	$text .= "<b>Statistiche combattimento:</b>\n";
                	$text .= "🗡 Danno: <b>".$User->getDamage()."</b>\n";
                	$text .= "🛡 Difesa: <b>".$User->getDefense()."</b>\n";
                	$text .= "‼️ Critico: <b>".($User->getCritical()/100)."%</b>\n";
                	
                    $content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML", 'reply_markup' => $keyboard_markup); 
                    $Telegram->sendMessage($content);
                    return;
                    
                case "🎒":
                	$text = "Zainetto dell'avventuriero: \n";
                	$result = $User->executeQuery("SELECT * FROM wt_backpacks WHERE user_id = ?", "i", $User->getUserId());
                	if (!$result->num_rows) $text = "Il tuo zainetto è vuoto.";
                	else while ($row = $result->fetch_array()) $text .= $row["name"]." (<b>".$row["quantity"]."</b>)\n";
                	
                	$in_keyb_buttons = array(
                		array($Telegram->inlineKeyboardButton("Armi 🗡", "", "weapons", ""), $Telegram->inlineKeyboardButton("Corazze 👑", "", "armors", ""), $Telegram->inlineKeyboardButton("Scudi 🛡", "", "shields", "")),
                		array($Telegram->inlineKeyboardButton("Equipaggiamento 👟", "", "equipped", "")),
                		array($Telegram->inlineKeyboardButton("Incantesimi 🔥", "", "spells", "")),
                		array($Telegram->inlineKeyboardButton("Cibo 🍕", "", "food", ""), $Telegram->inlineKeyboardButton("Bevande 🍺", "", "drinks", ""))
                	);
                    $inline_keyb = $Telegram->inlineKeyboardMarkup($in_keyb_buttons);
                	
                	$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML", 'reply_markup' => $inline_keyb); 
                    $Telegram->sendMessage($content);
                	return;
                    
                case "⚙️":
                    return;
                    
                case "🗣":
                    if (count($NPCs) == 0) {
                        $text = "Non c'è nessuno con cui parlare!";
                        $content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML", 'reply_markup' => $keyboard_markup); 
                        $Telegram->sendMessage($content);
                    } else if (count($NPCs) == 1) {
                        $text = "<b>".$NPCs[0]->getName()."</b>\n";
                        $text .= $NPCs[0]->getMessage()."\n";
                        
                        // Check if there's any quest available
                        $result = $Quest->executeQuery("SELECT * FROM wt_quests WHERE issuer = ? ORDER BY que_number DESC", "i", $NPCs[0]->getNPCId());
                        $quest_ids = [];
                        while($row = $result->fetch_array()) $quest_ids[] = $row["quest_id"];
                        
                        $result = $Quest->executeQuery("SELECT * FROM wt_completed_quests WHERE issuer = ? AND user_id = ? ORDER BY que_number DESC", "ii", array($NPCs[0]->getNPCId(), $User->getUserId()));
                        $completed_quest_ids = [];
                        while($row = $result->fetch_array()) $completed_quest_ids[] = $row["quest_id"];
                        
                        $available_quest_ids = array_diff($quest_ids, $completed_quest_ids);
                        
                        $in_keyb_buttons = [];
                        
                        // Quest: available quests
                        if (count($available_quest_ids)) {
                        	$Quest->loadQuest($available_quest_ids[0]);
                        	
                        	if ($Quest->getName()) $button_text = "📜 ".$Quest->getName();
                        	else $button_text = "Quest 📜";
                        	
                            $in_keyb_buttons[] = array($Telegram->inlineKeyboardButton($button_text, "", "quest_preview_".$Quest->getQuestId(), ""));
                        }
                        
                        // Quest: give type
                        if ($User->getActiveQuest()) {
                        	$Quest->loadQuest($User->getActiveQuest());
                        	
                        	$parameters = explode("&", $Quest->getParameters());
                        	$npc_id = $parameters[0];
                        	
                        	if ($Quest->getType() == "give" and $npc_id == $NPCs[0]->getNPCId()) {
                        		$in_keyb_buttons[] = array($Telegram->inlineKeyboardButton("🤜 Dai", "", "quest_give_".$Quest->getQuestId(), ""));
                        	}
                        }
                        
                        // NPC's special 
                        switch ($NPCs[0]->getType()) {
                        	case "regular":
                        		break;
                        		
                        	case "market":
                        		$in_keyb_buttons[] = array(
                        			$Telegram->inlineKeyboardButton("Compra 🛒", "", "market_buy_".$NPCs[0]->getNPCId(), ""),
                        			$Telegram->inlineKeyboardButton("Vendi 💰", "", "market_sell_".$NPCs[0]->getNPCId(), "")
                        		);
                        		break;
                        	
                        	case "bar":
                        		$in_keyb_buttons[] = array(
                        			$Telegram->inlineKeyboardButton("Compra 🛒", "", "market_buy_".$NPCs[0]->getNPCId(), "")
                        		);
                        		break;
                        	
                        	case "halloffame":
                        		$in_keyb_buttons[] = array(
                        			$Telegram->inlineKeyboardButton("Livello 🎖", "", "charts_level_".$NPCs[0]->getNPCId(), ""),
                        			$Telegram->inlineKeyboardButton("Winterly ", "", "charts_money_".$NPCs[0]->getNPCId(), "")
                        		);
                        		$in_keyb_buttons[] = array(
                        			$Telegram->inlineKeyboardButton("Quest completate ", "", "charts_quests_".$NPCs[0]->getNPCId(), ""),
                        			$Telegram->inlineKeyboardButton("Creature uccise ", "", "charts_creature_".$NPCs[0]->getNPCId(), "")
                        		);
                        		break;
                        }
                        
                        $inline_keyb = $Telegram->inlineKeyboardMarkup($in_keyb_buttons);
                        $content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML", 'reply_markup' => $inline_keyb);
                        $Telegram->sendMessage($content);
                        
                        // Check for giveaway
                        $Logger->logMethod("Check for giveaway.");
                        $giveaway = $User->executeQuery("SELECT * FROM wt_giveaways WHERE issuer = ?", "i", $NPCs[0]->getNPCId());
                        if ($giveaway->num_rows) {
                        	$giveaway = $giveaway->fetch_array();
                			
                        	$result = $User->executeQuery("SELECT * FROM wt_redeemed_giveaways WHERE giveaway_id = ? AND user_id = ?", "ii", array($giveaway["giveaway_id"], $User->getUserId()));
                        	
                        	if (!$result->num_rows) {
                        		$text = "<b>".$NPCs[0]->getName().": </b> ".$giveaway["message"];
                        		$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML");
                        		$Telegram->sendMessage($content);
                        		
                        		$User->addItemToBackpack($giveaway["item_id"], $giveaway["rarity"], $giveaway["level"], $giveaway["quantity"]);
                        		
                        		$text = "Hai ottenuto ".$giveaway["quantity"]." ".$User->itemIdToName($giveaway["item_id"])." di livello ".$giveaway["level"]." e rarità ".$giveaway["rarity"];//rarity id to name
                        		$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML", 'reply_markup' => $keyboard_markup);
                        		$Telegram->sendMessage($content);
                        		
                        		$User->executeQuery("INSERT INTO wt_redeemed_giveaways VALUES(?,?,?)", "iii", array(0, $giveaway["giveaway_id"], $User->getUserId()));
                        	}
                        }
                    } else {
                        $text = "Con chi vuoi parlare?";
                        // Generate keyboard
                        for ($i = 0; $i < count($NPCs); $i++) $keyboard[$i] = array("Parla con ".$NPCs[$i]->getName());
                        $keyboard_markup = $Telegram->replyKeyboardMarkup($keyboard, true);
                        $content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML", 'reply_markup' => $keyboard_markup);
                        $Telegram->sendMessage($content);
                    }
                    return;
                
                case "⚔️":
                	$spawned_creatures = $Terrain->executeQuery("SELECT * FROM wt_spawned_creatures WHERE terrain_id = ?", "i", $Terrain->getTerrain(null));
                    if ($spawned_creatures->num_rows) {
                    	$text = "Che creatura vuoi attaccare?";
                    	while ($creature = $spawned_creatures->fetch_array()) {
                    		$in_keyb_buttons[] = array($Telegram->inlineKeyboardButton($creature["name"]." Lv ".$creature["creature_level"]."🎖 ".(int)($creature["stamina"]/$creature["stamina_max"]*100)."%♥️", "", "fight_".$creature["spawned_creature_id"], ""));
                    	}
                    	$inline_keyb = $Telegram->inlineKeyboardMarkup($in_keyb_buttons);
                    } else $text = "Non ci sono creature da uccidere!\n\n";
                	
                    $content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML", 'reply_markup' => $inline_keyb);
                    $Telegram->sendMessage($content);
                	return;
                	
                case "🤝":
                	$users = $Terrain->executeQuery("SELECT * FROM wt_users WHERE terrain = ? AND user_id <> ?", "ii", array($Terrain->getTerrain(null), $User->getUserId()));
                    if ($users->num_rows) {
                    	$text = "Chi vuoi sfidare?";
                    	while ($user = $users->fetch_array()) {
                    		
                    		$in_keyb_buttons[] = array($Telegram->inlineKeyboardButton($user["username"]." Lv ".$user["level"]."🎖", "", "challange_request_".$user["user_id"], ""));
                    	}
                    	$inline_keyb = $Telegram->inlineKeyboardMarkup($in_keyb_buttons);
                    } else $text = "Non ci sono avventurieri da sfidare!";
                	
                    $content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML", 'reply_markup' => $inline_keyb);
                    $Telegram->sendMessage($content);
                	return;
                	
                case "Vedi":
                	$parameters = explode("🎖", $words[1]);
                	$item_name = trim($parameters[0]);
                	$level = trim($parameters[1]);
                	
                	$result = $User->executeQuery("SELECT * FROM wt_items WHERE name = ?", "s", $item_name);
                	
                	if ($result->num_rows) {
                		$item = $result->fetch_array();
                		
                		// Get quantity
                		$result = $User->executeQuery("SELECT backpack_item_id, quantity FROM wt_backpacks WHERE item_id = ? AND level = ?", "ii", array($item["item_id"], $level));
                		if ($result->num_rows) {
                			$row = $result->fetch_array();
                			$backpack_item_id = $row["backpack_item_id"];
                			$item_quantity = $row["quantity"];
                		} else $item_quantity = 0;
                		
                		$text = "<b>".$item["name"]."</b>\n";
                		$text .= $item["description"]."\n";
                		$text .= "⚖️ <i>Quantità: ".$item_quantity."</i>\n";
                		//$text .= 
                		$text .= "🏋️‍♀️ <i>Peso: ".$item["weight"]."</i>\n";
						$text .= "💠 <i>Valore: ".$item["cost"]."</i>\n\n";
						
                		if ($item["stamina"]) $text .= "♥️ <b>Stamina: ".$item["stamina"]."</b>\n";
						if ($item["mana"]) $text .= "⚡️ <b>Mana: ".$item["mana"]."</b>\n";
						if ($item["tiredness"]) $text .= "💤 <b>Stanchezza: ".$item["tiredness"]."%</b>\n";
						if ($item["sobriety"]) $text .= "🍺 <b>Sobrietà: ".$item["sobriety"]."%</b>\n\n";
						if ($item["damage"]) $text .= "🗡 <b>Danno: ".$item["damage"]."</b>\n";
						if ($item["defense"]) $text .= "🛡 <b>Difesa: ".$item["defense"]."</b>\n";
						if ($item["critical"]) $text .= "‼️ <b>Critico: ".($item["critical"]/100)."%</b>\n";
                		
                		$in_keyb_buttons = [];
                		
                		if ($item_quantity and $item["type"] == "weapon") $in_keyb_buttons[] = array($Telegram->inlineKeyboardButton("Equipaggia 🗡", "", "equip_weapon_".$backpack_item_id, ""));
                		if ($item_quantity and $item["type"] == "armor") $in_keyb_buttons[] = array($Telegram->inlineKeyboardButton("Equipaggia 🗡", "", "equip_armor_".$backpack_item_id, ""));
                    	if ($item_quantity and $item["type"] == "shield") $in_keyb_buttons[] = array($Telegram->inlineKeyboardButton("Equipaggia 🗡", "", "equip_shield_".$backpack_item_id, ""));
                    	if ($item_quantity and $item["type"] == "food") $in_keyb_buttons[] = array($Telegram->inlineKeyboardButton("Mangia 🍕", "", "eat_".$backpack_item_id, ""));
                    	if ($item_quantity and $item["type"] == "drink") $in_keyb_buttons[] = array($Telegram->inlineKeyboardButton("Bevi 🍺", "", "drink_".$backpack_item_id, ""));
                    	if ($item_quantity) $in_keyb_buttons[] = array($Telegram->inlineKeyboardButton("Chiudi 🎒", "", "close", ""));
                    	else $in_keyb_buttons[] = array($Telegram->inlineKeyboardButton("Torna all'avventura 🏔", "", "close", ""));
                    	
                    	$inline_keyb = $Telegram->inlineKeyboardMarkup($in_keyb_buttons);
                    	$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML", 'reply_markup' => $inline_keyb);
                    	$Telegram->sendMessage($content);
                	} else {
                		$text = "L'oggetto richiesto non esiste!";
                        $content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML", 'reply_markup' => $keyboard_markup);
                        $Telegram->sendMessage($content);
                    }
                	return;
                
                case "Rimuovi":
                	$parameters = explode("🎖", $words[1]);
                	$item_name = trim($parameters[0]);
                	$level = trim($parameters[1]);
                	
					//get stats by level
					//update user's stats
					$result = $User->executeQuery("SELECT * FROM wt_items WHERE name = ?", "s", $item_name);
					$item = $result->fetch_array();
					$item_id = $item["item_id"];
					$Logger->logMethod($item["item_id"]);
					$User->updateStatistics(
						$User->getStamina() - $item["stamina"] * (1 + ($level-1)/10),
						$User->getStaminaMax() - $item["stamina"] * (1 + ($level-1)/10),
						$User->getMana() - $item["mana"] * (1 + ($level-1)/10),
						$User->getManaMax() - $item["mana"] * (1 + ($level-1)/10),
						$User->getDamage() - $item["damage"] * (1 + ($level-1)/10),
						$User->getDefense() - $item["defense"] * (1 + ($level-1)/10),
						$User->getCritical() - $item["critical"] * (1 + ($level-1)/10),
						$User->getWeight(),
						$User->getWeightMax(),
						$User->getTiredness(),
						$User->getSobriety()
					);
			
					$User->addItemToBackpack($item_id, $level, 1);
					
					$User->executeQuery("DELETE FROM wt_equipped_items WHERE user_id = ? AND item_id = ?", "ii", array($User->getUserId(), $item_id));
					
    				$text = "Equipaggiamento rimosso.";
                	$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML", 'reply_markup' => $keyboard_markup);
                    $Telegram->sendMessage($content);
                	return;
                	
                case "Chiudi":
                	$text = array(
                		"Chiudi lo zaino.",
                		"Rimetti lo zaino in spalla."
                	);
                	$content = array('chat_id' => $User->getUserId(), 'text' => $text[rand(0, count($text)-1)], 'parse_mode' => "HTML", 'reply_markup' => $keyboard_markup);
                    $Telegram->sendMessage($content);
                    
                    require_once("methods/describeTerrain.php");
                	describeTerrain($Logger, $Telegram, $User, $Terrain, $keyboard_markup);
                	return;
                
                case "✉️":
                	$text = "TWT ha bisogno del tuo aiuto! Se dovessi avere suggerimenti o dovessi aver trovato bug unisciti al gruppo di Feedback ✉️ ! Unisciti per discutere le idee che verranno implementate nella prossima versione.";
                	$in_keyb_buttons = array(array($Telegram->inlineKeyboardButton("✉️", "https://t.me/twt_feedback", "", "")));                    
                    $inline_keyb = $Telegram->inlineKeyboardMarkup($in_keyb_buttons);
                    $content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML", 'reply_markup' => $inline_keyb);
                    $Telegram->sendMessage($content);
                	return;
            }
            break;
    }

    switch ($Telegram->Text()) {
        case "/start":
        	require_once("methods/describeTerrain.php");
            describeTerrain($Logger, $Telegram, $User, $Terrain, $keyboard_markup);
            return;
        
        /*case "/restart":
            changeStatus($Logger, $User, "none");
            $Telegram->sendMessage(array("chat_id" => $User->chat_id, "text" => "Everything should be back to normal! 👍", "reply_markup" => $Telegram->replyKeyboardRemove(True)));
            return;*/
    }
}

function NPCIdToName($User, $npc_id) {
	$result = $User->executeQuery("SELECT * FROM wt_npcs WHERE npc_id = ?", "i", $npc_id);
	$npc = $result->fetch_array();
	return $npc["name"];
}
