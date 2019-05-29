<?php

class Editor extends Database
{
// User
    protected $editor_id;
    protected $username;

// Status
    protected $status;
    protected $level;
    protected $terrain;
    
    
    // Getters and Setters
    
    public function setUsername($username)
    {
        $this->username = $username;
    }
    
    public function getUsername()
    {
        return $this->username;
    }
    
    public function setEditorId($editor_id)
    {
        $this->editor_id = $editor_id;
    }
    
    public function getEditorId()
    {
        return $this->editor_id;
    }
    
    public function setLevel($level)
    {
        $this->level = $level;
    }
    
    public function getLevel()
    {
        return $this->level;
    }
    
    public function setTerrain($terrain)
    {
        $this->terrain = $terrain;
    }
    
    public function getTerrain()
    {
        return $this->terrain;
    }
    
    
    // Methods
    
    public function getEditor()
    {
        $result = $this->executeQuery("SELECT * FROM wt_editors WHERE editor_id=?", "i", $this->getEditorId());
        if (!$result->num_rows) return false;
        
        // Save user's data
        $row = $result->fetch_array();
        $this->setEditorId($row["editor_id"]);
        $this->setUsername($row["username"]);
        $this->setLevel($row["level"]);
        $this->setTerrain($row["terrain"]);
        //$this->status = $row["status"];
        //$this->object_id = $row["object_id"];
        //if (isset($this->language_code) and $this->language_code != $row["language_code"]) updateLanguageCode($Logger, $Telegram, $User);
        return true;
    }

    /* addUser()
    This function is called when the user is not saved in the database, thus is a new user.
    It records the new user's info and start the tutorial by changing the status and by sending the first step of the tutorial.
    */
    public function addEditor()
    {
        $this->setTerrain(1);// Substitute with the in-game terrain
        $this->setLevel(0);
        
        $this->executeQuery(
            "INSERT INTO wt_editors VALUES(?,?,?,?)",
            "isii",
            array(
                $this->getEditorId(),
                $this->getUsername(),
                $this->getLevel(),
                $this->getTerrain()
            )
        );
        
        
        // Welcome message + Intro to the tutorial
        /*$text = "Welcome, *" . $User->username . "*! This is *Shuffle It*, a new social experience.\n\nYour journey is about to begin.\n\nI will guide you through the *tutorial*, get ready!";
        $content = array('chat_id' => $User->chat_id, 'text' => $text, 'parse_mode' => "Markdown"); 
        $Telegram->sendMessage($content);*/
    
        /*$User = getUser($Logger, $Telegram, $User);
        changeStatus($Logger, $User, "tutorial_1");*/
    }
    
    public function updateTerrain($terrain)
    {
        $this->executeQuery("UPDATE wt_editors SET terrain = ? WHERE editor_id = ?", "ii", array($terrain, $this->getEditorId()));
    }
    
    public function updateUsername()
    {
        $this->logMethod("Updating username. Editor: ".$this->getEditorId());
        
        $result = $this->executeQuery("UPDATE wt_editors SET username = ? WHERE editor_id = ?", "si", array($this->getUsername(), $this->getEditorId()));
    }
}
