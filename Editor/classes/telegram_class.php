<?php

/*
####################
## Telegram Class ##
####################
*/

class Telegram {
    private $bot_token = "";
    private $data = array();
    private $updates = array();
    private $reply = array();
    
    public function __construct($bot_token) {
        $this->bot_token = $bot_token;
        $this->data = $this->getData();
    }
    
    // Get the data of the current message
    public function getData() {
        if (empty($this->data)) {
            $rawData = file_get_contents("php://input");
            return json_decode($rawData, true);
        } else {
            return $this->data;
        }
    }
    
    public function endpoint($method, array $content, $post = true) {
        $url = 'https://api.telegram.org/bot' . $this->bot_token . '/' . $method;
        if ($post) $this->reply = $this->sendAPIRequest($url, $content);
        else $this->reply = $this->sendAPIRequest($url, array(), false);
        $this->reply = json_decode($this->reply, true);
        return $this->reply;
    }
    
    private function sendAPIRequest($url, array $content, $post = true) {
        if (isset($content['chat_id'])) {
            $url = $url . "?chat_id=" . $content['chat_id'];
            unset($content['chat_id']);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if ($post) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
    
        public function getUpdates($offset = 0, $limit = 100, $timeout = 0, $update = true) {
        $content = array('offset' => $offset, 'limit' => $limit, 'timeout' => $timeout);
        $this->updates = $this->endpoint("getUpdates", $content);
        if ($update) {
            if(count($this->updates["result"]) >= 1) { //for CLI working.
                $last_element_id = $this->updates["result"][count($this->updates["result"]) - 1]["update_id"] + 1;
                $content = array('offset' => $last_element_id, 'limit' => "1", 'timeout' => $timeout);
                $this->endpoint("getUpdates", $content);
            }
        }
        return $this->updates;
    }
    
    // Get the number of updates
    public function UpdateCount() {
        return count($this->updates["result"]);
    }
    
    public function serveUpdate($update) {
        $this->data = $this->updates["result"][$update];
    }

    public function gettData() {
        return $this->data;
    }
    // Reply
    public function getReply() {
        return $this->reply;
    }


    // Methods
    public function sendMessage(array $content) {
        return $this->endpoint("sendMessage", $content);
    }
    
    public function sendPhoto(array $content) {
        return $this->endpoint("sendPhoto", $content);
    }
    
    public function sendAudio(array $content) {
        return $this->endpoint("sendAudio", $content);
    }
    
    public function sendDocument(array $content) {
        return $this->endpoint("sendDocument", $content);
    }
    
    public function sendSticker(array $content) {
        return $this->endpoint("sendSticker", $content);
    }
    
    public function sendVideo(array $content) {
        return $this->endpoint("sendVideo", $content);
    }
    
    public function sendVoice(array $content) {
        return $this->endpoint("sendVoice", $content);
    }
    
    public function sendLocation(array $content) {
        return $this->endpoint("sendLocation", $content);
    }
    
    public function sendVenue(array $content) {
        return $this->endpoint("sendVenue", $content);
    }
    
    public function sendContact(array $content) {
        return $this->endpoint("sendContact", $content);
    }
    
    public function sendChatAction(array $content) {
        return $this->endpoint("sendChatAction", $content);
    }
    
    public function getFile(array $content) {
        return $this->endpoint("getFile", $content);
    }
    
    public function answerCallbackQuery(array $content) {
        return $this->endpoint("answerCallbackQuery", $content);
    }
    
    // Editing
    public function editMessageText(array $content) {
        return $this->endpoint("editMessageText", $content);
    }
    public function editMessageCaption(array $content) {
        return $this->endpoint("editMessageCaption", $content);
    }
    public function editMessageReplyMarkup(array $content) {
        return $this->endpoint("editMessageReplyMarkup", $content);
    }
    
    // Reply Keyboard Markup
    public function replyKeyboardMarkup(array $options, $resize = false, $onetime = false, $selective = true) {
        $replyMarkup = array(
            'keyboard' => $options,
            'resize_keyboard' => $resize,
            'one_time_keyboard' => $onetime,
            'selective' => $selective
        );
        $encodedMarkup = json_encode($replyMarkup, true);
        return $encodedMarkup;
    }
    
    public function replyKeyboardRemove($remove = false, $selective = true) {
        $replyMarkup = array(
            'remove_keyboard' => $remove,
            'selective' => $selective
        );
        return json_encode($replyMarkup, true);
    }
    
    public function inlineKeyboardMarkup(array $options) {
        $replyMarkup = array('inline_keyboard' => $options,);
        return json_encode($replyMarkup, true);
    }
    
    public function inlineKeyboardButton($text, $url = "", $callback_data = "", $switch_inline_query = "") {
        $replyMarkup = array('text' => $text);
        
        if ($url != "") $replyMarkup['url'] = $url;
        else if ($callback_data != "") $replyMarkup['callback_data'] = $callback_data;
        else if ($switch_inline_query != "") $replyMarkup['switch_inline_query'] = $switch_inline_query;

        return $replyMarkup;
    }



    // Access data
    public function Message() {
        if(isset($this->data["message"])) return $this->data["message"];
        else return false;
    }
    public function Message_Id() {
        return $this->data["message"]["message_id"];
    }
    public function User_Id() {
        return $this->data["message"]["from"]["id"];
    }
    public function Username() {
        if(isset($this->data["message"]["from"]["username"])) return $this->data["message"]["from"]["username"];
        else return false;
    }
    public function Language_Code() {
        if(isset($this->data["message"]["from"]["language_code"])) return $this->data["message"]["from"]["language_code"];
        else return false;
    }
    public function Chat_Id() {
        return $this->data["message"]["chat"]["id"];
    }
    public function Type() {
        if(isset($this->data["message"]["chat"]["type"])) return $this->data["message"]["chat"]["type"];
        else return false;
    }
    public function Text() {
        if(isset($this->data["message"]["text"])) return $this->data["message"]["text"];
        else return false;
    }
    public function Photo() {
        if(isset($this->data["message"]["photo"])) return $this->data["message"]["photo"];
        else return false;
    }
    public function Video() {
        if(isset($this->data["message"]["video"])) return $this->data["message"]["video"];
        else return false;
    }
    public function Sticker() {
        if(isset($this->data["message"]["sticker"])) return $this->data["message"]["sticker"];
        else return false;
    }
    public function Document() {
        if(isset($this->data["message"]["document"])) return $this->data["message"]["document"];
        else return false;
    }
    public function Voice() {
        if(isset($this->data["message"]["voice"])) return $this->data["message"]["voice"];
        else return false;
    }
    public function Audio() {
        if(isset($this->data["message"]["audio"])) return $this->data["message"]["audio"];
        else return false;
    }
    
    // Groups
    public function New_Chat_Member_Id() {
        if(isset($this->data["message"]["new_chat_member"]["id"])) return $this->data["message"]["new_chat_member"]["id"];
        else return false;
    }
    
    // Callbacks
    public function Callback_Query() {
        if(isset($this->data["callback_query"])) return $this->data["callback_query"];
        else return false;
    }
    public function Callback_Id() {
        return $this->data["callback_query"]["id"];
    }
    public function Callback_User_Id() {
        return $this->data["callback_query"]["from"]["id"];
    }
    public function Callback_Username() {
        return $this->data["callback_query"]["from"]["username"];
    }
    public function Callback_Language_Code() {
        return $this->data["callback_query"]["from"]["language_code"];
    }
    public function Callback_Data() {
        return $this->data["callback_query"]["data"];
    }
    public function Callback_Chat_Id() {
        return $this->data["callback_query"]["message"]["chat"]["id"];
    }
    public function Callback_Type() {
        return $this->data["callback_query"]["message"]["chat"]["type"];
    }
    public function Callback_Message_Id() {
        return $this->data["callback_query"]["message"]["message_id"];
    }

    // Inline Queries
    public function Inline_Query() {
        if(isset($this->data["inline_query"])) return $this->data["inline_query"];
        else return false;
    }
    public function Inline_Id() {
        return $this->data["inline_query"]["id"];
    }
    public function Inline_Query_Query() {
        return $this->data["inline_query"]["query"];
    }
    public function Inline_User_Id() {
        return $this->data["inline_query"]["from"]["id"];
    }
    public function answerInlineQuery($id, $content) {
        return $this->endpoint("answerInlineQuery", ["inline_query_id"=>$id, "results"=>json_encode($content)]);
    }
    public function InlineQueryResultArticle($id, $title, $input_message_content, $inline_keyb) {
        $results = array(
            "type" => "article",
            "id" => $id,
            "title" => $title,
            "input_message_content" => $input_message_content,
            "reply_markup" => $inline_keyb
        );
        
        return $results;
    }
    public function InputTextMessageContent($message_text, $parse_mode, $disable_web_page_preview) {
        $content = array(
            'message_text' => $message_text,
            'parse_mode' => $parse_mode,
            'disable_web_page_preview' => $disable_web_page_preview
        );
        return $content;
    }
    public function InputLocationMessageContent($latitude, $longitude) {
        $content = array(
            'latitude' => $latitude,
            'longitude' => $longitude
        );
        return $content;
    }
    public function InputVenueMessageContent($latitude, $longitude, $title, $address, $foursquare_id) {
        $content = array(
            'latitude' => $latitude,
            'longitude' => $longitude,
            'title' => $title,
            'address' => $address,
            'foursquare_id' => $foursquare_id
        );
        return json_encode($content, true);
    }
    public function InputContactMessageContent($phone_number, $first_name, $last_name = "") {
        $content = array(
            'phone_number' => $phone_number,
            'first_name' => $first_name,
            'last_name' => $last_name
        );
        return json_encode($content, true);
    }
}

?>
