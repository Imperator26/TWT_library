<?php

class User extends Database
{
// User
    protected $user_id;
    protected $username;

// Chat
    protected $language_code;

// Status
    protected $status;
    protected $status_time;
    protected $ethnicity;
    protected $terrain;
    protected $quest_que_no;
    protected $active_quest;
    protected $money;
    protected $icy_diamonds;
    protected $experience;
    protected $stamina;
    protected $stamina_max;
    protected $mana;
    protected $mana_max;
    protected $damage;
    protected $defense;
    protected $critical;
    protected $weight;
    protected $weight_max;
    protected $tiredness;
    protected $sobriety;
    protected $hotel_terrain;
    protected $hotel_booked_until;
    protected $meditation_points;
    
    
    // Getters and Setters
    
    public function setUsername($username)
    {
        $this->username = $username;
    }
    
    public function getUsername()
    {
        return $this->username;
    }
    
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }
    
    public function getUserId()
    {
        return $this->user_id;
    }
    
    public function setLanguageCode($language_code)
    {
        $language_code = explode($language_code, "-");
        switch ($language_code[0]) {
            case "it":
                $language_code = "ITA";
                break;
            case "es":
                $language_code = "ESP";
                break;
            default:
                $language_code = "ENG";
        }
        $this->language_code = $language_code;
    }
    
    public function getLanguageCode()
    {
        return $this->language_code;
    }
    
    public function setStatus($status)
    {
        $this->status = $status;
    }
    
    public function getStatus()
    {
        return $this->status;
    }
    
    public function setStatusTime($status_time)
    {
        $this->status_time = $status_time;
    }
    
    public function getStatusTime()
    {
        return $this->status_time;
    }
    
    public function setEthnicity($ethnicity)
    {
        $this->ethnicity = $ethnicity;
    }
    
    public function getEthnicity()
    {
        return $this->ethnicity;
    }
    
    public function setTerrain($terrain)
    {
        $this->terrain = $terrain;
    }
    
    public function getTerrain()
    {
        return $this->terrain;
    }
    
    public function setQuestQueNumber($quest_que_no)
    {
        $this->quest_que_no = $quest_que_no;
    }
    
    public function getQuestQueNumber()
    {
        return $this->quest_que_no;
    }
    
    public function setActiveQuest($active_quest)
    {
        $this->active_quest = $active_quest;
    }
    
    public function getActiveQuest()
    {
        return $this->active_quest;
    }
    
    public function setMoney($money)
    {
        $this->money = $money;
    }
    
    public function getMoney()
    {
        return $this->money;
    }
    
    public function setIcyDiamonds($icy_diamonds)
    {
        $this->icy_diamonds = $icy_diamonds;
    }
    
    public function getIcyDiamonds()
    {
        return $this->icy_diamonds;
    }
    
    public function setLevel($level)
    {
        $this->level = $level;
    }
    
    public function getLevel()
    {
        return $this->level;
    }
    
    public function setExperience($experience)
    {
        $this->experience = $experience;
    }
    
    public function getExperience()
    {
        return $this->experience;
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
    
    public function setMana($mana)
    {
        $this->mana = $mana;
    }
    
    public function getMana()
    {
        return $this->mana;
    }
    
    public function setManaMax($mana_max)
    {
        $this->mana_max = $mana_max;
    }
    
    public function getManaMax()
    {
        return $this->mana_max;
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
    
    public function setWeight($weight)
    {
        $this->weight = $weight;
    }
    
    public function getWeight()
    {
        return $this->weight;
    }
    
    public function setWeightMax($weight_max)
    {
        $this->weight_max = $weight_max;
    }
    
    public function getWeightMax()
    {
        return $this->weight_max;
    }
    
    public function setTiredness($tiredness)
    {
        $this->tiredness = $tiredness;// int times 10
    }
    
    public function getTiredness()
    {
        return $this->tiredness;
    }
    
    public function setSobriety($sobriety)
    {
        $this->sobriety = $sobriety;
    }
    
    public function getSobriety()
    {
        return $this->sobriety;
    }
    
    public function setHotelTerrain($hotel_terrain)
    {
        $this->hotel_terrain = $hotel_terrain;
    }
    
    public function getHotelTerrain()
    {
        return $this->hotel_terrain;
    }
    
    public function setHotelBookedUntil($hotel_booked_until)
    {
        $this->hotel_booked_until = $hotel_booked_until;
    }
    
    public function getHotelBookedUntil()
    {
        return $this->hotel_booked_until;
    }
    
    public function setMeditationPoints($meditation_points)
    {
        $this->meditation_points = $meditation_points;
    }
    
    public function getMeditationPoints()
    {
        return $this->meditation_points;
    }
    
    
    // Methods
    
    public function getUser()
    {
        $result = $this->executeQuery("SELECT * FROM wt_users WHERE user_id = ?", "i", $this->getUserId());
        if (!$result->num_rows) return false;
        
        // Save user's data
        $row = $result->fetch_array();
        $this->setUserId($row["user_id"]);
        $this->setUsername($row["username"]);
        $this->setLanguageCode($row["language_code"]);
        $this->setStatus($row["status"]);
        $this->setStatusTime($row["status_time"]);
        $this->setEthnicity($row["ethnicity"]);
        $this->setTerrain($row["terrain"]);
        $this->setQuestQueNumber($row["quest_que_no"]);
        $this->setActiveQuest($row["active_quest"]);
        $this->setMoney($row["money"]);
        $this->setIcyDiamonds($row["icy_diamonds"]);
        $this->setLevel($row["level"]);
        $this->setExperience($row["experience"]);
        $this->setStamina($row["stamina"]);
        $this->setStaminaMax($row["stamina_max"]);
        $this->setMana($row["mana"]);
        $this->setManaMax($row["mana_max"]);
        $this->setDamage($row["damage"]);
        $this->setDefense($row["defense"]);
        $this->setCritical($row["critical"]);
        $this->setWeight($row["weight"]);
        $this->setWeightMax($row["weight_max"]);
        $this->setTiredness($row["tiredness"]);
        $this->setSobriety($row["sobriety"]);
        $this->setHotelTerrain($row["hotel_terrain"]);
        $this->setHotelBookedUntil($row["hotel_booked_until"]);
        $this->setMeditationPoints($row["meditation_points"]);
        
        return true;
    }

    /* addUser()
    This function is called when the user is not saved in the database, thus is a new user.
    It records the new user's info and start the tutorial by changing the status and by sending the first step of the tutorial.
    */
    public function addUser()
    {
        $this->setStamina(0);
        $this->setStaminaMax(0);
        $this->setMana(0);
        $this->setManaMax(0);
        $this->setDamage(0);
        $this->setDefense(0);
        $this->setCritical(0);
        $this->setWeight(0);
        $this->setWeightMax(0);
        
        $this->executeQuery(
            "INSERT INTO wt_users VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
            "isssiiiiiiiiiiiiiiiiiiiiiiii",
            array(
                $this->getUserId(),
                $this->getUsername(),
                $this->getLanguageCode(),
                "none", // status
                0,  	// status_time
                null, 	// ethnicity
                1,  	// terrain
                1,		// quest_que_no
                0,		// active_quest
                0,		// money
                0,		// icy diamonds
                1,		// level
                0,		// experience
                $this->getStamina(),
                $this->getStaminaMax(),
                $this->getMana(),
                $this->getManaMax(),
                $this->getDamage(),
                $this->getDefense(),
                $this->getCritical(),
                $this->getWeight(),
                $this->getWeightMax(),
                0,  	// tiredness
                100,	// sobriety
                0,  	// hotel_terrain
                0,  	// hotel_booked_until
                0   	// meditation_points
            )
        );
    }
    
    public function updateTerrain($terrain)
    {
    	$this->setTerrain($terrain);
    	
        $this->executeQuery("UPDATE wt_users SET terrain = ? WHERE user_id = ?", "ii", array($terrain, $this->getUserId()));
    }
    
    /* updateUsername()
    This function is used after receiving the user's info from getUser() in analyzer.php.
    The username received from Telegram is compared with the stored one.
    If they're different updateUsername() updates the databese.*/
    public function updateUsername()
    {
        $this->logMethod("Updating username. User: ".$this->getUserId()." Status: ".$this->getStatus());
        
        $result = $this->executeQuery("UPDATE wt_users SET username = ? WHERE user_id = ?", "si", array($this->getUsername(), $this->getUserId()));
    }
	
	public function updateStatus($status)
	{
		$this->setStatus($status);
		$this->executeQuery(
			"UPDATE wt_users SET status = ? WHERE user_id = ?",
			"si",
			array($status, $this->getUserId()));
	}
	
	// ---------------------------------------- Stats Section ----------------------------------------
	public function updateStatistics($stamina, $stamina_max, $mana, $mana_max, $damage, $defense, $critical, $weight, $weight_max, $tiredness, $sobriety)
	{
		$this->executeQuery(
			"UPDATE wt_users SET stamina = ?, stamina_max = ?, mana = ?, mana_max = ?, damage = ?, defense = ?, critical = ?, weight = ?, weight_max = ?, tiredness = ?, sobriety = ? WHERE user_id = ?",
			"iiiiiiiiiiii",
			array(
				$stamina,//negative stamina check
				$stamina_max,
				$mana,
				$mana_max,
				$damage,
				$defense,
				$critical,
				$weight,
				$weight_max,
				$tiredness,
				$sobriety,
				$this->getUserId()
			)
		);
	}
	
	
	// ---------------------------------------- Quest Section ----------------------------------------
	public function updateQuestQueNumber($quest_que_no)
	{
		$this->setQuestQueNumber($quest_que_no);
		
		$this->executeQuery("UPDATE wt_users SET quest_que_no = ? WHERE user_id = ?", "ii", array($quest_que_no, $this->getUserId()));
	}
	
	public function updateActiveQuest($active_quest_id)
	{
		$this->setActiveQuest($active_quest_id);
		
		$this->executeQuery("UPDATE wt_users SET active_quest = ? WHERE user_id = ?", "ii", array($active_quest_id, $this->getUserId()));
	}
	
	
	// ---------------------------------------- Stamina Section ----------------------------------------
	public function updateStamina($stamina)
	{
		$this->setStamina($stamina);
		
		$this->executeQuery("UPDATE wt_users SET stamina = ? WHERE user_id = ?", "ii", array($this->getStamina(), $this->getUserId()));
	}
	
	
	// ---------------------------------------- Money Section ----------------------------------------
	public function moveMoney($money)
	{
		$this->setMoney($this->getMoney()+$money);//check for negative money
		
		$this->executeQuery("UPDATE wt_users SET money = ? WHERE user_id = ?", "ii", array($this->getMoney(), $this->getUserId()));
	}
	
	
	// ---------------------------------------- Experience Section ----------------------------------------
	public function experienceNeededToLevelUp($level)
	{
		return pow($level, 2) * 10;
	}
	
	public function addExperience($experience)	// Recursive
	{
		if ($this->getExperience() + $experience >= $this->experienceNeededToLevelUp($this->getLevel())) {
			$this->addExperience(($this->getExperience() + $experience) - $this->experienceNeededToLevelUp($this->getLevel()));
			$this->levelUp();
			return true;
		}
		else {
			$this->executeQuery("UPDATE wt_users SET experience = ? WHERE user_id = ?", "si", array($experience, $this->getUserId()));
			return false;
		}
	}
	
	public function levelUp()
	{
		$this->setLevel($this->getLevel()+1);
		$this->executeQuery("UPDATE wt_users SET level = ? WHERE user_id = ?", "ii", array($this->getLevel(), $this->getUserId()));
		$result = $this->executeQuery("SELECT * FROM wt_ethnicities WHERE ethnicity_id = ?", "i", $this->getEthnicity());
		$row = $result->fetch_array();
		
		$this->executeQuery(
			"UPDATE wt_users SET stamina = ?, stamina_max = ?, mana = ?, mana_max = ?, damage = ?, defense = ?, critical = ? WHERE user_id = ?",
			"iiiiiiii",
			array(
				$row["stamina"] * $this->getLevel(),
				$row["stamina"] * $this->getLevel(),
				$row["mana"] * $this->getLevel(),
				$row["mana"] * $this->getLevel(),
				$row["damage"] * $this->getLevel(),
				$row["defense"] * $this->getLevel(),
				$row["critical"] * pow($this->getLevel()-1, 0.6),
				$this->getUserId()
			)
		);
	
	}
	
	// ---------------------------------------- Items Section ----------------------------------------
	public function addItemToBackpack($item_id, $level, $quantity)
	{
		$result = $this->executeQuery("SELECT * FROM wt_backpacks WHERE user_id = ? AND item_id = ? AND level = ?", "iii", array($this->getUserId(), $item_id, $level));
        if ($result->num_rows) {
        	$row = $result->fetch_array();
        	$total_of_items_owned = $row["quantity"] + $quantity;
        	
        	$this->executeQuery("UPDATE wt_backpacks SET quantity = ? WHERE user_id = ? AND item_id = ? AND level = ?", "iiii", array($total_of_items_owned, $this->getUserId(), $item_id, $level));
        } else {
        	$result = $this->executeQuery("SELECT * FROM wt_items WHERE item_id = ?", "i", $item_id);
        	$row = $result->fetch_array();
        	
        	$this->executeQuery(
        		"INSERT INTO wt_backpacks VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
        		"iiisiisiiiiiiiiii",
        		array(
        			0,
        			$this->getUserId(),
        			$item_id,
        			$row["type"],
        			$row["rarity"],
        			$level,
        			$this->itemIdToName($item_id),
        			$quantity,
        			(int) $row["stamina"] * (1 + ($level-1)/10),
        			(int) $row["mana"] * (1 + ($level-1)/10),
        			(int) $row["damage"] * (1 + ($level-1)/10),
        			(int) $row["defense"] * (1 + ($level-1)/10),
        			(int) $row["critical"] * (1 + ($level-1)/10),
        			(int) $row["tiredness"] * (1 + ($level-1)/10),
        			(int) $row["sobriety"] * (1 + ($level-1)/10),
        			(int) $row["weight"],
        			(int) $row["cost"] * (1 + ($level-1)/10),
        		)
        	);
        }
	}
	
	public function removeItemFromBackpack($item_id, $level, $quantity)
	{
		$result = $this->executeQuery("SELECT * FROM wt_backpacks WHERE user_id = ? AND item_id = ? AND level = ?", "iii", array($this->getUserId(), $item_id, $level));
        $row = $result->fetch_array();
    	$total_of_items_owned = $row["quantity"] - $quantity;
    	
        if ($total_of_items_owned) $this->executeQuery("UPDATE wt_backpacks SET quantity = ? WHERE user_id = ? AND item_id = ? AND level = ?", "iiii", array($total_of_items_owned, $this->getUserId(), $item_id, $level));
        else $this->executeQuery("DELETE FROM wt_backpacks WHERE user_id = ? AND item_id = ? AND level = ?", "iii", array($this->getUserId(), $item_id, $level));
    }
	
	public function itemIdToName($item_id)
	{
		$result = $this->executeQuery("SELECT * FROM wt_items WHERE item_id = ?", "i", $item_id);
        $row = $result->fetch_array();
        return $row["name"];
	}
	
	
	// ---------------------------------------- Challanges Section ----------------------------------------
	public function challangeAdventurer($user_id, $bet)
	{
		$result = $this->executeQuery("SELECT * FROM wt_users WHERE user_id = ?", "i", $user_id);
        $user = $result->fetch_array();
        
		$this->executeQuery(
            "INSERT INTO wt_challanges VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
            "iiiiiissiiiiiiiiii",
            array(
            	0,//challange_id
            	0,//accepted
            	0,//turn
            	$bet,
                $this->getUserId(),
                $user["user_id"],
                $this->getUsername(),
                $user["username"],
                $this->getStaminaMax(),
                $user["stamina_max"],
                $this->getManaMax(),
                $user["mana_max"],
                $this->getDamage(),
                $user["damage"],
                $this->getDefense(),
                $user["defense"],
                $this->getCritical()*100,
                $user["critical"]
            )
        );
        
        return $this->getLastInsertedId();
	}
	
	public function approveChallange($challange_id)
	{
		$this->executeQuery("UPDATE wt_challanges SET approved = ? WHERE challange_id = ?", "ii", array(true, $challange_id));
	}
	
	
	public function removeChallange($challange_id)
	{
		$this->executeQuery("DELETE FROM wt_challanges WHERE challange_id = ?", "i", $challange_id);
	}
}
