<?php

//Convert object into insert query
function WriteObject(&$conn, $tableName, &$object)
{
	$array = [ $object ];
	return WriteObjects($conn, $tableName, $array);
}

//Convert objects into insert query
function WriteObjects(&$conn, $tableName, &$objectArray)
{
	$keys = [];
	foreach(get_object_vars($objectArray[0]) as $k => $v)
		array_push($keys, $k);
		
	//no replace or ignore here, error related to inserting already existing sql might help detect faulty logic
	$sql = "INSERT INTO {$tableName} (";
	foreach($keys as $k)
		$sql .= "`{$k}`, ";
		
	$sql = substr_replace($sql, "", -2); //remove last space+comma
	$sql .= ") VALUES ";
	foreach($objectArray as $object) {
		$sql .= "(";
		foreach(get_object_vars($object) as $v) {
			if($v === "NULL") {
				$sql .= "NULL, ";
			} else {
				$v = $conn->quote($v);
				$sql .= "{$v}, ";
			}
		}
		$sql = substr_replace($sql, "", -2); //remove last space+comma
		$sql .= "), ";
	}
	$sql = substr_replace($sql, "", -2); //remove last space+comma
	$sql .= ";" . PHP_EOL;
	return $sql;
}

//check if the caller has already called this function with given id
function CheckAlreadyImported($id)
{
	static $imported = [ ];
	$callerName = debug_backtrace()[1]['function'];
	if(array_key_exists($callerName, $imported))
	{
		if(in_array($id, $imported[$callerName]))
			return true;
	} else {
		$imported[$callerName] = [ ];
	}
	
	array_push($imported[$callerName], $id);
	return false;
}

function HasAny(&$container, $keyname, $value)
{
	foreach(array_keys($container) as $key)
		if($container[$key]->$keyname == $value)
			return true;
		
	return false;
}

function GetHighest(&$container, $keyname)
{
	$highest = 0;
	
	foreach(array_keys($container) as $key)
		$highest = max($container[$key]->$keyname, $highest);
	
	return $highest;
}

function CheckIdentical(&$sunContainer, &$tcContainer, $keyname, $value)
{
	$sunResults = FindAll($sunContainer, $keyname, $value);
	$tcResults = FindAll($tcContainer, $keyname, $value);
	return $sunResults == $tcResults; //does this work okay? This is supposed to compare keys + values, but we don't care about keys.
}

function FindAll(&$container, $keyname, $value)
{
	$results = [];
	
	foreach(array_keys($container) as $key) {
		if($container[$key]->$keyname == $value)
			array_push($results, $container[$key]);
	}
			
	return $results;
}

function RemoveAny(&$container, $keyname, $value)
{
	foreach(array_keys($container) as $key) {
		if($container[$key]->$keyname == $value)
			unset($container[$key]);
	}
}

abstract class Conditions
{
	const CONDITION_ACTIVE_EVENT = 12;
}
	
function GetConditionName($conditionType)
{
	switch($conditionType)
	{
	case 0: return "CONDITION_NONE"; 
	case 1: return "CONDITION_AURA"; 
	case 2: return "CONDITION_ITEM"; 
	case 3: return "CONDITION_ITEM_EQUIPPED"; 
	case 4: return "CONDITION_ZONEID"; 
	case 5: return "CONDITION_REPUTATION_RANK"; 
	case 6: return "CONDITION_TEAM"; 
	case 7: return "CONDITION_SKILL"; 
	case 8: return "CONDITION_QUESTREWARDED"; 
	case 9: return "CONDITION_QUESTTAKEN"; 
	case 10: return "CONDITION_DRUNKENSTATE";
	case 11: return "CONDITION_WORLD_STATE";
	case 12: return "CONDITION_ACTIVE_EVENT";
	case 13: return "CONDITION_INSTANCE_INFO";
	case 14: return "CONDITION_QUEST_NONE";
	case 15: return "CONDITION_CLASS";
	case 16: return "CONDITION_RACE";
	case 17: return "CONDITION_ACHIEVEMENT";
	case 18: return "CONDITION_TITLE";
	case 19: return "CONDITION_SPAWNMASK";
	case 20: return "CONDITION_GENDER";
	case 21: return "CONDITION_UNIT_STATE";
	case 22: return "CONDITION_MAPID";
	case 23: return "CONDITION_AREAID";
	case 24: return "CONDITION_CREATURE_TYPE";
	case 25: return "CONDITION_SPELL";
	case 26: return "CONDITION_PHASEMASK";
	case 27: return "CONDITION_LEVEL";
	case 28: return "CONDITION_QUEST_COMPLETE";
	case 29: return "CONDITION_NEAR_CREATURE";
	case 30: return "CONDITION_NEAR_GAMEOBJECT";
	case 31: return "CONDITION_OBJECT_ENTRY_GUID";
	case 32: return "CONDITION_TYPE_MASK";
	case 33: return "CONDITION_RELATION_TO";
	case 34: return "CONDITION_REACTION_TO";
	case 35: return "CONDITION_DISTANCE_TO";
	case 36: return "CONDITION_ALIVE";
	case 37: return "CONDITION_HP_VAL";
	case 38: return "CONDITION_HP_PCT";
	case 39: return "CONDITION_REALM_ACHIEVEMENT";
	case 40: return "CONDITION_IN_WATER";
	case 41: return "CONDITION_TERRAIN_SWAP";
	case 42: return "CONDITION_STAND_STATE";
	case 43: return "CONDITION_DAILY_QUEST_DONE";
	case 44: return "CONDITION_CHARMED";
	case 45: return "CONDITION_PET_TYPE";
	case 46: return "CONDITION_TAXI";
	case 47: return "CONDITION_QUESTSTATE";
	default:
		return "UNKNOWN CONDITION {$conditionType}";
	}
}

function ConvertPoIIcon($icon)
{
	switch($icon)
	{
	case 7:
		return 6;
	default:
		echo "WARNING: Unhandled TC icon: " . $icon . PHP_EOL;
		return 0;
	}
}

function ConvertGameEventId($tcEvent)
{
	//TODO: handle negative events
	switch($tcEvent)
	{
	case 1: 
	case 2: 
	case 3:
	case 4:
	case 5:
	case 6:
	case 7:
	case 8:
	case 9:
	case 10:
	case 11:
	case 12:
	case 14:
	case 18:
	case 19:
	case 20:
	case 21:
	case 23:
		return $tcEvent;
	case 24:
		return 26;
	case 50:
		return 80; //Pirates' Day
	case 51:
		return 81; //Day of the Dead
	case 52:
		return 57;
	case 62:
		return 82;
	case 56:
		return 75;
	// ... incomplete table here... add if the warning is triggered
	default:
		echo "WARNING: Unhandled TC event: " . $tcEvent . PHP_EOL;
		return 0;
	}
}

function ConvertSpawnGroup(&$id, $guid)
{
	switch($id)
	{
	case 0:
	case 1:
	case 2:
	case 3:
	case 4:
		return true;
	case 44:
		$id = 7;
		return true;
	case 46:
		$id = 6;
		return true;
	default:
		echo "Unknown spawn group {$id} for tc guid {$guid}" . PHP_EOL;
		return false;
	}
}
