<?
function analyzeGroupMessage($Logger, $Telegram, $User) {
    $Logger->tMethod("Analyzing text from group.");
    
    $User->chat_id = $Telegram->Chat_Id();
    
    switch($Telegram->Text()){
        case "/restart":
        case "/restart@ShuffleItBot":
        case "/show_favourites":
        case "/show_favourites@ShuffleItBot":
        case "/my_achievements":
        case "/my_achievements@ShuffleItBot":
            $text = "Sorry, this command can only be used in a private chat with @ShuffleItBot.";
            $content = array('chat_id' => $User->chat_id, 'text' => $text, 'reply_to_message_id' => $Telegram->Message_Id(), 'parse_mode' => "Markdown"); 
            $Telegram->sendMessage($content);
            return;
    }
    
    if($Telegram->New_Chat_Member_Id() == "295649194") {
        $Logger->tMethod("Adding bot to group.");
        addBotToGroupAchievement($Logger, $Telegram, $User);
        $text = "Hello people! Are you ready to *Shuffle* things up? Let's get started. Use the commands to contact me. Send /shuffle\_it to begin with.";
        $content = array('chat_id' => $User->chat_id, 'text' => $text, 'parse_mode' => "Markdown"); 
        $Telegram->sendMessage($content);
        return;
    } else return;
}
?>
