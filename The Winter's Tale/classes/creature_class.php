<?php

class Creature extends Database
{
	protected $spawned_creature_id;
	protected $terrain_id;
	protected $creature_id;
	protected $name;
	protected $description;
	protected $creature_level;
	protected $stamina;
	protected $stamina_max;
	protected $damage;
	protected $defense;
	protected $critical;
	protected $experience;
	protected $money;
	
	
	public function __construct($creature_id)
    {
        $this->loadCreature($creature_id);
    }
    
    
    public function setSpawnedCreatureId($spawned_creature_id)
    {
        $this->spawned_creature_id = $spawned_creature_id;
    }
    
    public function getSpawnedCreatureId()
    {
        return $this->spawned_creature_id;
    }
    
    public function setTerrainId($terrain_id)
    {
        $this->terrain_id = $terrain_id;
    }
    
    public function getTerrainId()
    {
        return $this->terrain_id;
    }
    
    public function setCreatureId($creature_id)
    {
        $this->creature_id = $creature_id;
    }
    
    public function getCreatureId()
    {
        return $this->creature_id;
    }
    
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
    
    public function setCreatureLevel($creature_level)
    {
        $this->creature_level = $creature_level;
    }
    
    public function getCreatureLevel()
    {
        return $this->creature_level;
    }
    
    public function setStamina($stamina)
    {
        $this->stamina = $stamina;
    }
    
    public function getStamina()
    {
        return $this->stamina;
    }
    
    public function setStaminaMax($stamina_max)
    {
        $this->stamina_max = $stamina_max;
    }
    
    public function getStaminaMax()
    {
        return $this->stamina_max;
    }
    
    public function setDamage($damage)
    {
        $this->damage = $damage;
    }
    
    public function getDamage()
    {
        return $this->damage;
    }
    
    public function setDefense($defense)
    {
        $this->defense = $defense;
    }
    
    public function getDefense()
    {
        return $this->defense;
    }
    
    public function setCritical($critical)
    {
        $this->critical = $critical;
    }
    
    public function getCritical()
    {
        return $this->critical;
    }
    
    public function setExperience($experience)
    {
        $this->experience = $experience;
    }
    
    public function getExperience()
    {
        return $this->experience;
    }
    
    public function setMoney($money)
    {
        $this->money = $money;
    }
    
    public function getMoney()
    {
        return $this->money;
    }
    
    
	// ---------------------------------------- Methods ----------------------------------------
	
	public function loadCreature($spawned_creature_id)//
	{
		$this->logRegular("Creature: ".$creature_id);
        $result = $this->executeQuery("SELECT * FROM wt_spawned_creatures WHERE spawned_creature_id = ?", "i", $spawned_creature_id);
        
        $row = $result->fetch_array();
        $this->setSpawnedCreatureId($row["spawned_creature_id"]);
        $this->setTerrainId($row["terrain_id"]);
        $this->setCreatureId($row["creature_id"]);
        $this->setCreatureLevel($row["creature_level"]);
        $this->setName($row["name"]);
        $this->setDescription($row["description"]);
        $this->setStamina($row["stamina"]);
        $this->setStaminaMax($row["stamina_max"]);
        $this->setDamage($row["damage"]);
        $this->setDefense($row["defense"]);
        $this->setCritical($row["critical"]);
        $this->setExperience($row["experience"]);
        $this->setMoney($row["money"]);
        //$this->setStamina($row["stamina"]);
	}
	
	public function removeYourself()
	{
		$this->executeQuery("DELETE FROM wt_spawned_creatures WHERE spawned_creature_id = ?", "i", $this->getSpawnedCreatureId());
	}
	
	// ---------------------------------------- Stamina Section ----------------------------------------
	public function updateStamina($stamina)
	{
		$this->setStamina($stamina);//check for negative money
		
		$this->executeQuery("UPDATE wt_spawned_creatures SET stamina = ? WHERE spawned_creature_id = ?", "ii", array($this->getStamina(), $this->getSpawnedCreatureId()));
	}
}
