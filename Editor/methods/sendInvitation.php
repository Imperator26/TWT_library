<?
function sendInvitation($Logger, $Telegram, $User) {
    $title = "Invite a friend... 🚀";
    $input_message_content = $Telegram->InputTextMessageContent("Are you ready to *Shuffle It*? 🔀", "Markdown", false);
    $in_keyb_buttons = array(    array($Telegram->inlineKeyboardButton("⭐️", "http://t.me/ShuffleItBot", "void", "")));
    $inline_keyb = array('inline_keyboard' => $in_keyb_buttons);
    $content = $Telegram->InlineQueryResultArticle("invitation", $title, $input_message_content, $inline_keyb);
    $Logger->tRegular(var_export($content, true));
    $Telegram->answerInlineQuery($Telegram->Inline_Id(), [$content]);
}
?>
