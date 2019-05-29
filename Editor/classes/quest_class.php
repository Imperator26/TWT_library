<?php

class Quest extends Database
{
    protected $quest_id;
    protected $editor_id;
    protected $issuer;
    protected $que_number;
    protected $is_main;
    protected $name;
    protected $type;
    protected $summary;
    protected $msg_pre_quest;
    protected $msg_during_quest;
    protected $msg_post_quest;
    protected $quest_parameters;
    protected $money;
    protected $experience;
    
    
    // Getters and Setters
    public function setQuestId($quest_id)
    {
        $this->quest_id = $quest_id;
    }
    
    public function getQuestId()
    {
        return $this->quest_id;
    }
    
    public function setEditorId($editor_id)
    {
        $this->editor_id = $editor_id;
    }
    
    public function getEditorId()
    {
        return $this->editor_id;
    }
    
    public function setIssuer($issuer)
    {
        $this->issuer = $issuer;
    }
    
    public function getIssuer()
    {
        return $this->issuer;
    }
    
    public function setQueNumber($que_number)
    {
        $this->que_number = $que_number;
    }
    
    public function getQueNumber()
    {
        return $this->que_number;
    }
    
    public function setIsMain($is_main)
    {
        $this->is_main = $is_main;
    }
    
    public function getIsMain()
    {
        return $this->is_main;
    }
    
    public function setName($name)
    {
        $this->name = $name;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function setType($type)
    {
        $this->type = $type;
    }
    
    public function getType()
    {
        return $this->type;
    }
    
    public function setSummary($summary)
    {
        $this->summary = $summary;
    }
    
    public function getSummary()
    {
        return $this->summary;
    }
    
    public function setMessagePreQuest($msg_pre_quest)
    {
        $this->msg_pre_quest = $msg_pre_quest;
    }
    
    public function getMessagePreQuest()
    {
        return $this->msg_pre_quest;
    }
    
    public function setMessageDuringQuest($msg_during_quest)
    {
        $this->msg_during_quest = $msg_during_quest;
    }
    
    public function getMessageDuringQuest()
    {
        return $this->msg_during_quest;
    }
    
    public function setMessagePostQuest($msg_post_quest)
    {
        $this->msg_post_quest = $msg_post_quest;
    }
    
    public function getMessagePostQuest()
    {
        return $this->msg_post_quest;
    }
    
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
    }
    
    public function getParameters()
    {
        return $this->parameters;
    }
    
    public function setMoney($money)
    {
        $this->money = $money;
    }
    
    public function getMoney()
    {
        return $this->money;
    }
    
    public function setExperience($experience)
    {
        $this->experience = $experience;
    }
    
    public function getExperience()
    {
        return $this->experience;
    }
    
    
    // Methods
    public function loadQuest($quest_id)
    {
        $result = $this->executeQuery("SELECT * FROM wt_quests WHERE quest_id = ?", "i", $quest_id);
        
        if (!$result->num_rows) return false;
        
        $row = $result->fetch_array();
        $this->setQuestId($quest_id);
        $this->setIssuer($row["issuer"]);
        $this->setQueNumber($row["que_number"]);
        $this->setIsMain($row["is_main"]);
        $this->setName($row["name"]);
        $this->setType($row["type"]);
        $this->setSummary($row["summary"]);
        $this->setMessagePreQuest($row["msg_pre_quest"]);
        $this->setMessageDuringQuest($row["msg_during_quest"]);
        $this->setMessagePostQuest($row["msg_post_quest"]);
        $this->setParameters($row["parameters"]);
        
        $rewards = explode("&", $row["rewards"]);
        $this->setMoney($rewards[0]);
        $this->setExperience($rewards[1]);
        
        return true;
    }
    
    public function addQuest($issuer, $type, $parameters, $rewards, $editor_id)
    {
        if (!isset($issuer) or !is_numeric($issuer))return "The latter <i>issuer</i> parameter is empty or is not a number.";
        if (!isset($type) or !is_string($type) or ($type != "kill" and $type != "talk" and $type != "give")) return "The <i>type</i> parameter is empty or is not a string or is not a possible option.";
        if (!isset($parameters) or !is_string($parameters)) return "The <i>parameters</i> field is empty or is not a valid string.";
        if (!isset($rewards) or !is_string($rewards)) return "The <i>rewards</i> field is empty or is not a valid string.";
        $is_main = true; //
        /*switch ($type) {
            case "kill":
                $parameters = $npc_id."%".$quantity;
                break;
            
            case "talk":
                $parameters = $npc_id;
                break;
            
            case "give":
                $parameters = $npc_id."%".$item_id."%".$quantity;
                break;
        }
        
        $rewards = $money."%".$experience;*/
        
        // Check if the name of the terrain has already been taken
        /*$result = $this->executeQuery("SELECT * FROM wt_quests WHERE name = ?", "s", $name);
        if ($result->num_rows) return "Name already in use.";*/
        
        // Count how many quests already are issued from the same NPC
        $result = $this->executeQuery("SELECT * FROM wt_quests WHERE issuer = ?", "i", $issuer);
        $que_number = $result->num_rows + 1;
        
        $this->executeQuery(
            "INSERT INTO wt_quests VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)",
            "iiiiissssssss",
            array(
                0,//quest_id
                $editor_id,
                $issuer,
                $que_number,
                $is_main,
                null,//name
                $type,
                $summary,
                null,//msg_pre_quest
                null,//msg_during_quest
                null,//msg_post_quest
                $parameters,
                $rewards
            )
        );
        return $this->getLastInsertedId();
    }
    
    public function editTitle($quest_id, $name)
    {
        // Check if the name is valid - WRITE A SEPERATE METHOD!!!!
        $this->executeQuery("UPDATE wt_quests SET name = ? WHERE quest_id = ?", "si", array($name, $quest_id));
        return false;
    }
    
    public function editSummary($quest_id, $summary)
    {
        // Check if the summary is valid
        $this->executeQuery("UPDATE wt_quests SET summary = ? WHERE quest_id = ?", "si", array($summary, $quest_id));
        return false;
    }
    
    public function editMessagePreQuest($quest_id, $msg_pre_quest)
    {
        // Check if the message is valid
        $this->executeQuery("UPDATE wt_quests SET msg_pre_quest = ? WHERE quest_id = ?", "si", array($msg_pre_quest, $quest_id));
        return false;
    }
    
    public function editMessageDuringQuest($quest_id, $msg_during_quest)
    {
        // Check if the message is valid
        $this->executeQuery("UPDATE wt_quests SET msg_during_quest = ? WHERE quest_id = ?", "si", array($msg_during_quest, $quest_id));
        return false;
    }
    
    public function editMessagePostQuest($quest_id, $msg_post_quest)
    {
        // Check if the message is valid
        $this->executeQuery("UPDATE wt_quests SET msg_post_quest = ? WHERE quest_id = ?", "si", array($msg_post_quest, $quest_id));
        return false;
    }
    
    public function editParameters($quest_id, $parameters)
    {
        // Check if the message is valid
        $this->executeQuery("UPDATE wt_quests SET parameters = ? WHERE quest_id = ?", "si", array($parameters, $quest_id));
        return false;
    }
    
    public function editRewards($quest_id, $rewards)
    {
        // Check if the message is valid
        $this->executeQuery("UPDATE wt_quests SET rewards = ? WHERE quest_id = ?", "si", array($rewards, $quest_id));
        return false;
    }
    
    protected function checkValidity()
    {
    
    }
}
