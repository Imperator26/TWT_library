<?php

class Terrain extends Database
{
    protected $name;
    protected $description;
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
    
    
    // Getters and Setters
    
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
    
    
    // Methods
    
    public function loadTerrain($terrain)
    {
        $this->logRegular("Terrain: ".$terrain);
        $result = $this->executeQuery("SELECT * FROM wt_terrains WHERE terrain = ?", "i", $terrain);
        
        $row = $result->fetch_array();
        $this->setName($row["name"]);
        $this->setDescription($row["description"]);
        $this->setTerrain($row);
        $this->setWelcomeMessage($row);
        $this->setApproval($row["approved"]);
        $this->setCreatureId($row["creature_id"]);
        $this->setProbability($row["probability"]);
        $this->setCreatureLevel($row["creature_level"]);
    }
    
    public function createTerrain($terrain, $towards, $name, $description, $editor_id)
    {
        if (!isset($towards) or !is_string($towards) or ($towards != "north" and $towards != "east" and $towards != "south" and $towards != "west")) return "The <i>direction</i> parameter is empty or is not a string or is not an expected value.";
        if (!isset($name) or !is_string($name)) return "The <i>name</i> parameter is empty or is not a string.";
        if (!isset($description) or !is_string($description)) return "The <i>description</i> parameter is empty or is not a string.";
        
        $type = "regular";
        
        // Check if a terrain already exists in the submitted direction
        if ($this->getTerrain($towards) != null) return "A terrain already exists in that direction.";
        
        // Check if the name of the terrain has already been taken
        $result = $this->executeQuery("SELECT * FROM wt_terrains WHERE name = ?", "s", $name);
        if ($result->num_rows) return "Terrain's name already in use.";
        
        // Get the opposite direction (from)
        if ($towards == "north") {
            $south = $terrain;
        } else $south = null;
        if ($towards == "east") {
            $west = $terrain;
        } else $west = null;
        if ($towards == "south") {
            $north = $terrain;
        } else $north = null;
        if ($towards == "west") {
            $east = $terrain;
        } else $east = null;
        
        $wlc_msg_north = null;
        $wlc_msg_east = null;
        $wlc_msg_south = null;
        $wlc_msg_west = null;
        
        // Create new terrain
        $this->executeQuery(
            "INSERT INTO wt_terrains VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
            "isssiiiiiiiiiiiii",
            array(
                0,
                $name,
                $description,
                $type,
                $editor_id,
                $north,
                $east,
                $south,
                $west,
                $wlc_msg_north,
                $wlc_msg_east,
                $wlc_msg_south,
                $wlc_msg_west,
                false,// Approved
                0,//creature_id
                0,//probability
                0 //level=0
            )
        );
        
        // Get its id
        $this->setTerrain(array($towards => $this->getLastInsertedId()));
        
        // Update 
        $this->executeQuery("UPDATE wt_terrains SET ".$towards." = ? WHERE terrain = ?", "ii", array($this->getTerrain($towards), $terrain)); // Not proper prepared statements. Oh well
        
        return false;
    }
    
    public function editName($name)
    {
        // Check if the name is valid
        $this->executeQuery("UPDATE wt_terrains SET name = ? WHERE terrain = ?", "si", array($name, $this->getTerrain(null)));
    }
    
    public function editDescription($description)
    {
        // Check if the description is valid
        $this->executeQuery("UPDATE wt_terrains SET description = ? WHERE terrain = ?", "si", array($description, $this->getTerrain(null)));
    }
    
    public function editWelcomeMessage($from, $message)
    {
        if (!isset($from) or !is_string($from) or ($from != "north" and $from != "east" and $from != "south" and $from != "west")) return "The <i>direction</i> parameter is empty or is not a string or is not an expected value.";
        if (!isset($message) or !is_string($message)) return "The <i>message</i> parameter is empty or is not a string.";
        
        $this->executeQuery("UPDATE wt_terrains SET wlc_msg_".$from." = ? WHERE terrain = ?", "si", array($message, $this->getTerrain(null)));
        
        return false;
    }
    
    public function editCreatureSetup($creature_id,  $probability, $creature_level)
    {
    	
    }
    
    
    public function approveTerrain()
    {
    	$this->executeQuery("UPDATE wt_terrains SET approved = ? WHERE terrain = ?", "ii", array(1, $this->getTerrain(null)));
    }
    
    public function getNPCsId() {
        $result = $this->executeQuery("SELECT * FROM wt_npcs WHERE terrain = ?", "i", $this->getTerrain(null));
        
        $NPCs_id = [];
        while ($row = $result->fetch_array()) array_push($NPCs_id, $row["npc_id"]);
        
        return $NPCs_id;
    }
    
    public function addNPC($terrain, $name, $message, $editor_id)
    {
        if (!isset($name) or !is_string($name)) return "The <i>name</i> parameter is empty or is not a string.";
        if (!isset($message) or !is_string($message)) return "The <i>message</i> parameter is empty or is not a string.";
        
        // Check if the name of the terrain has already been taken
        $result = $this->executeQuery("SELECT * FROM wt_npcs WHERE name = ?", "s", $name);
        if ($result->num_rows) return "NPC's name already in use.";
        
        // Check if a NPC is already present
        $result = $this->executeQuery("SELECT * FROM wt_npcs WHERE terrain = ?", "s", $terrain);
        if ($result->num_rows) return "NPC already present.";
        
        $type = "regular";
        
        $this->executeQuery(
            "INSERT INTO wt_npcs VALUES(?,?,?,?,?,?)",
            "iisssi",
            array(
                0,
                $terrain,
                $type,
                $name,
                $message,
                $editor_id
            )
        );
        return false;
    }
    
    public function addItem($type, $rarity, $ethnicity, $name, $description, $editor_id)
    {
        if (!isset($type) or !is_string($type)) return "The <i>type</i> parameter is empty or is not a string.";// check for types
        //if (!isset($rarity) or !is_string($rarity)) return "The <i>type</i> parameter is empty or is not a string.";// check for types
        if (!isset($rarity) or !is_numeric($rarity)) return "The <i>rarity</i> parameter is empty or is not a number.";
        if (!isset($ethnicity) or !is_string($ethnicity)) return "The <i>ethnicity</i> parameter is empty or is not a string.";// check for ethnicities
        if (!isset($name) or !is_string($name)) return "The <i>name</i> parameter is empty or is not a string.";
        if (!isset($description) or !is_string($description)) return "The <i>description</i> parameter is empty or is not a string.";
        
        // Check if the name of the terrain has already been taken
        $result = $this->executeQuery("SELECT * FROM wt_items WHERE name = ?", "s", $name);
        if ($result->num_rows) return "Name already in use.";
        
        $this->executeQuery(
            "INSERT INTO wt_items VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
            "iisisssiiiiiiiii",
            array(
                0,
                $editor_id,
                $type,
                $rarity,
                $ethnicity,
                $name,
                $description,
                0,//stamina
                0,//mana
                0,//damage
                0,//defense
                0,//critical
                0,//tiredness
                0,//sobriety
                0,//weight
                0 //cost
            )
        );
        return $this->getLastInsertedId();
    }
    
    public function editItemName($item_id, $name)
    {
    	if (!isset($name) or !is_string($name)) return "The <i>name</i> parameter is empty or is not a string.";
        
        $this->executeQuery("UPDATE wt_items SET name = ? WHERE item_id = ?", "si", array($name, $item_id));
        
        return false;
    }
    
    public function editItemDescription($item_id, $description)
    {
    	if (!isset($description) or !is_string($description)) return "The <i>description</i> parameter is empty or is not a string.";
        
        $this->executeQuery("UPDATE wt_items SET description = ? WHERE item_id = ?", "si", array($description, $item_id));
        
        return false;
    }
    
    public function editItemStatistics($item_id, $statistics)
    {
    	$parameters = explode("&", $statistics, 7);
    	$stamina = trim($parameters[0]);
    	$mana = trim($parameters[1]);
    	$damage = trim($parameters[2]);
    	$defense = trim($parameters[3]);
    	$critical = trim($parameters[4]);
    	$weight = trim($parameters[5]);
    	$cost = trim($parameters[6]);
    	
    	if (!isset($stamina) or !is_numeric($stamina)) return "The <i>stamina</i> parameter is empty or is not a number.";
    	if (!isset($mana) or !is_numeric($mana)) return "The <i>mana</i> parameter is empty or is not a number.";
    	if (!isset($damage) or !is_numeric($damage)) return "The <i>damage</i> parameter is empty or is not a number.";
    	if (!isset($defense) or !is_numeric($defense)) return "The <i>defense</i> parameter is empty or is not a number.";
    	if (!isset($critical) or !is_numeric($critical)) return "The <i>critical</i> parameter is empty or is not a number.";
		if (!isset($weight) or !is_numeric($weight)) return "The <i>weight</i> parameter is empty or is not a number.";
		if (!isset($cost) or !is_numeric($cost)) return "The <i>cost</i> parameter is empty or is not a number.";
        
        $this->executeQuery("UPDATE wt_items SET stamina = ?, mana = ?, damage = ?, defense = ?, critical = ?, weight = ?, cost = ? WHERE item_id = ?", "iiiiiiii", array($stamina, $mana, $damage, $defense, $critical, $weight, $cost, $item_id));
        
        return false;
    }
    
    public function addGiveaway($issuer, $item_id, $rarity, $level, $quantity, $message)
    {
    	if (!isset($issuer) or !is_numeric($issuer)) return "The <i>issuer</i> parameter is empty or is not a number.";
    	if (!isset($item_id) or !is_numeric($item_id)) return "The <i>item_id</i> parameter is empty or is not a number.";
    	if (!isset($rarity) or !is_numeric($rarity)) return "The <i>rarity</i> parameter is empty or is not a number.";
    	if (!isset($level) or !is_numeric($level)) return "The <i>level</i> parameter is empty or is not a number.";
    	if (!isset($quantity) or !is_numeric($quantity)) return "The <i>quantity</i> parameter is empty or is not a number.";
    	if (!isset($message) or !is_string($message)) return "The <i>message</i> parameter is empty or is not a number.";
    	
    	// Check if the issuer already has a giveaway
        $result = $this->executeQuery("SELECT * FROM wt_giveaways WHERE issuer = ?", "i", $issuer);
        if ($result->num_rows) return "Issuer already has a giveaway.";
        
        $this->executeQuery(
            "INSERT INTO wt_giveaways VALUES(?,?,?,?,?,?,?)",
            "iisiiii",
            array(
                0,
                $issuer,
                $message,
                $item_id,
                $rarity,
                $level,
                $quantity
            )
        );
        
        return false;
    }
}
