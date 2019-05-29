<?
function analyzePrivateMessage($Logger, $Telegram, $Editor, $Terrain, $Quest, $NPCs, $keyboard_markup) {
    $Logger->logMethod("Analyzing text from private.");

    if (!$Editor->getLevel()) {
        switch ($Telegram->Text()) {
            case "/requesttobecomeeditor":
                $text = $Editor->getUsername()." con id ".$Editor->getEditorId()." richiede di diventare editore. Accettare?";
                $in_keyb_buttons = array(array($Telegram->inlineKeyboardButton("Declina 🚫", "", "request_".$Editor->getEditorId()."_rejected", ""), $Telegram->inlineKeyboardButton("Accetta 👌", "", "request_".$Editor->getEditorId()."_accepted", "")));
                $inline_keyb = $Telegram->inlineKeyboardMarkup($in_keyb_buttons);
                $content = array('chat_id' => '118557634', 'text' => $text, 'parse_mode' => "HTML", 'reply_markup' => $inline_keyb); 
                $Telegram->sendMessage($content);
                
                $text = "Richiesta per diventare un <i>editor</i> inviata con successo al <b>Gran Pinguino</b>.";
                $content = array('chat_id' => $Editor->getEditorId(), 'text' => $text, 'parse_mode' => "HTML");
                $Telegram->sendMessage($content);
                return;
            default:
                $text = "Per poter diventare un <i>editor</i> devi prima essere stato accettato dal <b>Gran Pinguino</b>.";
                $content = array('chat_id' => $Editor->getEditorId(), 'text' => $text, 'parse_mode' => "HTML");
                $Telegram->sendMessage($content);
                return;
        }
    } else {
        $words = explode(" ", $Telegram->Text(), 2);
        switch($words[0]){
            case "👀":
                $text = "<b>".$Terrain->getName()."</b>\n";
                    $text .= $Terrain->getDescription()."\n";
                
                    // Switch with new array then add punctuation in the middle
                    $terrains = $Terrain->getTerrain("all");
                    if ($terrains["north"] or $terrains["east"] or $terrains["south"] or $terrains["west"]) {
                        $text .= "Passaggi percorribili:<b>";
                        if ($terrains["north"]) $text .= " Nord";
                        if ($terrains["east"]) $text .= " Est";
                        if ($terrains["west"]) $text .= " Ovest";
                        if ($terrains["south"]) $text .= " Sud";
                        $text .= "</b>.\n";
                    } else $text .= "Nessun passaggio praticabile.\n";
                    
                    // Available NPCs
                    switch (count($NPCs)) {
                        case 0:
                            $text .= "Non ci sono PNG.\n";
                            break;
                            
                        case 1:
                            $text .= "È presente un PNG.\n";
                            break;
                            
                        case 2:
                            $text .= "Ci sono 2 PNG.\n";
                            break;
                            
                        default:
                            $text .= "Ci sono molti PNG.\n";
                            break;
                    }
                    
                    if ($Terrain->getApproval()) $text .= "Luogo approvato!\n";
                    else $text .= "Luogo non approvato!\n";
                    
                    $content = array('chat_id' => $Editor->getEditorId(), 'text' => $text, 'parse_mode' => "HTML"); 
                    $Telegram->sendMessage($content);
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
                    $Editor->updateTerrain($new_terrain);
                    $Terrain->loadTerrain($new_terrain);
                    
                    if ($text = $Terrain->getWelcomeMessage("wlc_msg_".$from)) {
                        $content = array('chat_id' => $Editor->getEditorId(), 'text' => $text, 'parse_mode' => "HTML");
                        $Telegram->sendMessage($content);
                    }
                    
                    $text = "<b>".$Terrain->getName()."</b>\n";
                    $text .= $Terrain->getDescription()."\n\n";
                
                    // Switch with new array then add punctuation in the middle
                    $terrains = $Terrain->getTerrain("all");
                    if ($terrains["north"] or $terrains["east"] or $terrains["south"] or $terrains["west"]) {
                        $text .= "Passaggi percorribili:<b>";
                        if ($terrains["north"]) $text .= " Nord";
                        if ($terrains["east"]) $text .= " Est";
                        if ($terrains["west"]) $text .= " Ovest";
                        if ($terrains["south"]) $text .= " Sud";
                        $text .= "</b>.\n";
                    } else $text .= "Nessun passaggio praticabile.\n";
                    
                    // Update NPCs
                    $NPCs_id = $Terrain->getNPCsId();
                    $NPCs = [];
                    for ($i = 0; $i < count($NPCs_id); $i++) $NPCs[$i] = new NPC($NPCs_id[$i]);
                    
                    // Available NPCs
                    switch (count($NPCs)) {
                        case 0:
                            $text .= "Non ci sono PNG.\n";
                            break;
                            
                        case 1:
                            $text .= "È presente un PNG.\n";
                            break;
                            
                        case 2:
                            $text .= "Ci sono 2 PNG.\n";
                            break;
                            
                        default:
                            $text .= "Ci sono molti PNG.\n";
                            break;
                    }
                    
                    if ($Terrain->getApproval()) $text .= "Luogo approvato!\n";
                    else $text .= "Luogo non approvato!\n";
                    
                    $content = array('chat_id' => $Editor->getEditorId(), 'text' => $text, 'parse_mode' => "HTML", 'reply_markup' => $keyboard_markup); 
                    $Telegram->sendMessage($content);
                } else {
                    $text = "Non c'è nessun passaggio in quella direzione.";
                    $content = array('chat_id' => $Editor->getEditorId(), 'text' => $text, 'parse_mode' => "HTML", 'reply_markup' => $keyboard_markup); 
                    $Telegram->sendMessage($content);
                }
                return;
                
            
            // ---------------------------------------- Terrains Section ----------------------------------------
            case "/createterrain":
                $parameters = explode(",", $words[1], 3);
                $towards = trim(strtolower($parameters[0]));
                $name = ucfirst(trim($parameters[1]));
                $description = ucfirst(trim($parameters[2]));
                
                $error = $Terrain->createTerrain($Terrain->getTerrain(null), $towards, $name, $description, $Editor->getEditorId());
                
                if ($error === false) {
                    $text = "Terreno creato correttamente!";
                    $content = array('chat_id' => $Editor->getEditorId(), 'text' => $text, 'parse_mode' => "HTML"); 
                    $Telegram->sendMessage($content);
                } else {
                    $text = "Impossibile creare nuovo terreno. Errore: ".$error;
                    $content = array('chat_id' => $Editor->getEditorId(), 'text' => $text, 'parse_mode' => "HTML"); 
                    $Telegram->sendMessage($content);
                }
                return;
        
            case "/editname":
                $Logger->logMethod("Editing terrain name.");
                
                if (isset($words[1])) {
                    $Terrain->editName(ucfirst(trim($words[1])));
                    
                    $text = "Nome aggiornato!";
                    $content = array('chat_id' => $Editor->getEditorId(), 'text' => $text, 'parse_mode' => "HTML"); 
                    $Telegram->sendMessage($content);
                } else {
                    $text = "Non è stato possibile aggiornare il nome.";
                    $content = array('chat_id' => $Editor->getEditorId(), 'text' => $text, 'parse_mode' => "HTML"); 
                    $Telegram->sendMessage($content);
                }
                return;
                
            case "/editdescription":
                $Logger->logMethod("Editing terrain description.");
                
                if (isset($words[1])) {
                    $Terrain->editDescription(ucfirst(trim($words[1])));
                    
                    $text = "Descrizione aggiornata!";
                    $content = array('chat_id' => $Editor->getEditorId(), 'text' => $text, 'parse_mode' => "HTML"); 
                    $Telegram->sendMessage($content);
                } else {
                    $text = "Non è stato possibile aggiornare la descrizione.";
                    $content = array('chat_id' => $Editor->getEditorId(), 'text' => $text, 'parse_mode' => "HTML"); 
                    $Telegram->sendMessage($content);
                }
                return;
                
            case "/editwelcomemessage":
                $Logger->logMethod("Editing welcome message.");
                
                $parameters = explode(",", $words[1], 2);
                $towards = trim(strtolower($parameters[0]));
                $message = ucfirst(trim($parameters[1]));
                
                $error = $Terrain->editWelcomeMessage($towards, $message);
                
                if ($error === false) {
                    $text = "Messaggio di benvenuto aggiornato correttamente!";
                    $content = array('chat_id' => $Editor->getEditorId(), 'text' => $text, 'parse_mode' => "HTML"); 
                    $Telegram->sendMessage($content);
                } else {
                    $text = "Impossibile aggiornare il messaggio di benvenuto. Errore: ".$error;
                    $content = array('chat_id' => $Editor->getEditorId(), 'text' => $text, 'parse_mode' => "HTML"); 
                    $Telegram->sendMessage($content);
                }
                return;
                
            case "/approve":
            	if (permissionDenied($Editor->getEditorId(), $Editor->getLevel(), 3)) return;
            	
            	$Logger->logMethod("Approving terrain.");
            	
            	$Terrain->approveTerrain();
            	
            	$text = "Luogo approvato.";
                $content = array('chat_id' => $Editor->getEditorId(), 'text' => $text, 'parse_mode' => "HTML"); 
                $Telegram->sendMessage($content);
            	return;
                
                
            // ---------------------------------------- NPCs Section ----------------------------------------
            case "/addnpc":
                $Logger->logMethod("Adding NPC.");
                
                $parameters = explode(",", $words[1], 2);
                $name = ucfirst(trim($parameters[0]));
                $message = ucfirst(trim($parameters[1]));
                
                $error = $Terrain->addNPC($Terrain->getTerrain(null), $name, $message, $Editor->getEditorId());
                
                if ($error === false) {
                    $text = "PNG aggiunto correttamente!";
                    $content = array('chat_id' => $Editor->getEditorId(), 'text' => $text, 'parse_mode' => "HTML"); 
                    $Telegram->sendMessage($content);
                } else {
                    $text = "Impossibile aggiungere PNG. Errore: ".$error;
                    $content = array('chat_id' => $Editor->getEditorId(), 'text' => $text, 'parse_mode' => "HTML"); 
                    $Telegram->sendMessage($content);
                }
                return;
            
            case "/shownpcs":
                $Logger->logMethod("Showing NPCs");
                
                if (count($NPCs) == 0) $text = "Non ci sono PNG.";
                else {
                $text = "";
                    for ($i = 0; $i < count($NPCs); $i++) {
                        $text .= "npc_id: ".$NPCs[$i]->getNPCId()."\n";
                        $text .= "<b>".$NPCs[$i]->getName()."</b>\n";
                        $text .= $NPCs[$i]->getMessage()."\n\n";
                        
                        // Get quests ids
                        $result = $Quest->executeQuery("SELECT * FROM wt_quests WHERE issuer = ?", "i", $NPCs[$i]->getNPCId());
                        if (!$result->num_rows) $text .= "Questo PNG non ha quest associate.";
                        while ($row = $result->fetch_array()) {
                            $text .= "quest_id: ".$row["quest_id"]."\n";
                            $text .= "quest_name: ".$row["name"]."\n\n";
                        }
                    }
                }
                $content = array('chat_id' => $Editor->getEditorId(), 'text' => $text, 'parse_mode' => "HTML"); 
                $Telegram->sendMessage($content);
                return;
            
            case "/addgiveaway":
                return;
            
            // ---------------------------------------- Quests Section ----------------------------------------
            case "/addquest":
                $Logger->logMethod("Adding quest.");
                
                $parameterss = explode(",", $words[1], 4);
                $issuer = trim($parameterss[0]);
                $type = trim($parameterss[1]);
                $parameters = trim($parameterss[2]);
                $rewards = trim($parameterss[3]);
                
                $result = $Quest->addQuest($issuer, $type, $parameters, $rewards, $Editor->getEditorId());
                
                if (is_numeric($result)) {
                    $text = "Quest aggiunta correttamente! \n quest_id: ".$result;
                    $content = array('chat_id' => $Editor->getEditorId(), 'text' => $text, 'parse_mode' => "HTML"); 
                    $Telegram->sendMessage($content);
                } else {
                    $text = "Impossibile aggiungere quest. Errore: ".$result;
                    $content = array('chat_id' => $Editor->getEditorId(), 'text' => $text, 'parse_mode' => "HTML"); 
                    $Telegram->sendMessage($content);
                }
                return;
                
            case "/showquest":
                $Logger->logMethod("Showing quest.");
                
                $quest_id = trim($words[1]);
                if (!is_numeric($quest_id)) $text = "Non valid quest_id.";
                else {
                    if (!$Quest->loadQuest($quest_id)) $text = "The quest doesn't exist.";
                    else {
                        $text = "quest_id: ".$Quest->getQuestId()."\n";
                        $text .= "issuer: ".$Quest->getIssuer()."\n";
                        $text .= "name: ".$Quest->getName()."\n";
                        $text .= "type: ".$Quest->getType()."\n";
                        $text .= "summary: ".$Quest->getSummary()."\n";
                    }
                }
                
                $content = array('chat_id' => $Editor->getEditorId(), 'text' => $text, 'parse_mode' => "HTML"); 
                $Telegram->sendMessage($content);
                return;
            
            case "/editquesttitle":
                $Logger->logMethod("Editing quest name.");
                
                $parameters = explode(",", $words[1], 2);
                $quest_id = trim($parameters[0]);
                $name = ucfirst(trim($parameters[1]));
                
                $error = $Quest->editTitle($quest_id, $name);
                
                if ($error === false) {
                    $text = "Titolo modificato correttamente!";
                    $content = array('chat_id' => $Editor->getEditorId(), 'text' => $text, 'parse_mode' => "HTML"); 
                    $Telegram->sendMessage($content);
                } else {
                    $text = "Impossibile modificare il titolo. Errore: ".$error;
                    $content = array('chat_id' => $Editor->getEditorId(), 'text' => $text, 'parse_mode' => "HTML"); 
                    $Telegram->sendMessage($content);
                }
                return;
            
            case "/editquestsummary":
                $Logger->logMethod("Editing quest summary.");
                
                $parameters = explode(",", $words[1], 2);
                $quest_id = trim($parameters[0]);
                $summary = ucfirst(trim($parameters[1]));
                
                $error = $Quest->editSummary($quest_id, $summary);
                
                if ($error === false) {
                    $text = "Riassunto modificato correttamente!";
                    $content = array('chat_id' => $Editor->getEditorId(), 'text' => $text, 'parse_mode' => "HTML"); 
                    $Telegram->sendMessage($content);
                } else {
                    $text = "Impossibile modificare il riassunto. Errore: ".$error;
                    $content = array('chat_id' => $Editor->getEditorId(), 'text' => $text, 'parse_mode' => "HTML"); 
                    $Telegram->sendMessage($content);
                }
                return;
                
            case "/editmessageprequest":
                $Logger->logMethod("Editing message pre quest.");
                
                $parameters = explode(",", $words[1], 2);
                $quest_id = trim($parameters[0]);
                $msg_pre_quest = ucfirst(trim($parameters[1]));
                
                $error = $Quest->editMessagePreQuest($quest_id, $msg_pre_quest);
                
                if ($error === false) {
                    $text = "Messaggio modificato correttamente!";
                    $content = array('chat_id' => $Editor->getEditorId(), 'text' => $text, 'parse_mode' => "HTML"); 
                    $Telegram->sendMessage($content);
                } else {
                    $text = "Impossibile modificare il messaggio. Errore: ".$error;
                    $content = array('chat_id' => $Editor->getEditorId(), 'text' => $text, 'parse_mode' => "HTML"); 
                    $Telegram->sendMessage($content);
                }
                return;
                
            case "/editmessageduringquest":
                $Logger->logMethod("Editing message during quest.");
                
                $parameters = explode(",", $words[1], 2);
                $quest_id = trim($parameters[0]);
                $msg_during_quest = ucfirst(trim($parameters[1]));
                
                $error = $Quest->editMessageDuringQuest($quest_id, $msg_during_quest);
                
                if ($error === false) {
                    $text = "Messaggio modificato correttamente!";
                    $content = array('chat_id' => $Editor->getEditorId(), 'text' => $text, 'parse_mode' => "HTML"); 
                    $Telegram->sendMessage($content);
                } else {
                    $text = "Impossibile modificare il messaggio. Errore: ".$error;
                    $content = array('chat_id' => $Editor->getEditorId(), 'text' => $text, 'parse_mode' => "HTML"); 
                    $Telegram->sendMessage($content);
                }
                return;
                
            case "/editmessagepostquest":
                $Logger->logMethod("Editing message post quest.");
                
                $parameters = explode(",", $words[1], 2);
                $quest_id = trim($parameters[0]);
                $msg_post_quest = ucfirst(trim($parameters[1]));
                
                $error = $Quest->editMessagePostQuest($quest_id, $msg_post_quest);
                
                if ($error === false) {
                    $text = "Messaggio modificato correttamente!";
                    $content = array('chat_id' => $Editor->getEditorId(), 'text' => $text, 'parse_mode' => "HTML"); 
                    $Telegram->sendMessage($content);
                } else {
                    $text = "Impossibile modificare il messaggio. Errore: ".$error;
                    $content = array('chat_id' => $Editor->getEditorId(), 'text' => $text, 'parse_mode' => "HTML"); 
                    $Telegram->sendMessage($content);
                }
                return;
            
            case "/editquestparameters":
                $Logger->logMethod("Editing quest name.");
                
                $parameterss = explode(",", $words[1], 2);
                $quest_id = trim($parameterss[0]);
                $parameters = trim($parameterss[1]);
                
                $error = $Quest->editParameters($quest_id, $parameters);
                
                if ($error === false) {
                    $text = "Parametri della quest modificati correttamente!";
                    $content = array('chat_id' => $Editor->getEditorId(), 'text' => $text, 'parse_mode' => "HTML"); 
                    $Telegram->sendMessage($content);
                } else {
                    $text = "Impossibile modificare i parametri della quest. Errore: ".$error;
                    $content = array('chat_id' => $Editor->getEditorId(), 'text' => $text, 'parse_mode' => "HTML"); 
                    $Telegram->sendMessage($content);
                }
                return;
                
            case "/editquestrewards":
                $Logger->logMethod("Editing quest name.");
                
                $parameters = explode(",", $words[1], 2);
                $quest_id = trim($parameterss[0]);
                $rewards = trim($parameterss[1]);
                
                $error = $Quest->editRewards($quest_id, $rewards);
                
                if ($error === false) {
                    $text = "Ricompense modificato correttamente!";
                    $content = array('chat_id' => $Editor->getEditorId(), 'text' => $text, 'parse_mode' => "HTML"); 
                    $Telegram->sendMessage($content);
                } else {
                    $text = "Impossibile modificare le ricompense. Errore: ".$error;
                    $content = array('chat_id' => $Editor->getEditorId(), 'text' => $text, 'parse_mode' => "HTML"); 
                    $Telegram->sendMessage($content);
                }
                return;
                
                
            // ---------------------------------------- Items Section ----------------------------------------
            case "/additem":
                $Logger->logMethod("Adding item.");
                
                if (permissionDenied($Editor->getEditorId(), $Editor->getLevel(), 2)) return;
                
                $parameters = explode(",", $words[1], 4);
                $type = trim(strtolower($parameters[0]));
                $ethnicity = trim(strtolower($parameters[1]));
                $name = ucfirst(trim($parameters[2]));
                $description = ucfirst(trim($parameters[3]));
                
                $result = $Terrain->addItem($type, $ethnicity, $name, $description, $Editor->getEditorId());
                
                if (is_numeric($result)) {
                    $text = "Oggetto aggiunto correttamente! item_id: ".$result;
                    $content = array('chat_id' => $Editor->getEditorId(), 'text' => $text, 'parse_mode' => "HTML"); 
                    $Telegram->sendMessage($content);
                } else {
                    $text = "Impossibile aggiungere oggetto. Errore: ".$result;
                    $content = array('chat_id' => $Editor->getEditorId(), 'text' => $text, 'parse_mode' => "HTML"); 
                    $Telegram->sendMessage($content);
                }
                return;
                
            case "/edititemname":
                $Logger->logMethod("Editing item name.");
                
                if (permissionDenied($Editor->getEditorId(), $Editor->getLevel(), 2)) return;
                
                $parameters = explode(",", $words[1], 2);
                $item_id = trim($parameters[0]);
                $name = ucfirst(trim($parameters[1]));
                
                $error = $Terrain->editItemName($idet_id, $name);
                
                if ($error === false) {
                    $text = "Nome aggiornato!";
                    $content = array('chat_id' => $Editor->getEditorId(), 'text' => $text, 'parse_mode' => "HTML"); 
                    $Telegram->sendMessage($content);
                } else {
                    $text = "Impossibile aggiornare name. Errore: ".$error;
                    $content = array('chat_id' => $Editor->getEditorId(), 'text' => $text, 'parse_mode' => "HTML"); 
                    $Telegram->sendMessage($content);
                }
                return;
                
            case "/edititemdescription":
                $Logger->logMethod("Editing item description.");
                
                if (permissionDenied($Editor->getEditorId(), $Editor->getLevel(), 2)) return;
                
                $parameters = explode(",", $words[1], 2);
                $item_id = trim($parameters[0]);
                $description = ucfirst(trim($parameters[1]));
                
                $error = $Terrain->editItemDescription($item_id, $description);
                
                if ($error === false) {
                    $text = "Descrizione aggiornata!";
                    $content = array('chat_id' => $Editor->getEditorId(), 'text' => $text, 'parse_mode' => "HTML"); 
                    $Telegram->sendMessage($content);
                } else {
                    $text = "Impossibile aggiornare descrizione. Errore: ".$error;
                    $content = array('chat_id' => $Editor->getEditorId(), 'text' => $text, 'parse_mode' => "HTML"); 
                    $Telegram->sendMessage($content);
                }
                return;
                
            case "/edititemstatistics":
                $Logger->logMethod("Editing items statistics.");
                
                if (permissionDenied($Editor->getEditorId(), $Editor->getLevel(), 2)) return;
                
                $parameters = explode(",", $words[1], 2);
                $item_id = trim($parameters[0]);
                $statistics = trim($parameters[1]);
                
                $error = $Terrain->editItemStatistics($item_id, $statistics);
                
                if ($error === false) {
                    $text = "Statistiche aggiornate!";
                    $content = array('chat_id' => $Editor->getEditorId(), 'text' => $text, 'parse_mode' => "HTML"); 
                    $Telegram->sendMessage($content);
                } else {
                    $text = "Impossibile aggiornare le statistiche. Errore: ".$error;
                    $content = array('chat_id' => $Editor->getEditorId(), 'text' => $text, 'parse_mode' => "HTML"); 
                    $Telegram->sendMessage($content);
                }
                return;
                
            case "/creategiveaway":
            	$Logger->logMethod("Adding giveaway.");
                
                if (permissionDenied($Editor->getEditorId(), $Editor->getLevel(), 2)) return;
                
                $parameters = explode(",", $words[1], 6);
                $issuer = trim($parameters[0]);
                $item_id = trim($parameters[1]);
                $rarity = trim($parameters[2]);
                $level = trim($parameters[3]);
                $quantity = trim($parameters[4]);
                $message = trim($parameters[5]);
                
                $error = $Terrain->addGiveaway($issuer, $item_id, $rarity, $level, $quantity, $message);
                
                if ($error === false) {
                    $text = "Regalo aggiunto!";
                    $content = array('chat_id' => $Editor->getEditorId(), 'text' => $text, 'parse_mode' => "HTML"); 
                    $Telegram->sendMessage($content);
                } else {
                    $text = "Impossibile aggiungere il regalo. Errore: ".$error;
                    $content = array('chat_id' => $Editor->getEditorId(), 'text' => $text, 'parse_mode' => "HTML"); 
                    $Telegram->sendMessage($content);
                }
            	return;
                
                
            // ---------------------------------------- Administration Section ----------------------------------------
            case "/upgrade":
                $sign = "+";
            case "/downgrade":
                if (permissionDenied($Editor->getEditorId(), $Editor->getLevel(), 3)) return;
                
                //if (!isset($sign)) $sign = "-";
                
                $username = trim($words[1]);
                
                $result = $Editor->executeQuery("SELECT * FROM wt_editors WHERE username = ?", "s", $username);
                if (!$result->num_rows) {
                    $text = "Username non trovato!";
                    $content = array('chat_id' => $Editor->getEditorId(), 'text' => $text, 'parse_mode' => "HTML"); 
                    $Telegram->sendMessage($content);
                    return;
                }
                $row = $result->fetch_array();
                $editor_id = $row["editor_id"];
                $level = $row["level"];
                
                if ($sign == "+") {
                    $level += 1;
                    $text = "Congratulazioni! Sei stato promosso dal Gran Pinguino. Ora sei un editor di livello ".$level;
                } else {
                    $level -= 1;
                    $text = "Sei stato degradato dal Gran Pinguino. Ora sei un editor di livello ".$level;
                }
                
                $Editor->executeQuery("UPDATE wt_editors SET level = ? WHERE username = ?", "is", array($level, $username));
                
                $content = array('chat_id' => $editor_id, 'text' => $text, 'parse_mode' => "HTML"); 
                $Telegram->sendMessage($content);
                
                $text = "Livello aggiornato.";
                $content = array('chat_id' => $Editor->getEditorId(), 'text' => $text, 'parse_mode' => "HTML"); 
                $Telegram->sendMessage($content);
                return;
        }
    }
}

function permissionDenied($chat_id, $level, $limit)
{
    if ($level < $limit) {
        $text = "Non hai i permessi necessari per utilizzare questo comando.";
        $content = array('chat_id' => $chat_id, 'text' => $text, 'parse_mode' => "HTML"); 
        $Telegram->sendMessage($content);
        return true;
    } else return false;
}
?>
