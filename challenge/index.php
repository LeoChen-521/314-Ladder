<?php
include_once("../rest.php");

$request = new RestRequest();
$vars = $request->getRequestVariables();

$db = new PDO("pgsql:dbname=ladder_xchen13 host=localhost password=1846485 user=xchen13");
//$db = new PDO("pgsql:dbname=ladder host=localhost password=314dev user=dev");
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$no_username = "Username does not exist.";
$no_challenger_key = "No challenger key given";
$no_challengee_key = "No challengee key given";
$no_scheduled_key = "No scheduled key given";
$no_player_challenger_key = "No player or challenger key given";
$no_accepted_key = "No accepted key given";

$bad_scheduled = "The given scheduled is invalid";
$bad_accepted_date = "The given accepted date is invalid";

$player_not_involved = "player is not involved as either the challenger or challengee.";


$challengee_rank_too_high = "A player can only challenge another player who are at most ranked 3 spots above.";
$challengee_rank_too_low = "A player can only challenge another player who are not ranked below him or her.";
$challenger_accepted_another_player = "The challenger has already been accepted by another player.";
$challengee_accepted_another_player = "The challengee has already accepted another player.";
$no_such_challenge = "Challenge does not exist";

if($request->isGet())
{
	$has_error = false;
	$player_is_given = false;
	$challenger_is_given = false;

	if(array_key_exists("player", $vars))
	{
		if($vars["player"] != null && username_exists($db, $vars["player"]))
		{
			$player = $vars["player"];
			$player_is_given = true;
		}
		else
		{
			$results = array("error_text" => $no_username);
			$has_error = true;
			http_response_code(400);
			echo(json_encode($results));
			return;
		}
	}
	// else if the "challenger" parameter is given, return players available for that player to challenge.
	elseif(array_key_exists("challenger", $vars))
	{
		if($vars["challenger"] != null && username_exists($db, $vars["challenger"]))
		{
			$challenger = $vars["challenger"];
			$challenger_is_given = true;
		}
		else
		{
			$results = array("error_text" => $no_username);
			$has_error = true;
			http_response_code(400);
			echo(json_encode($results));
			return;
		}
	}
	else
	{
		$results = array("error_text" => $no_player_challenger_key);
		$has_error = true;
		http_response_code(400);
		echo(json_encode($results));
		return;
	}

	// Get the challenge names array
	//$names_array = outstanding_challenge_names();
	// if(!array_key_exists($player, $names_array))
	// {
	// 	$results = array("error_text" => $player_not_involved);
	// 	$has_error = true;
	// 	http_response_code(400);
	// 	return;
	// }

	// if player is given then returns the challenge.
	if(!$has_error && $player_is_given)
	{
		try
		{
			$sql = "select * from challenge where (challenger = ?) or (challengee = ?)";
			//prepare the statement
			$statement = $db->prepare($sql);
			//run the query
			$statement->execute([$player, $player]);
			//get the results
			$result_array = $statement->fetchAll(PDO::FETCH_ASSOC);

			$results = ["challenges" => $result_array];

			http_response_code(200);
		}
		catch(PDOException $e)
		{
			$results = array("error_text" => $e->getMessage());
			http_response_code(400);
		}
	}
	//An alternative challenge read operation, if the "challenger" parameter is given, return players available for that player to challenge.
	elseif(!$has_error && $challenger_is_given)
	{
		// cannot accepted situations
		try
		{
			$challenger_current_rank = current_rank($db, $challenger);
			$available_high_rank = $challenger_current_rank - 3;

			$sql = "select name, email, rank, phone, username from player where (rank < ? and rank >= ?)";
			//prepare the statement
			$statement = $db->prepare($sql);
			//run the query
			$statement->execute([$challenger_current_rank, $available_high_rank]);
			//get the results
			$result_array = $statement->fetchAll(PDO::FETCH_ASSOC);

			$results = ["candidates" => $result_array];

			http_response_code(200);
		}
		catch(PDOException $e)
		{
			$results = array("error_text" => $e->getMessage());
			http_response_code(400);
		}
	}
}

elseif($request->isPost())
{
	$has_error = false;
	if(array_key_exists("challenger", $vars))
	{
		// if input(challenger) is not null and input username exist.
		if($vars["challenger"] != null && username_exists($db, $vars["challenger"]))
		{
			$challenger = $vars["challenger"];
		}
		else
		{
			$results = array("error_text" => $no_username);
			$has_error = true;
			http_response_code(400);
			echo(json_encode($results));
			return;
		}
	}
	else
	{
		$results = array("error_text" => $no_challenger_key);
		$has_error = true;
		http_response_code(400);
		echo(json_encode($results));
		return;
	}

	if(array_key_exists("challengee", $vars))
	{
		// if input(challenger) is not null and input username exist.
		if ($vars["challengee"] != null && username_exists($db, $vars["challengee"]))
		{
			$challengee = $vars["challengee"];
		}
		else
		{
			$results = array("error_text" => $no_username);
			$has_error = true;
			http_response_code(400);
			echo(json_encode($results));
			return;
		}
	}
	else
	{
		$results = array("error_text" => $no_challengee_key);
		$has_error = true;
		http_response_code(400);
		echo(json_encode($results));
		return;
	}

	if(array_key_exists("scheduled", $vars))
	{
		// if input(challenger) is not null and input username exist.
		if ($vars["scheduled"] != null && date_format_isValid($vars["scheduled"]))
		{
			$scheduled = $vars["scheduled"];
		}
		else
		{
			$results = array("error_text" => $bad_scheduled);
			$has_error = true;
			http_response_code(400);
			echo(json_encode($results));
			return;
		}
	}
	else
	{
		$results = array("error_text" => $no_scheduled_key);
		$has_error = true;
		http_response_code(400);
		echo(json_encode($results));
		return;
	}

	// challenger and challengee and scheduled are all gotten above.
	// TODO: Check the combination unique or not

	// get the current ranks of challenger and challengee

	$challenger_rank = current_rank($db, $challenger);
	$challengee_rank = current_rank($db, $challengee);

	//cannot challenge oneself
	// if($challengee_rank === $challenger_rank)
	// {
	// 	$results = array("error_text" => "Cannot challenge oneself");
	// 	$has_error = true;
	// 	http_response_code(400);
	// 	return;
	// }

	// Make sure a player can only challenge another play who are at most ranked 3 spots above.
	if($challenger_rank - $challengee_rank > 3)
	{
		$results = array("error_text" => $challengee_rank_too_high);
		$has_error = true;
		http_response_code(400);
		echo(json_encode($results));
		return;
	}

	// Make sure a player can only challenge another play who are not ranked below them.
	if($challenger_rank - $challengee_rank <= 0)
	{
		$results = array("error_text" => $challengee_rank_too_low);
		$has_error = true;
		http_response_code(400);
		echo(json_encode($results));
		return;
	}

	//Make sure a player can only challenge another play who are not a party to an outstanding challenge.
	// TODO: marks accepted people
	$outstanding_challenge_name_array = outstanding_challenge_names($db);
	if(array_key_exists($challenger, $outstanding_challenge_name_array))
	{
		$results = array("error_text" => $challenger_accepted_another_player);
		$has_error = true;
		http_response_code(400);
		echo(json_encode($results));
		return;
	}
	if(array_key_exists($challengee, $outstanding_challenge_name_array))
	{
		$results = array("error_text" => $challengee_accepted_another_player);
		$has_error = true;
		http_response_code(400);
		echo(json_encode($results));
		return;
	}


	// inputs are checked.
	if (!$has_error)
	{
		try
		{
			$db->beginTransaction();
			//insert challenge query
			$sql = "insert into challenge (challenger, challengee, issued, accepted, scheduled) values (?, ?, ?, ?, ?)";
			$statement = $db->prepare($sql);

			//issued date and time
			$issued = date("Y-m-d");
			$accepted = null;

			$statement->execute([$challenger, $challengee, $issued, $accepted, $scheduled]);
			$db->commit();

			$results = array("error_text" => "");
			http_response_code(202);

		}
		catch(PDOException $e)
		{
			$results = array("error_text" => $e->getMessage());
			http_response_code(400);
		}
	}
}

elseif($request->isPut())
{
	$has_error = false;
	// Check challenger
	if(array_key_exists("challenger", $vars))
	{
		if($vars["challenger"] != null && username_exists($db, $vars["challenger"]))
		{
			$challenger = $vars["challenger"];
		}
		else
		{
			$results = array("error_text" => $no_username);
			$has_error = true;
			http_response_code(400);
			echo(json_encode($results));
			return;
		}
	}
	else
	{
		$results = array("error_text" => $no_challenger_key);
		$has_error = true;
		http_response_code(400);
		echo(json_encode($results));
		return;
	}
	// Check challengee
	if(array_key_exists("challengee", $vars))
	{
		if($vars["challengee"] != null && username_exists($db, $vars["challengee"]))
		{
			$challengee = $vars["challengee"];
		}
		else
		{
			$results = array("error_text" => $no_username);
			$has_error = true;
			http_response_code(400);
			echo(json_encode($results));
			return;
		}
	}
	else
	{
		$results = array("error_text" => $no_challengee_key);
		$has_error = true;
		http_response_code(400);
		echo(json_encode($results));
		return;
	}
	// Check scheduled
	if(array_key_exists("scheduled", $vars))
	{
		if($vars["scheduled"] != null && date_format_isValid($vars["scheduled"]))
		{
			$scheduled = $vars["scheduled"];
		}
		else
		{
			$results = array("error_text" => $bad_scheduled);
			$has_error = true;
			http_response_code(400);
			echo(json_encode($results));
			return;
		}
	}
	else
	{
		$results = array("error_text" => $no_scheduled_key);
		$has_error = true;
		http_response_code(400);
		echo(json_encode($results));
		return;
	}

	if(array_key_exists("accepted", $vars))
	{
		if($vars["accepted"] != null && accepted_date_format_isValid($vars["accepted"]))
		{
			$accepted = $vars["accepted"];
		}
		else
		{
			$results = array("error_text" => $bad_accepted_date);
			$has_error = true;
			http_response_code(400);
			echo(json_encode($results));
			return;
		}
	}
	else
	{
		$results = array("error_text" => $no_accepted_key);
		$has_error = true;
		http_response_code(400);
		echo(json_encode($results));
		return;
	}


	// If there is no such challenge exists, print the error.
	if(!challenge_exists($db, $challenger, $challengee, $scheduled))
	{
		$results = array("error_text" => $no_such_challenge);
		$has_error = true;
		http_response_code(400);
		echo(json_encode($results));
		return;
	}

	if(!$has_error)
	{
		try
		{
			$db->beginTransaction();
			// update challenge query TODO: updates  change accepted
			$sql = "update challenge set accepted = ? where challenger = ? and challengee = ? and scheduled = ?";

			$statement = $db->prepare($sql);
			$statement->execute([$accepted, $challenger, $challengee, $scheduled]);

			$db->commit();

			// Delete all other outstanding challenges involving the challenger and challengee
			delete_after_updates($db, $challenger, $challengee);

			$results = array("error_text" => "");
			http_response_code(200);
		}
		catch(PDOException $e)
		{
			$results = array("error_text" => $e->getMessage());
			http_response_code(400);
		}
	}
}

elseif($request->isDelete())
{
	$has_error = false;
	// Check challenger
	if(array_key_exists("challenger", $vars))
	{
		if($vars["challenger"] != null && username_exists($db, $vars["challenger"]))
		{
			$challenger = $vars["challenger"];
		}
		else
		{
			$results = array("error_text" => $no_username);
			$has_error = true;
			http_response_code(400);
			echo(json_encode($results));
			return;
		}
	}
	else
	{
		$results = array("error_text" => $no_challenger_key);
		$has_error = true;
		http_response_code(400);
		echo(json_encode($results));
		return;
	}
	// Check challengee
	if(array_key_exists("challengee", $vars))
	{
		if($vars["challengee"] != null && username_exists($db, $vars["challengee"]))
		{
			$challengee = $vars["challengee"];
		}
		else
		{
			$results = array("error_text" => $no_username);
			$has_error = true;
			http_response_code(400);
			echo(json_encode($results));
			return;
		}
	}
	else
	{
		$results = array("error_text" => $no_challengee_key);
		$has_error = true;
		http_response_code(400);
		echo(json_encode($results));
		return;
	}
	// Check scheduled
	if(array_key_exists("scheduled", $vars))
	{
		if($vars["scheduled"] != null && date_format_isValid($vars["scheduled"]))
		{
			$scheduled = $vars["scheduled"];
		}
		else
		{
			$results = array("error_text" => $bad_scheduled);
			$has_error = true;
			http_response_code(400);
			echo(json_encode($results));
			return;
		}
	}
	else
	{
		$results = array("error_text" => $no_scheduled_key);
		$has_error = true;
		http_response_code(400);
		echo(json_encode($results));
		return;
	}

	// If there is no such challenge exists, print the error.
	if(!challenge_exists($db, $challenger, $challengee, $scheduled))
	{
		$results = array("error_text" => $no_such_challenge);
		$has_error = true;
		http_response_code(400);
		echo(json_encode($results));
		return;
	}

	if (!$has_error)
	{
		try
		{
			$db->beginTransaction();

			// delete challenge query
			$delete_challenge_sql = "delete from challenge where (challenger = ? and challengee = ? and scheduled = ?)";
			$statement_delete_challenge = $db->prepare($delete_challenge_sql);
			$statement_delete_challenge->execute([$challenger, $challengee, $scheduled]);

			$db->commit();

			$results = array("error_text" => "");
			http_response_code(200);
		}
		catch(PDOException $e)
		{
			$results = array("error_text" => $e->getMessage());
			http_response_code(400);
		}
	}
}


//Check the username exists.
function username_exists($db, $username)
{
	$exists = false;
	$username_check_sql = "select count(*) from player where username = ?";
	$statement_check_username = $db->prepare($username_check_sql);
	$statement_check_username->execute([$username]);
	$check_username_results_array = $statement_check_username->fetch(PDO::FETCH_ASSOC);
	$exists = intval($check_username_results_array["count"]) == 1;

	return $exists;
}

//Check the challenge exists.
function challenge_exists($db, $challenger, $challengee, $scheduled)
{
	$exists = false;
	$challenge_check_sql = "select count(*) from challenge where challenger = ? and challengee = ? and scheduled = ?";
	$statement_check_challenge = $db->prepare($challenge_check_sql);
	$statement_check_challenge->execute([$challenger, $challengee, $scheduled]);
	$check_challenge_results_array = $statement_check_challenge->fetch(PDO::FETCH_ASSOC);
	$exists = intval($check_challenge_results_array["count"]) >= 1;

	return $exists;
}

// Returns the player's current rank.
function current_rank($db, $username)
{
	$current_rank_sql = "select rank from player where username = ?";
	$statement_current_rank = $db->prepare($current_rank_sql);
	$statement_current_rank->execute([$username]);
	$current_rank_result = $statement_current_rank->fetch(PDO::FETCH_ASSOC);
	$current_rank = $current_rank_result["rank"];

	return $current_rank;
}

// Returns an array of players' names who are a party to an outstanding challenge already.
function outstanding_challenge_names($db)
{
	$sql = "select challenger, challengee from challenge where accepted IS NOT NULL";
	$statement = $db->prepare($sql);
	$statement->execute();
	$outstanding_challenge_name_array_result = $statement->fetchAll(PDO::FETCH_ASSOC);

	return $outstanding_challenge_name_array_result;
}

// Check date format TODO: check time 25:00:00?? check time reasonable?
function date_format_isValid($played_date)
{
	$isValid = false;
	if(preg_match('/^(\d{4})-(\d{2})-(\d{2})\s(\d{2}):(\d{2}):(\d{2})$/', $played_date))
	{
		$isValid = true;
	}
	return $isValid;
}

// Check date format TODO: check time 25:00:00?? check time reasonable?
function accepted_date_format_isValid($accepted)
{
	$isValid = false;
	if(preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $accepted))
	{
		$isValid = true;
	}
	return $isValid;
}

// After updates the challenge accepted field, all other outstanding challenges involving the challenger and challengee must be deleted
function delete_after_updates($db, $challenger, $challengee)
{
	$db->beginTransaction();
	$sql = "delete from challenge where ((challenger = ? or challengee = ?) and (accepted IS NULL)) or ((challenger = ? or challengee = ?) and (accepted IS NULL))";
	$statement = $db->prepare($sql);
	$statement->execute([$challenger, $challengee, $challengee, $challenger]);
	$db->commit();
}








echo(json_encode($results));
?>
