<?php

require_once("classes/database_class.php");

$Database = new Database();

$items = array_map('str_getcsv', file('items.csv'));

for ($i = 0; $i < count($items); $i++) {
	printf($items[0]);
}

