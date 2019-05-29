<?php

class NPC extends Database
{
    protected $npc_id;
    protected $terrain;
    protected $type;
    protected $name;
    protected $message;
    
    
    public function __construct($npc_id)
    {
        $this->loadNPC($npc_id);
    }
    
    // Getters and Setters
    
    public function setNPCId($npc_id)
    {
        $this->npc_id = $npc_id;
    }
    
    public function getNPCId()
    {
        return $this->npc_id;
    }
    
    public function setTerrain($terrain)
    {
        $this->terrain = $terrain;
    }
    
    public function getTerrain()
    {
        return $this->terrain;
    }
    
    public function setName($name)
    {
        $this->name = $name;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function setMessage($message)
    {
        $this->message = $message;
    }
    
    public function getMessage()
    {
        return $this->message;
    }
    
    public function setType($type)
    {
        $this->type = $type;
    }
    
    public function getType()
    {
        return $this->type;
    }
    
    
    // Methods
    
    public function loadNPC($npc_id)
    {
        $this->logRegular("NPC: ".$npc_id);
        $result = $this->executeQuery("SELECT * FROM wt_npcs WHERE npc_id = ?", "i", $npc_id);
        
        $row = $result->fetch_array();
        $this->setNPCId($row["npc_id"]);
        $this->setType($row["type"]);
        $this->setName($row["name"]);
        $this->setMessage($row["message"]);
        $this->setTerrain($row["terrain"]);
        
    }
}
