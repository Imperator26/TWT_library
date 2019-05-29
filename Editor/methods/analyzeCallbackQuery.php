<?php
function analyzeCallbackQuery($Logger, $Telegram, $Editor, $Terrain, $Quest, $keyboard_markup) {
    $Logger->logMethod("Analyzing callback.");
    
    $words = explode("_", $Telegram->Callback_Data());
    switch ($words[0]) {
        case "request":
            $content = array('callback_query_id' => $Telegram->Callback_Id());
            $Telegram->answerCallbackQuery($content);
            
            $editor_id = $words[1];
            switch ($words[2]) {
                case "rejected":
                    $in_keyb_buttons = array(array($Telegram->inlineKeyboardButton("Rejected.", "", "void", "")));
                    $inline_keyb = $Telegram->inlineKeyboardMarkup($in_keyb_buttons);
                    $content = array('chat_id' => $Telegram->Callback_Chat_Id(), 'message_id' => $Telegram->Callback_Message_Id(), 'reply_markup' => $inline_keyb);
                    $Telegram->editMessageReplyMarkup($content);
                    return;
                
                case "accepted":
                    $Editor->executeQuery("UPDATE wt_editors SET level = ? WHERE editor_id = ?", "ii", array(1, $editor_id));
                    
                    $in_keyb_buttons = array(array($Telegram->inlineKeyboardButton("Accepted!", "", "void", "")));
                    $inline_keyb = $Telegram->inlineKeyboardMarkup($in_keyb_buttons);
                    $content = array('chat_id' => $Telegram->Callback_Chat_Id(), 'message_id' => $Telegram->Callback_Message_Id(), 'reply_markup' => $inline_keyb);
                    $Telegram->editMessageReplyMarkup($content);
                    
                    $text = "La tua richiesta per diventare un <i>editor</i> è stata accettata con successo dal <b>Gran Pinguino</b>.";
                    $content = array('chat_id' => $editor_id, 'text' => $text, 'parse_mode' => "HTML");
                    $Telegram->sendMessage($content);
                    
                    $text = "Ora sei ufficialmente un <b>editor</b> di <b>TWT</b>!";
                    $content = array('chat_id' => $editor_id, 'text' => $text, 'parse_mode' => "HTML", 'reply_markup' => $keyboard_markup);
                    $Telegram->sendMessage($content);
                    return;
            }
            return;
        
        case "void":
            $content = array('callback_query_id' => $Telegram->Callback_Id());
            $Telegram->answerCallbackQuery($content);
            return;
    }
}

