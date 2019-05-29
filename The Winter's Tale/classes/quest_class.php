<?php

class Quest extends Database
{
    protected $quest_id;
    protected $editor_id;
    protected $issuer;
    protected $que_number;
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
    
    public function questCompleted($user_id)
	{
		$this->executeQuery("INSERT INTO wt_completed_quests VALUES(?,?,?,?,?,?)", "iiiiii", array(0, $this->getQuestId(), $this->getIssuer(), $this->getQueNumber(), $user_id, time()));
	
		$this->setQuestId(0);
		
		$this->executeQuery("UPDATE wt_users SET quest = ? WHERE user_id = ?", "ii", array(0, $user_id));
	}
}
