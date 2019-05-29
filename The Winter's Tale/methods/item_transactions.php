<?php


$csv = array_map('str_getcsv', file("../csv/items.csv"));

$items = [];

for ($i = 1; $i < count($csv); $i++) {
	print_r($csv[$i]);
	print("<br>");
	print("<br>");
	
	$items[$i-1] = array(
		"item_id" => $csv[$i][0],
		"editor_id" => 118557634,
		"type" => $csv[$i][3],
		"rarity" => $csv[$i][4],
		"ethnicity" => $csv[$i][5],
		"name" => $csv[$i][1],
		"description" => $csv[$i][2],
		"stamina" => $csv[$i][6],
		"mana" => $csv[$i][7],
		"damage" => $csv[$i][8],
		"defense" => $csv[$i][9],
		"critical" => $csv[$i][10],
		"tiredness" => $csv[$i][11],
		"sobriety" => $csv[$i][12],
		"weight" => $csv[$i][13],
		"cost" => $csv[$i][14]
	);
	
	print_r($items[$i-1]);
	print("<br>");
	print("<br>");
	print("<br>");
	
	
	
	$Database = new Database();
	
	while ($item = $items->fetch_array()) {
	$result = $Database->executeQuery("SELECT * FROM wt_items WHERE name = ?", "s", $item["name"]);
	if ($result->num_rows) {
		continue;
		
		//$Database->executeQuery("UPDATE wt_items SET item_id = ? WHERE name = ?", "si", array("none", $items["name"]));
	}
	
	$Database->executeQuery(
		"INSERT INTO wt_items VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
		"iisisssiiiiiiiii",
		array(
			$item["item_id"],
			$item["editor_id"],
			$item["type"],
			$item["rarity"],
			$item["ethnicity"],
			$item["name"],
			$item["description"],
			$item["stamina"],
			$item["mana"],
			$item["damage"],
			$item["defense"],
			$item["critical"],
			$item["tiredness"],
			$item["sobriety"],
			$item["weight"],
			$item["cost"]
		)
	);
}



