<?php

function analyzeCallbackQuery($Logger, $Telegram, $User, $Terrain, $Quest, $keyboard_markup) {
    $Logger->logMethod("Analyzing callback.");
    
    $words = explode("_", $Telegram->Callback_Data());
    switch ($words[0]) {
        case "quest":
            $content = array('callback_query_id' => $Telegram->Callback_Id());
            $Telegram->answerCallbackQuery($content);
            
            $Quest->loadQuest($words[2]);
            
            switch ($words[1]) {
                case "preview":
                	$text = "<b>".$Quest->getName()."</b>\n";
                	$text .= $Quest->getMessagePreQuest()."\n";
                	$text .= "Ricompensa:\n";
                	$text .= "💠 ".$Quest->getMoney()." ";
                	$text .= "🎓 ".$Quest->getExperience();
                    $in_keyb_buttons = array(array($Telegram->inlineKeyboardButton("Accetta! 👍", "", "quest_begin_".$Quest->getQuestId(), "")));
                    $inline_keyb = $Telegram->inlineKeyboardMarkup($in_keyb_buttons);
                    $content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML", 'reply_markup' => $inline_keyb);
                    $Telegram->sendMessage($content);
                    return;
                
                case "begin":
                	// Check if the user has a standing quest
                	if ($User->getActiveQuest()) {
                		$text = "Hai un'altra quest in corso.";
                		$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML", 'reply_markup' => $keyboard_markup);
                    	$Telegram->sendMessage($content);
                    	return;
                	}
                	// Check if the quest has already benn completed
                	$result = $Quest->executeQuery("SELECT * FROM wt_completed_quests WHERE quest_id = ? AND user_id = ?", "ii", array($Quest->getQuestId(), $User->getUserId()));
                	if ($result->num_rows) {
                		$text = "Quest già completata!";
                		$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML", 'reply_markup' => $keyboard_markup);
                    	$Telegram->sendMessage($content);
                    	return;
                	}
                	
                	$User->updateActiveQuest($Quest->getQuestId());
                	//$User->updateStatus();
                	
                    $in_keyb_buttons = array(array($Telegram->inlineKeyboardButton("Quest accettata!", "", "void", "")));
                    $inline_keyb = $Telegram->inlineKeyboardMarkup($in_keyb_buttons);
                    $content = array('chat_id' => $Telegram->Callback_Chat_Id(), 'message_id' => $Telegram->Callback_Message_Id(), 'reply_markup' => $inline_keyb);
                    $Telegram->editMessageReplyMarkup($content);
                    return;
                
                case "give":
                	if (!$Quest->getActiveQuestId()) {
                		$text = "Non hai quest attive!";
                		$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML", 'reply_markup' => $keyboard_markup);
                    	$Telegram->sendMessage($content);
                    	return;
                	}
                	
                	$parameters = explode("&", $Quest->getParameters());
                	$npc_id = $parameters[0];
                	$item_id = $parameters[1];
                	$level = $parameters[2];
                	$quantity = $parameters[3];
                	
                	// Check if the user is in front of the NPC
                	$result = $Terrain->executeQuery("SELECT * FROM wt_npcs WHERE npc_id = ?", "i", $npc_id);
                	$npc = $result->fetch_array();
                	if ($User->getTerrain(null) != $npc["terrain"]) {
                		$text = "Torna a parlare con ".$npc["name"];
                		$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML", 'reply_markup' => $keyboard_markup);
                    	$Telegram->sendMessage($content);
                    	return;
                	}
                	// Check if the user has all the items needed
                	$result = $User->executeQuery("SELECT * FROM wt_backpacks WHERE user_id = ? AND item_id = ? AND level = ?", "iii", array($User->getUserId(), $item_id, $level));
                	if ($result->num_rows) {
                		$row = $result->fetch_array();
                		if ($row["quantity"] >= $quantity) {
                			$User->removeItemFromBackpack($item_id, $rarity, $level, $quantity);
                			$User->updateActiveQuest(0);
                			
                			$text = "✅  Quest completata!\n";
                			$text .= "<b>".$Quest->getName()."</b>\n";
                			$text .= $Quest->getMessagePostQuest()."\n";//add name by function idToName
                			$text .= "Ricompensa:\n";
                			$text .= "💠 ".$Quest->getMoney()." ";
                			$text .= "🎓 ".$Quest->getExperience();
                			
							$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML", 'reply_markup' => $keyboard_markup);
                			$Telegram->sendMessage($content);
                			
                			$Quest->questCompleted($User->getUserId());
            				//       ------- $User->updateQuestQueNumber() if main quest -------
                			$User->moveMoney($Quest->getMoney());
                			$levelled_up = $User->addExperience($Quest->getExperience());
                			printLevelUp($Telegram, $User, $levelled_up);
                			return;
                		} else $text = "Non hai gli oggetti richiesti.";
                	} else $text = "Non hai gli oggetti richiesti.";

                	$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML", 'reply_markup' => $keyboard_markup);
                	$Telegram->sendMessage($content);
                	return;
                
                case "withdraw":
                	if (!$Quest->getActiveQuestId()) {
                		$text = "Non hai quest attive!";
                		$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML", 'reply_markup' => $keyboard_markup);
                    	$Telegram->sendMessage($content);
                    	return;
                	}
                	
                	$User->updateActiveQuest(0);
                	
                	$text = "Hai rinunciato! 🏃";
                	$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML", 'reply_markup' => $keyboard_markup);
                	$Telegram->sendMessage($content);
                	return;
            }
            return;
            
        case "weapons":
        	showBackpackButtons($Telegram, $User, "weapon", "Non possiedi armi!");
            return;
        
        case "armors":
        	showBackpackButtons($Telegram, $User, "armor", "Non possiedi corazze!");
        	return;
        	
        case "shields":
        	showBackpackButtons($Telegram, $User, "shield", "Non possiedi scudi!");
        	return;
        	
        case "equipped":
        	$result = $User->executeQuery("SELECT * FROM wt_equipped_items WHERE user_id = ?", "i", $User->getUserId());
			if (!$result->num_rows) {
				$content = array('callback_query_id' => $Telegram->Callback_Id(), 'text' => "Non hai niente equipaggiato!", 'show_alert' => true);
				$Telegram->answerCallbackQuery($content);
			}
			else {
				$content = array('callback_query_id' => $Telegram->Callback_Id());
				$Telegram->answerCallbackQuery($content);
		
				$text = "";
				$keyboard = [];
				while ($row = $result->fetch_array()) {
					$text .= $row["name"]."  (<b>".$row["quantity"]."</b>)\n";
					$keyboard[] = array("Rimuovi ".$row["name"]." 🎖".$row["level"]."");
				}
				$keyboard_markup = $Telegram->replyKeyboardMarkup($keyboard, true);
				$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML", 'reply_markup' => $keyboard_markup); 
				$Telegram->sendMessage($content);
			}
        	return;
        	
        case "spells":
        	showBackpackButtons($Telegram, $User, "spell", "Non possiedi incantesimi!");
        	return;
        	
        case "food":
        	showBackpackButtons($Telegram, $User, "food", "Non possiedi cibo!");
        	return;
        	
        case "drinks":
        	showBackpackButtons($Telegram, $User, "drink", "Non possiedi bevande!");
        	return;
        	
        case "equip":
        	$content = array('callback_query_id' => $Telegram->Callback_Id());
            $Telegram->answerCallbackQuery($content);
            
        	$type = $words[1];
        	$backpack_item_id = $words[2];
        	
        	switch ($type) {
        		case "weapon":
        			equipItem($Logger,$Telegram, $User, $type, $backpack_item_id, "Hai già un'arma equipaggiata. La vuoi sostituire?", "Arma equipaggiata!");
        			break;
        		
        		case "armor":
        			equipItem($Telegram, $User, $type, $backpack_item_id, "Hai già una protezione equipaggiata. La vuoi sostituire?", "Protezione equipaggiata!");
        			break;
        			
        		case "shield":
        			equipItem($Telegram, $User, $type, $backpack_item_id, "Hai già uno scudo equipaggiato. Lo vuoi sostituire?", "Scudo equipaggiato!");
        			break;
        	}
        	return;
        
        case "substitute":
        	$content = array('callback_query_id' => $Telegram->Callback_Id());
            $Telegram->answerCallbackQuery($content);
            
			$equipped_item_id = $words[1];
			$old_item_id = $words[2];
			$level = $words[3];
			$backpack_item_id = $words[4];
			
			//get stats by level
			//update user's stats
			$result = $User->executeQuery("SELECT * FROM wt_items WHERE item_id = ?", "i", $old_item_id);
			$item = $result->fetch_array();
			
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
			
			$User->addItemToBackpack($old_item_id, $level, 1);
			
			
			$result = $User->executeQuery("SELECT * FROM wt_backpacks WHERE backpack_item_id = ?", "i", $backpack_item_id);
			$item = $result->fetch_array();
			
			$User->executeQuery(
				"UPDATE wt_equipped_items SET item_id = ?, type = ?, level = ?, name = ? WHERE equipped_item_id = ?",
				"isisi",
				array(
					$item["item_id"],
					$item["type"],
					$item["level"],
					$item["name"],
					$equipped_item_id
				)
			);
			
			$User->updateStatistics(
				$User->getStamina() + $item["stamina"],
				$User->getStaminaMax() + $item["stamina"],
				$User->getMana() + $item["mana"],
				$User->getManaMax() + $item["mana"],
				$User->getDamage() + $item["damage"],
				$User->getDefense() + $item["defense"],
				$User->getCritical() + $item["critical"],
				$User->getWeight(),
				$User->getWeightMax(),
				$User->getTiredness(),
				$User->getSobriety()
			);
			
			$User->removeItemFromBackpack($item["item_id"], $item["level"], 1);
			
			$text = "Sostituzione effettuata.";
            $content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML");
        	$Telegram->sendMessage($content);
			return;
        
        /*case "unequip":
        	switch () {
        	
        	}
        	return;*/
        	
        case "eat":
        case "drink":
        	$content = array('callback_query_id' => $Telegram->Callback_Id());
            $Telegram->answerCallbackQuery($content);
            
        	$backpack_item_id = $words[1];
        	// Get quantity
			$result = $User->executeQuery("SELECT * FROM wt_backpacks WHERE user_id = ? AND backpack_item_id = ?", "ii", array($User->getUserId(), $backpack_item_id));
			if ($result->num_rows) {
				$item = $result->fetch_array();
				
				$User->updateStatistics(
					$User->getStamina() + $item["stamina"],
					$User->getStaminaMax(),
					$User->getMana() + $item["mana"],
					$User->getManaMax(),
					$User->getDamage() + $item["damage"],
					$User->getDefense() + $item["defense"],
					$User->getCritical() + $item["critical"],
					$User->getWeight() - $item["weight"],
					$User->getWeightMax(),
					$User->getTiredness() + $item["tiredness"],
					$User->getSobriety() + $item["sobriety"]
				);
				
				$User->removeItemFromBackpack($item["item_id"], $item["level"], 1);
				
				$text = "";
				if ($item["stamina"]) $text .= "Ha recuperato ".$item["stamina"]." ♥️\n";
				if ($item["mana"]) $text .= "Ha recuperato ".$item["mana"]." ⚡️\n";
				$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML", 'reply_markup' => $keyboard_markup); 
				$Telegram->sendMessage($content);
			} else {
				$text = "Non possiedi questo oggetto!";
				$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML", 'reply_markup' => $keyboard_markup); 
				$Telegram->sendMessage($content);
			}
        	return;
        
        case "drop":
        	$content = array('callback_query_id' => $Telegram->Callback_Id());
            $Telegram->answerCallbackQuery($content);
        	return;
        	
        case "close":
        	$content = array('callback_query_id' => $Telegram->Callback_Id());
            $Telegram->answerCallbackQuery($content);
            
            $text = array(
                	"Chiudi lo zaino.",
                	"Rimetti lo zaino in spalla."
            );
            $content = array('chat_id' => $User->getUserId(), 'text' => $text[rand(0, count($text)-1)], 'parse_mode' => "HTML", 'reply_markup' => $keyboard_markup);
            $Telegram->sendMessage($content);
            
        	require_once("methods/describeTerrain.php");
        	describeTerrain($Logger, $Telegram, $User, $Terrain, $keyboard_markup);
        	return;
        	
        case "fight"://Update message instead
        	$content = array('callback_query_id' => $Telegram->Callback_Id());
            $Telegram->answerCallbackQuery($content);
            
        	$Creature = new Creature($words[1]);
        	
        	if (!$Creature->getSpawnedCreatureId()) {
        		$text = "La creatura è già stata eliminata.";
        		$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML"/*, 'reply_markup' => $inline_keyb*/); 
				$Telegram->sendMessage($content);
				return;
        	}
        	
        	if ($User->getTerrain() != $Creature->getTerrainId()) {
        		$text = "Non ti trovi più vicino alla creatura.";
        		$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML"/*, 'reply_markup' => $inline_keyb*/); 
				$Telegram->sendMessage($content);
				return;
        	}
        	
        	if ($User->getCritical() > rand(0, 10000)) $damage = $User->getDamage()*2 - $Creature->getDefense();
        	else $damage = randomizeStat($User->getDamage()) - randomizeStat($Creature->getDefense());
        	
        	if ($damage < 0) $text = $Creature->getName()." ha parato l'attacco!\n";
        	else if ($damage < $Creature->getStamina()) {
        		$Creature->updateStamina($Creature->getStamina()-$damage);
        		
        		$text = "Hai inflitto ".$damage."!\n";
        		$text .= $Creature->getName()." ".$Creature->getStamina()."/".$Creature->getStaminaMax()."\n";
        	} else {
        		$Creature->removeYourself();
        		
        		$User->moveMoney($Creature->getMoney());
        		$levelled_up = $User->addExperience($User->getExperience() + $Creature->getExperience());
        		printLevelUp($Telegram, $User, $levelled_up);
        		
        		$text = "Hai sconfitto la creatura!\n";
        		$text .= "Ricompensa:\n";
        		$text .= "💠 ".$Creature->getMoney()." ";
                $text .= "🎓 ".$Creature->getExperience()."\n";
        		//$inline_keyb = [];
        		$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML"/*, 'reply_markup' => $inline_keyb*/); 
				$Telegram->sendMessage($content);
				
				// kill quest
				
				return;
        	}
        	
        	if ($Creature->getCritical() > rand(0, 10000)) $damage = $Creature->getDamage()*2 - $User->getDefense();
        	else $damage = randomizeStat($Creature->getDamage()) - randomizeStat($User->getDefense());
        	
        	if ($damage < 0) $text .= "Hai parato l'attacco!\n";
			else if ($damage < $User->getStamina()) {
        		$User->updateStamina($User->getStamina()-$damage);
        		
        		$text .= $Creature->getName()." ti ha inflitto ".$damage."!\n";
        	} else {
        		$User->updateStamina(0);
        		
        		$text = "Sei stato sconfitto da ".$Creature->getName()."!\n";
        		//$inline_keyb = [];
        		$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML"/*, 'reply_markup' => $inline_keyb*/); 
				$Telegram->sendMessage($content);
				return;
        	}
			
			$in_keyb_buttons = array(array($Telegram->inlineKeyboardButton("Attacca ⚔️", "", "fight_".$Creature->getSpawnedCreatureId(), ""), $Telegram->inlineKeyboardButton("Incantesimi 🔥", "", "usespell_".$Creature->getSpawnedCreatureId(), "")));
            $inline_keyb = $Telegram->inlineKeyboardMarkup($in_keyb_buttons);
		
			$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML", 'reply_markup' => $inline_keyb); 
			$Telegram->sendMessage($content);
        	return;
		
		case "challange":
			$content = array('callback_query_id' => $Telegram->Callback_Id());
            $Telegram->answerCallbackQuery($content);
            
			$status = $words[1];
			
			switch ($status) {
				case "request":
					$user_id = $words[2];
					$text = "<b>Quanto vuoi scommettere?</b>\nSe vinci la sfida riceverai indietro i Winterly 💠 puntati e guadagnerai inoltre quelli del tuo avversario. Se perdi perdi anche i Winterly 💠 puntati. In entrambi i casi quadagni esperienza 🎓.";
					$in_keyb_buttons = array(
						array($Telegram->inlineKeyboardButton("100 💠", "", "challange_bet_".$user_id."_100", "")),
						array($Telegram->inlineKeyboardButton("500 💠", "", "challange_bet_".$user_id."_500", "")),
						array($Telegram->inlineKeyboardButton("1000 💠", "", "challange_bet_".$user_id."_1000", ""))
					);
                	$inline_keyb = $Telegram->inlineKeyboardMarkup($in_keyb_buttons);
					$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML", 'reply_markup' => $inline_keyb); 
					$Telegram->sendMessage($content);
					return;
				
				case "bet"://aggiungi timestamp - elimina richieste troppo vecchie
					$result = $User->executeQuery("SELECT * FROM wt_challanges WHERE user_id_a = ?", "i", $User->getUserId());
					if ($result->num_rows) {
						$text = "Hai già una richiesta di sfida attiva. Aspetta che l'avversario risponda oppure ritira la richiesta.";
						$in_keyb_buttons = array(array($Telegram->inlineKeyboardButton("Ritira la richiesta.", "", "challange_withdraw_".$challange_id, "")));
                		$inline_keyb = $Telegram->inlineKeyboardMarkup($in_keyb_buttons);
						$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML", 'reply_markup' => $inline_keyb); 
						$Telegram->sendMessage($content);
						return;
					}
					
					$user_id = $words[2];
					$bet = $words[3];
					$challange_id = $User->challangeAdventurer($user_id, $bet);
					
					$text = $User->getUsername()." ti sfida ad un duello per ".$bet." 💠! Vuoi accettare?";
					$in_keyb_buttons = array(array($Telegram->inlineKeyboardButton("Accetta! 👍", "", "challange_accepted_".$challange_id, ""), $Telegram->inlineKeyboardButton("Declina! ❌", "", "challange_declined_".$challange_id, "")));
                	$inline_keyb = $Telegram->inlineKeyboardMarkup($in_keyb_buttons);
					$content = array('chat_id' => $user_id, 'text' => $text, 'parse_mode' => "HTML", 'reply_markup' => $inline_keyb); 
					$Telegram->sendMessage($content);
			
					$text = "Richiesta inviata! Attendi che l'avversario risponda.";
					$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML"); 
					$Telegram->sendMessage($content);
					return;
			
				case "accepted":
					$challange_id = $words[2];
					
					$result = $User->executeQuery("SELECT * FROM wt_challanges WHERE challange_id = ?", "i", $challange_id);
					if ($result->num_rows) {
						$challange = $result->fetch_array();
						
						if (!$challange["approved"]) {
							$in_keyb_buttons = array(array($Telegram->inlineKeyboardButton("Sfida accettata!", "", "void", "")));
                    		$inline_keyb = $Telegram->inlineKeyboardMarkup($in_keyb_buttons);
                    		$content = array('chat_id' => $Telegram->Callback_Chat_Id(), 'message_id' => $Telegram->Callback_Message_Id(), 'reply_markup' => $inline_keyb);
                    		$Telegram->editMessageReplyMarkup($content);
                    		
							$User->approveChallange($challange_id);
							
							$User->executeQuery("UPDATE wt_users SET status = ? WHERE user_id = ?", "si", array("challange", $challange["user_id_a"]));
							$User->updateStatus("challange");
							
							$text = $User->getUsername()." ha accettato la sfida!";
							$in_keyb_buttons = array(array($Telegram->inlineKeyboardButton("Attacca 🗡", "", "challange_attack_".$challange_id."_a_b", "")));
                    		$inline_keyb = $Telegram->inlineKeyboardMarkup($in_keyb_buttons);
							$content = array('chat_id' => $challange["user_id_a"], 'text' => $text, 'parse_mode' => "HTML", 'reply_markup' => $inline_keyb); 
							$Telegram->sendMessage($content);
						} else {
							$text = "Hai già accettato la sfida. Combatti!";
							$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML"); 
							$Telegram->sendMessage($content);
						}
					} else {
						$text = "La sfida non esiste più!";
						$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML"); 
						$Telegram->sendMessage($content);
					}
					return;
					
				case "declined":
					$challange_id = $words[2];
					
					$result = $User->executeQuery("SELECT * FROM wt_challanges WHERE challange_id = ?", "i", $challange_id);
					if ($result->num_rows) {
						$challange = $result->fetch_array();
						
						if (!$challange["approved"]) {
							$in_keyb_buttons = array(array($Telegram->inlineKeyboardButton("Sfida rifiutata!", "", "void", "")));
                    		$inline_keyb = $Telegram->inlineKeyboardMarkup($in_keyb_buttons);
                    		$content = array('chat_id' => $Telegram->Callback_Chat_Id(), 'message_id' => $Telegram->Callback_Message_Id(), 'reply_markup' => $inline_keyb);
                    		$Telegram->editMessageReplyMarkup($content);
							$User->removeChallange($challange_id);
					
							$text = "Quell'ammazza balotta ha rifiutato la sfida.";
							$content = array('chat_id' => $challange["user_id_a"], 'text' => $text, 'parse_mode' => "HTML"); 
							$Telegram->sendMessage($content);
						} else {
							$text = "Hai accettato la sfida ora combatti!";
							$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML"); 
							$Telegram->sendMessage($content);
						}
					} else {
						$text = "Hai già rifiutato la sfida! Ammazza balotta...";
						$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML"); 
						$Telegram->sendMessage($content);
					}
					return;
					
				case "withdraw":
					$challange_id = $words[2];
					
					$result = $User->executeQuery("SELECT * FROM wt_challanges WHERE challange_id = ?", "i", $challange_id);
					if ($result->num_rows) {
						$challange = $result->fetch_array();
						
						if (!$challange["approved"]) {
							$in_keyb_buttons = array(array($Telegram->inlineKeyboardButton("Sfida ritirata!", "", "void", "")));
                    		$inline_keyb = $Telegram->inlineKeyboardMarkup($in_keyb_buttons);
                    		$content = array('chat_id' => $Telegram->Callback_Chat_Id(), 'message_id' => $Telegram->Callback_Message_Id(), 'reply_markup' => $inline_keyb);
                    		$Telegram->editMessageReplyMarkup($content);
							$User->removeChallange($challange_id);
					
							$text = "Quell'ammazza balotta ha ritirato la sfida.";
							$content = array('chat_id' => $challange["user_id_b"], 'text' => $text, 'parse_mode' => "HTML"); 
							$Telegram->sendMessage($content);
						} else {
							$text = "La sfida è stata accettata ora combatti!";
							$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML"); 
							$Telegram->sendMessage($content);
						}
					} else {
						$text = "Non c'è nessuna sfida da ritirare.";
						$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML"); 
						$Telegram->sendMessage($content);
					}
					return;
					
				case "attack":
					$challange_id = $words[2];
					$attacker = $words[3];
					$defender = $words[4];
					
					attack($Telegram, $User, $challange_id, $attacker, $defender);
					return;
			}
			return;
			
		case "market":
			switch ($words[1]) {
				case "buy":
					showShopItems($Telegram, $User, $words[2]);
					return;
				
				/*case "sell":
					
					return;*/
			}
			return;
		
		case "buy":
			buyItem($Telegram, $User, $words[1]);
			return;
		
		case "hotel":
			$content = array('callback_query_id' => $Telegram->Callback_Id());
			$Telegram->answerCallbackQuery($content);
		
			if ($Terrain->getType() != "hotel") {
				$text = "Non ti trovi più nell'albergo!";
				$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML"); 
				$Telegram->sendMessage($content);
				return;
			}
			
			switch($words[1]) {
				case "prices": 
					$text = "Quante notti vuoi rimanere?";
					$in_keyb_buttons = array(
						array($Telegram->inlineKeyboardButton("1 notte - 500 💠", "", "hotel_stay_1_500", "")),
						array($Telegram->inlineKeyboardButton("2 notti - 900 💠", "", "hotel_stay_2_900", "")),
						array($Telegram->inlineKeyboardButton("7 notti - 3.000 💠", "", "hotel_stay_7_3000", "")),
						array($Telegram->inlineKeyboardButton("14 notti - 6.000 💠", "", "hotel_stay_14_6000", "")),
						array($Telegram->inlineKeyboardButton("Arrivederci! 👋", "", "hotel_hall", ""))
					);
					$inline_keyb = $Telegram->inlineKeyboardMarkup($in_keyb_buttons);
					$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML", 'reply_markup' => $inline_keyb); 
					$Telegram->sendMessage($content);
					return;
				
				case "stay":
					if ($User->getHotelTerrain() == $User->getTerrain() and $User->getHotelBookedUntil() > time()) {
						$text = "Hai già una stanza prenotata!";
						$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML"); 
						$Telegram->sendMessage($content);
						return;
					}
					
					$nights = $words[2];
					$cost = $words[3];
					
					// Check money
					$money = $User->getMoney() - $cost;
					if ($money < 0) {
						$text = "Non hai abbastanza Winterly.";
						$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML"); 
						$Telegram->sendMessage($content);
						return;
					}
					
					$User->executeQuery("UPDATE wt_users SET money = ?, hotel_terrain = ?, hotel_booked_until = ? WHERE user_id = ?", "iiii", array($money, $User->getTerrain(), time()+$nights*24*3600, $User->getUserId()));
					$User->setHotelTerrain($User->getTerrain());
					$User->setHotelBookedUntil(time()+$nights*24*3600);
					
					$text = "Stanza prenotata!";
					$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML"); 
					$Telegram->sendMessage($content);
					
					require_once("methods/describeTerrain.php");
        			describeTerrain($Logger, $Telegram, $User, $Terrain, $keyboard_markup);
					return;
					
				case "hall":
					require_once("methods/describeTerrain.php");
        			describeTerrain($Logger, $Telegram, $User, $Terrain, $keyboard_markup);
        			return;
        	}
			return;
			
		case "nap":
			$content = array('callback_query_id' => $Telegram->Callback_Id());
            $Telegram->answerCallbackQuery($content);
			
			require_once("methods/sleep.php");
			startSleeping($Telegram, $User, $Terrain, $User->getTiredness()-250, time()+3600);
			return;
			
		case "sleep":
			$content = array('callback_query_id' => $Telegram->Callback_Id());
            $Telegram->answerCallbackQuery($content);
			
			require_once("methods/sleep.php");
			startSleeping($Telegram, $User, $Terrain, 0, time()+4*3600);
			return;
		
		case "meditation":
			$content = array('callback_query_id' => $Telegram->Callback_Id());
            $Telegram->answerCallbackQuery($content);
            
            require_once("methods/meditation.php");
            
			switch ($words[1]) {
				case "start":
					startMeditation($Telegram, $User, $Terrain);
					return;
				
				case "end":
					endMeditation($Telegram, $User);
					return;
			}
			return;
			
        case "void":
            $content = array('callback_query_id' => $Telegram->Callback_Id());
            $Telegram->answerCallbackQuery($content);
            return;
    }
}

function showBackpackButtons($Telegram, $User, $type, $text_if_empty)
{
	$result = $User->executeQuery("SELECT * FROM wt_backpacks WHERE user_id = ? AND type = ?", "is", array($User->getUserId(), $type));
	if (!$result->num_rows) {
		$content = array('callback_query_id' => $Telegram->Callback_Id(), 'text' => $text_if_empty, 'show_alert' => true);
		$Telegram->answerCallbackQuery($content);
	}
	else {
		$content = array('callback_query_id' => $Telegram->Callback_Id());
		$Telegram->answerCallbackQuery($content);
		
		$text = "";
		$keyboard = [];
		while ($row = $result->fetch_array()) {
			$text .= $row["name"]." (<b>".$row["quantity"]."</b>)\n";
			$keyboard[] = array("Vedi ".$row["name"]." 🎖".$row["level"]);
		}
		$keyboard[] = array("Chiudi 🎒");
		$keyboard_markup = $Telegram->replyKeyboardMarkup($keyboard, true);
		$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML", 'reply_markup' => $keyboard_markup); 
		$Telegram->sendMessage($content);
	}
}

function printLevelUp($Telegram, $User, $levelled_up) {
	$User->getUser();
	
	if ($levelled_up) {
		$text = "🎖 Congratulazioni! 🎖\n";
		$text .= "Sei salito/a al livello <b>".$User->getLevel()."</b>!\n";
		$text .= "🎓 Esp: <b>".$User->getExperience()."</b>/".$User->experienceNeededToLevelUp($User->getLevel())."\n";
		$text .= "♥️ Stamina: <b>".$User->getStamina()."</b>\n";
		$text .= "⚡️ Mana: <b>".$User->getMana()."</b>\n";
		$text .= "🗡 Danno: <b>".$User->getDamage()."</b>\n";
		$text .= "🛡 Difesa: <b>".$User->getDefense()."</b>\n";
		$text .= "‼️ Critico: <b>".$User->getCritical()."%</b>\n";
		$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML", 'reply_markup' => $keyboard_markup);
		$Telegram->sendMessage($content);
	}
}

function randomizeStat($stat) {
	return rand((int)$stat*0.75, (int)$stat*1.05);
}

function attack($Telegram, $User, $challange_id, $att, $def) {
	$result = $User->executeQuery("SELECT * FROM wt_challanges WHERE challange_id = ?", "i", $challange_id);
	if ($result->num_rows) {
		$challange = $result->fetch_array();
		
		if ($challange["critical_".$att] > rand(0, 10000)) $damage = randomizeStat($challange["damage_".$att])*2 - randomizeStat($challange["defense_".$def]);
		else $damage = randomizeStat($challange["damage_".$att]) - randomizeStat($challange["defense_".$def]);
		
		if ($damage < 0) {
			$text = $challange["username_".$def]." ha parato l'attacco!\n";
			$content = array('chat_id' => $challange["user_id_".$att], 'text' => $text, 'parse_mode' => "HTML"); 
			$Telegram->sendMessage($content);
			
			$text = "Hai parato l'attacco!";
			$in_keyb_buttons = array(array($Telegram->inlineKeyboardButton("Attacca 🗡", "", "challange_attack_".$challange_id."_".$def."_".$att, "")));
			$inline_keyb = $Telegram->inlineKeyboardMarkup($in_keyb_buttons);
			$content = array('chat_id' => $challange["user_id_".$def], 'text' => $text, 'parse_mode' => "HTML", 'reply_markup' => $inline_keyb); 
			$Telegram->sendMessage($content);
		} else if ($damage < $challange["stamina_".$def]) {
			$User->executeQuery("UPDATE wt_challanges SET stamina_".$def." = ? WHERE challange_id = ?", "ii", array($challange["stamina_".$def]-$damage, $challange_id));
			
			$text = "Hai inflitto ".$damage."!\n";
			$text .= $challange["username_".$def]." ha ancora ".$challange["stamina_".$def]-$damage." ♥️";
			$content = array('chat_id' => $challange["user_id_".$att], 'text' => $text, 'parse_mode' => "HTML"); 
			$Telegram->sendMessage($content);
			
			$text = "Hai subito ".$damage."\n";
			$text .= "Hai ancora ".$challange["stamina_".$def]-$damage." ♥️";
			$in_keyb_buttons = array(array($Telegram->inlineKeyboardButton("Attacca 🗡", "", "challange_attack_".$challange_id."_".$def."_".$att, "")));
			$inline_keyb = $Telegram->inlineKeyboardMarkup($in_keyb_buttons);
			$content = array('chat_id' => $challange["user_id_".$def], 'text' => $text, 'parse_mode' => "HTML", 'reply_markup' => $inline_keyb); 
			$Telegram->sendMessage($content);
		} else {
			$User->logMethod(getcwd());
			require_once("../classes/user_class.php");
			$User->logMethod(getcwd());
			$UserB = new User();
			$UserB->getUser($challange["user_id_".$def]);
			
			$User->logMethod($UserB->getUsername());
			
			$User->updateStatus("none");
			$UserB->updateStatus("none");
			
			$User->executeQuery("DELETE FROM wt_challanges WHERE challange_id = ?", "i", $challange_id);
			
			
			$exp_a = (int) (pow($User->getLevel(), 2) /10 *2); // pow * 10 /100 
			if (!$exp_a) $exp_a = 1;
			
			$text = "Hai sconfitto !\n";
			$text .= "Ricevi ".$challange["bet"]." 💠 e ".$exp_a." 🎓.\n";
			$content = array('chat_id' => $challange["user_id_".$att], 'text' => $text, 'parse_mode' => "HTML"); 
			$Telegram->sendMessage($content);
			
			$User->moveMoney($challange["bet"]);
			
			$levelled_up = $User->addExperience($User->getExperience() + $exp_a);
			printLevelUp($Telegram, $User, $levelled_up);
			
			
			$exp_b = (int) (pow($UserB->getLevel(), 2) /100); // pow * 10 /100
			if (!$exp_b) $exp_b = 1;
			
			$text = "Sei stato sconfitto!\n";
			$text .= "Hai perso ".$challange["bet"]." 💠 ma ricevi ".$exp_b." 🎓.\n";
			$content = array('chat_id' => $challange["user_id_".$def], 'text' => $text, 'parse_mode' => "HTML"); 
			$Telegram->sendMessage($content);
			
			$UserB->moveMoney(-$challange["bet"]);

			$levelled_up = $UserB->addExperience($UserB->getExperience() + $exp_b);
			printLevelUp($Telegram, $UserB, $levelled_up);
			return;
		}
		
	}/* else {
		$text .= "";
		$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML", 'reply_markup' => $keyboard_markup);
		$Telegram->sendMessage($content);
	}*/
}

function equipItem($Logger,$Telegram, $User, $type, $backpack_item_id, $text_question_already_equipped, $text_item_equipped) {
	$result = $User->executeQuery("SELECT * FROM wt_equipped_items WHERE user_id = ? AND type = ?", "is", array($User->getUserId(), $type));
	if ($result->num_rows) {
		$item = $result->fetch_array();
		$equipped_item_id = $item["equipped_item_id"];
		$item_id = $item["item_id"];
		$level = $item["level"];
		
		$in_keyb_buttons = array(array($Telegram->inlineKeyboardButton("Sostituisci 🔄", "", "substitute_".$equipped_item_id."_".$item_id."_".$level."_".$backpack_item_id, "")));
		$inline_keyb = $Telegram->inlineKeyboardMarkup($in_keyb_buttons);
		$content = array('chat_id' => $User->getUserId(), 'text' => $text_question_already_equipped, 'parse_mode' => "HTML", 'reply_markup' => $inline_keyb);
		$Telegram->sendMessage($content);
	} else {
		$result = $User->executeQuery("SELECT * FROM wt_backpacks WHERE backpack_item_id = ?", "i", $backpack_item_id);
		if ($result->num_rows) {
			$item = $result->fetch_array();
			
			$User->executeQuery(
				"INSERT INTO wt_equipped_items VALUES(?,?,?,?,?,?)",
				"iiisis",
				array(
					0,
					$User->getUserId(),
					$item["item_id"],
					$item["type"],
					$item["level"],
					$item["name"]
					)
				);
		
			$User->removeItemFromBackpack($item["item_id"], $item["level"], 1);
		
			$User->updateStatistics(
				$User->getStamina() + $item["stamina"],
				$User->getStaminaMax() + $item["stamina"],
				$User->getMana() + $item["mana"],
				$User->getManaMax() + $item["mana"],
				$User->getDamage() + $item["damage"],
				$User->getDefense() + $item["defense"],
				$User->getCritical() + $item["critical"],
				$User->getWeight(),
				$User->getWeightMax(),
				$User->getTiredness(),
				$User->getSobriety()
			);
		
			$content = array('chat_id' => $User->getUserId(), 'text' => $text_item_equipped, 'parse_mode' => "HTML");
			$Telegram->sendMessage($content);
		} else {
			$text = "Non possiedi l'oggetto che stai tentando di equipaggiare.";
			$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML");
			$Telegram->sendMessage($content);
		}
	}
}

function showShopItems($Telegram, $User, $npc_id) {
	$result = $User->executeQuery("SELECT * FROM wt_shops WHERE owner = ?", "i", $npc_id);
	$in_keyb_buttons = [];
	while ($item = $result->fetch_array()) {
		//array("Vedi ".$row["name"]." 🎖".$row["level"]) convert to callback
		$in_keyb_buttons[] = array($Telegram->inlineKeyboardButton($item["name"]." 💠 ".$item["price"], "", "buy_".$item["shop_item_id"], ""));
	}
	$text = "Cosa ti può interessare?";
	$inline_keyb = $Telegram->inlineKeyboardMarkup($in_keyb_buttons);
	$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML", 'reply_markup' => $inline_keyb); 
	$Telegram->sendMessage($content);
}

function buyItem($Telegram, $User, $shop_item_id) {
	$result = $User->executeQuery("SELECT * FROM wt_shops WHERE shop_item_id = ?", "i", $shop_item_id);
	$item = $result->fetch_array();
	
	// Check if the user is talking to the seller
	if ($User->getTerrain() != $item["terrain"]) {
		$text = "Non puoi compare questo oggetto ora.";
		$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML");
		$Telegram->sendMessage($content);
		return;
	}
	
	// Check if the user has da-money
	if ($User->getMoney() < $item["price"]) {
		$text = "Non possiedi Winterly sufficienti.";
		$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML");
		$Telegram->sendMessage($content);
		return;
	}
	
	$User->moveMoney(-$item["price"]);
	
	$User->addItemToBackpack($item["item_id"], $item["level"], 1);
	
	$text = $item["name"]." Lv. ".$item["level"]." acquistato/a!";
	$content = array('chat_id' => $User->getUserId(), 'text' => $text, 'parse_mode' => "HTML");
	$Telegram->sendMessage($content);
}
