<?
function everyOtherMessage($Logger, $Telegram, $User) {
// Every other commands
    switch($Telegram->Text()){
        case "/shuffle_it":
        case "/shuffle_it@ShuffleItBot":
            shuffleIt($Logger, $Telegram, $User);
            return;
            
        case "/instructions":
        case "/instructions@ShuffleItBot":
            $instructions = "Instructions 📖 :
🔹Use the button Shuffle It 🔀 and let the magic begin. You will receive a file: it can be anything from a picture of you in 6th grade, to a video of giraffes fighting, to a recipe with Kyogre's claws.
🔹You can rate the file you received from one ⭐️ to three 🌟🌟🌟 stars. We will keep track of it and then Shuffle another file for you automatically.
🔹With the ❤️ button you can save the file in your favorites so that you can easily find it later (use /show\_favourites).
🔹Of course you can contribute by uploading your own files using the command Upload 📎.
🔹For multiple upload all at once use Multiple Files 📦. When you're done press « Back.
🔹The button My Stats 📊 gives you some cool statistics about you and your uploaded files.
🔹If you find the content not appropriate press Flag 🚫. We will look into it.
New Functions will be introduced soon so keep on Shuffling 🔀 !";
            $content = array('chat_id' => $User->chat_id, 'text' => $instructions, 'parse_mode' => "Markdown"); 
            $Telegram->sendMessage($content);
            return;
        
        case "/changelog":
        case "/changelog@ShuffleItBot":
            $changelog = "*Complete changelog:*\n
- *1.0.0* (First stable version)
*Shuffle It* is released with the least amount of functions which are the core of the entire bot and the idea behind it.

- *1.0.1*
Small fixes.

- *1.1.0*
My Stats 📊 is now working. The user may now see the stats about his/her content and more.

- *1.1.1*
No username bug fixed.

- *1.2.0*
Flag 🚫 is now working. The user may now flag content.

- *1.2.1*
Massive Upload is now possible.

- *1.3.0*
Everyone can now add content to the favourites ❤️.

- *1.3.1*
Small update that fixes the favourite button.

- *1.3.2*
Database optimization.

- *1.4.0*
Now the system checks for duplicates before uploading any file.;

- *1.4.1*
Minor improvements.

- *1.5.0*
Shuffle It can now be added to groups!

- *2.0.0*
*Shuffle It* has a completely new UI. Inline buttons are now available to Shuffle things up. The code has been optimized and now Shuffle It is even faster than it was in earlier versions.

- *2.1.0*
*Achievements* 🏅 are finally here! Unlock them all! Use /show\_achievements.

- *2.2.0*
Bug fixes. Removed keyboard markup. New 🏡 Menu 🏡.

- *2.2.1*
Bug fixes.

- *2.2.2*
New tutorial!

- *2.2.3*
Minor updates. The content can now be filtered by languange!

- *2.2.4*
Added a button to the menu to invite friends.

- *2.2.5*
Minor improvements. Language now detected from Telegram's language code.

";// *2.2.6*
//";
            $content = array('chat_id' => $User->chat_id, 'text' => $changelog, 'parse_mode' => "Markdown"); 
            $Telegram->sendMessage($content);
            return;
            
        case "/show_achievements":
        case "/show_achievements@ShuffleItBot":
            $achievements = array(
            array("upload_1", "\n• *One small step for a man...*\nUpload your *first* file!\n⭐️\n"),
            array("upload_10", "\n• *Two-handed high five.*\nUpload *10* files.\n⭐️⭐️\n"),
            array("upload_25", "\n• *Addiction.*\nUpload *25* files.\n⭐️⭐️⭐️\n"),
            array("upload_50", "\n• *Fifty-Fifty.*\nUpload *50* files.\n⭐️⭐️⭐️⭐️\n"),
            array("upload_100", "\n• *Keep it 100.*\nUpload *100* files.\n⭐️⭐️⭐️⭐️⭐️\n"),
            array("upload_250", "\n• *Let's get serious!*\nUpload *250* files.\n🌟"),
            array("upload_500", "\n• *...and 500 times more!*\nUpload *500* files.\n🌟🌟\n"),
            array("upload_1000", "\n• *A picture is worth a thousand words.*\nUpload *1000* files.\n🌟🌟🌟\n"),
            array("upload_5000", "\n• *Five thousand and counting.*\nUpload *5000* files.\n🌟🌟🌟🌟\n"),
            array("upload_10000", "\n• *Call me Master!*\nUpload *10000* files.\n🌟🌟🌟🌟🌟\n"),
            array("votes_5", "\n• *The apprentice astronomer.*\nVote *5* files.\n⭐️\n"),
            array("votes_50", "\n• *A starry sky.*\nVote *50* files.\n⭐️⭐️\n"),
            array("votes_100", "\n• *White dwarf.*\nVote *100* files.\n⭐️⭐️⭐️\n"),
            array("votes_250", "\n• *Blue hypergiant.*\nVote *250* files.\n⭐️⭐️⭐️⭐️\n"),
            array("votes_500", "\n• *Black hole.*\nVote *500* files.\n⭐️⭐️⭐️⭐️⭐️\n"),
            array("votes_1000", "\n• *Galaxy explorer.*\nVote *1000* files.\n🌟\n"),
            array("votes_2500", "\n• *Halley's comet.*\nVote *2500* files.\n🌟🌟\n"),
            array("votes_5000", "\n• *Ursa Major.*\nVote *5000* files.\n🌟🌟🌟\n"),
            array("votes_15000", "\n• *Ursa Minor.*\nVote *15000* files.\n🌟🌟🌟🌟\n"),
            array("votes_25000", "\n• *The North Star.*\nVote *25000* files.\n🌟🌟🌟🌟🌟\n"),
            array("views_10", "\n• *Look ma, no hands!*\nReach a total of *10* views with your files.\n⭐️\n"),
            array("views_100", "\n• ✅ *Visualized.*\nReach a total of *100* views with your files.\n⭐️⭐️\n"),
            array("views_500", "\n• *Infamous.*\nReach a total of *500* views with your files.\n⭐️⭐️⭐️\n"),
            array("views_1000", "\n• *The bigger picture.*\nReach a total of *1000* views with your files.\n⭐️⭐️⭐️⭐️\n"),
            array("views_5000", "\n• *Hidden in plain sight.*\nReach a total of *5000* views with your files.\n⭐️⭐️⭐️⭐️⭐️\n"),
            array("views_10000", "\n• *The show must go on!*\nReach a total of *10000* views with your files.\n🌟\n"),
            array("views_50000", "\n• *Showman.*\nReach a total of *50000* views with your files.\n🌟🌟\n"),
            array("views_100000", "\n• *Eyes on me!*\nReach a total of *100000* views with your files.\n🌟🌟🌟\n"),
            array("views_500000", "\n• *This is called fame my friend.*\nReach a total of *500000* views with your files.\n🌟🌟🌟🌟\n"),
            array("views_1000000", "\n• *Six figures!*\nReach a total of *1000000* views with your files.\n🌟🌟🌟🌟🌟\n"),
            array("addBotToGroup", "\n• *Let the party begin!*\nAdd bot to a group.\n🏅\n")
            );
            
            $i = 0;
            $text = "*Complete list of achievements:*\n";
            while($i < 31) {
                $text .= $achievements[$i][1];
                $i++;
            }
            
            $content = array('chat_id' => $User->chat_id, 'text' => $text, 'parse_mode' => "Markdown"); 
            $Telegram->sendMessage($content);
            return;
        
        /*default:
            // Default message
            $text = "Sorry, I didn't get that.";
            $content = array('chat_id' => $User->chat_id, 'text' => $text); 
            $Telegram->sendMessage($content);
            return;*/
}
}
?>
