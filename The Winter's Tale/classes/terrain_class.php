<?php

class Terrain extends Database
{
    protected $name;
    protected $description;
    protected $type;
    protected $terrain;
    protected $north = null;
    protected $east = null;
    protected $south = null;
    protected $west = null;
    protected $wlc_msg_north = null;
    protected $wlc_msg_east = null;
    protected $wlc_msg_south = null;
    protected $wlc_msg_west = null;
    protected $approved = false;
    protected $creature_id;
    protected $probability;
    protected $creature_level;
    
    
    public function __construct($terrain)
    {
        $this->loadTerrain($terrain);
    }
    
    
    // ---------------------------------------- Getters and Setters Section ----------------------------------------
    
    public function setName($name)
    {
        $this->name = $name;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function setDescription($description)
    {
        $this->description = $description;
    }
    
    public function getDescription()
    {
        return $this->description;
    }
    
    public function setType($type)
    {
        $this->type = $type;
    }
    
    public function getType()
    {
        return $this->type;
    }
    
    public function setTerrain(array $terrains)
    {
        if (array_key_exists("terrain", $terrains)) $this->terrain = $terrains["terrain"];
        if (array_key_exists("north", $terrains)) $this->north = $terrains["north"];
        if (array_key_exists("east", $terrains)) $this->east = $terrains["east"];
        if (array_key_exists("south", $terrains)) $this->south = $terrains["south"];
        if (array_key_exists("west", $terrains)) $this->west = $terrains["west"];
    }
    
    public function getTerrain($where)
    {
        switch ($where) {
            case "all":
                return array(
                   "north" => $this->north,
                   "east" => $this->east,
                   "south" => $this->south,
                   "west" => $this->west
                );
            case "north":
                return $this->north;
            case "east":
                return $this->east;
            case "south":
                return $this->south;
            case "west":
                return $this->west;
            default:
                return $this->terrain;
        }
    }
    
    public function setWelcomeMessage(array $wlc_messages)
    {
        if (array_key_exists("wlc_msg_north", $wlc_messages)) $this->wlc_msg_north = $wlc_messages["wlc_msg_north"];
        if (array_key_exists("wlc_msg_east", $wlc_messages)) $this->wlc_msg_east = $wlc_messages["wlc_msg_east"];
        if (array_key_exists("wlc_msg_south", $wlc_messages)) $this->wlc_msg_south = $wlc_messages["wlc_msg_south"];
        if (array_key_exists("wlc_msg_west", $wlc_messages)) $this->wlc_msg_west = $wlc_messages["wlc_msg_west"];
    }
    
    public function getWelcomeMessage($where)
    {
        switch ($where) {
            case "all":
                return array(
                   "wlc_msg_north" => $this->wlc_msg_north,
                   "wlc_msg_east" => $this->wlc_msg_east,
                   "wlc_msg_south" => $this->wlc_msg_south,
                   "wlc_msg_west" => $this->wlc_msg_west
                );
            case "wlc_msg_north":
                return $this->wlc_msg_north;
            case "wlc_msg_east":
                return $this->wlc_msg_east;
            case "wlc_msg_south":
                return $this->wlc_msg_south;
            case "wlc_msg_west":
                return $this->wlc_msg_west;
        }
    }
    
    public function setApproval($approved)
    {
        $this->approved = $approved;
    }
    
    public function getApproval()
    {
        return $this->approved;
    }
    
    public function setCreatureId($creatue_id)
    {
        $this->creatue_id = $creatue_id;
    }
    
    public function getCreatureId()
    {
        return $this->creatue_id;
    }
    
    public function setProbability($probability)
    {
        $this->probability = $probability;
    }
    
    public function getProbability()
    {
        return $this->probability;
    }
    
    public function setCreatureLevel($creature_level)
    {
        $this->creature_level = $creature_level;
    }
    
    public function getCreatureLevel()
    {
        return $this->creature_level;
    }
    
    
    // ---------------------------------------- Methods Section ----------------------------------------
    public function loadTerrain($terrain)
    {
        $this->logRegular("Terrain: ".$terrain);
        $result = $this->executeQuery("SELECT * FROM wt_terrains WHERE terrain = ?", "i", $terrain);
        
        $row = $result->fetch_array();
        $this->setName($row["name"]);
        $this->setDescription($row["description"]);
        $this->setType($row["type"]);
        $this->setTerrain($row);
        $this->setWelcomeMessage($row);
        $this->setApproval($row["approved"]);
        $this->setCreatureId($row["creature_id"]);
        $this->setProbability($row["probability"]);
        $this->setCreatureLevel($row["creature_level"]);
    }
    
    
    // ---------------------------------------- NPCs Section ----------------------------------------
    public function getNPCsId() {
        $result = $this->executeQuery("SELECT * FROM wt_npcs WHERE terrain = ?", "i", $this->getTerrain(null));
        
        $NPCs_id = [];
        while ($row = $result->fetch_array()) array_push($NPCs_id, $row["npc_id"]);
        
        return $NPCs_id;
    }
    
    
    // ---------------------------------------- Creature Section ----------------------------------------
    public function spawnCreature($creature_level)
	{
		if (!$creature_level) $creature_level = 1;
		
		$creature = $this->executeQuery("SELECT * FROM wt_creatures WHERE creature_id = ?", "i", $this->getCreatureId());
		$creature = $creature->fetch_array();
		$this->executeQuery(
            "INSERT INTO wt_spawned_creatures VALUES(?,?,?,?,?,?,?,?,?,?,?,?)",
            "iiisiiiiiiii",
            array(
                0,
                $this->getTerrain(null),
                $this->getCreatureId(),
                $creature["name"],
                $creature_level,
                $creature["stamina"]*$creature_level,
                $creature["stamina"]*$creature_level,
                $creature["damage"]*$creature_level,
                $creature["defense"]*$creature_level,
                $creature["critical"]*$creature_level,
                $creature["experience"]*$creature_level,
                $creature["money"]*$creature_level
            )
        );
	}
}
