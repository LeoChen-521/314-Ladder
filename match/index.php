<?php
include_once("../rest.php");
//include_once("") ???

$request = new RestRequest();
$vars = $request->getRequestVariables();

$db = new PDO("pgsql:dbname=ladder_xchen13 host=localhost password=1846485 user=xchen13");
//$db = new PDO("pgsql:dbname=ladder host=localhost password=314dev user=dev");
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$no_username_key = "No username given";
$no_games_key = "No game given";
$no_winner_key = "Missing winner name";
$no_loser_key = "Missing loser name";
$no_winner_score_key = "Missing winner score";
$no_loser_score_key = "Missing loser score";
$no_played_date_key = "No played date given";
$no_player1_key = "No player1 given";
$no_player2_key = "No player2 given";

$no_such_username = "No username found";
$no_date_found = "No date found";
$no_winner_username = "Username of winner does not exist";
$no_loser_username = "Username of loser does not exist";
$no_player1_username = "Username of player1 does not exist";
$no_player2_username = "Username of player2 does not exist";

$bad_played_date_format = "Invalid played date format";

$not_both_players_involved = "Both players must be involved";

// Returns the match.
if($request->isGet())
{
	$has_error = false;
	$has_played = false;
	// check if username given
	if($vars["username"] != null && array_key_exists("username", $vars))
	{
		//username is the same format as winner and loser.
		$username = $vars["username"];
		if(!username_exists($db, $username))
		{
			$results = array("error_text" => $no_such_username);
			$has_error = true;
			http_response_code(400);
			echo(json_encode($results));
			return;
		}
		//$data = $vars[]
		//echo(json_encode($vars));
		//http_response_code(200);
		// check if the played date given (optional)
		if(array_key_exists("played", $vars))
		{
			if($vars["played"] != null)
			{
				$played_date = $vars["played"];
				$has_played = true;

				//echo $played_date;
				// Check date format
				if(date_format_isValid($played_date)) // if bad
				{
					if (!read_date_exist($db, $username, $played_date))
					{
						$results = array("error_text" => $no_date_found);
						$has_error = true;
						http_response_code(400);
						echo(json_encode($results));
						return;
					}
				}
				else
				{
					$results = array("error_text" => $bad_played_date_format);
					$has_error = true;
					http_response_code(400);
					echo(json_encode($results));
					return;
				}
			}
			else
			{
				$results = array("error_text" => "Played is empty");
				$has_error = true;
				http_response_code(400);
				echo(json_encode($results));
				return;
			}
		}

		if(!$has_error)
		{
			if($has_played)
			{
				try
				{
					$sql_game_wlp = "select winner, loser, played, number, winner_score, loser_score from game where (winner = ? or loser = ?) and played = ?";
					//prepare the statement
					$statement_game_wlp = $db->prepare($sql_game_wlp);
					//run the query
					$statement_game_wlp->execute([$username, $username, $played_date]);
					//get the results
					$results = $statement_player->fetchAll(PDO::FETCH_ASSOC);

					http_response_code(200);
				}
				catch(PDOException $e)
				{
					$results = array("error_text" => $e->getMessage());
					http_response_code(400);
				}
			}
			elseif(!$has_played)
			{
				try
				{
					$sql_game_wl = "select winner, loser, played, number, winner_score, loser_score from game where winner = ? or loser = ?"; // add date?
					//prepare the statement
					$statement_game_wl = $db->prepare($sql_game_wl);
					//run the query
					$statement_game_wl->execute([$username, $username]);
					//get the results
					$results = $statement_game_wl->fetchAll(PDO::FETCH_ASSOC);

					http_response_code(200);
				}
				catch(PDOException $e)
				{
					$results = array("error_text" => $e->getMessage());
					http_response_code(400);
				}
			}
		}
	}
	else
	{
		$results = array("error_text" => $no_username_key);
		http_response_code(400);
	}
}

// Create a new match
elseif($request->isPost())
{
	$has_error = false;
	if(array_key_exists("games", $vars))
	{
		//array games
		$games = $vars["games"];
		// Check if the played date key given 
		if(array_key_exists("played", $vars))
		{
			$played_date = $vars["played"];
			//Check played date format
			if(!date_format_isValid($played_date))
			{
				$results = array("error_text" => $bad_played_date_format);
				$has_error = true;
				http_response_code(400);
			}
		}
		else
		{
			$results = array("error_text" => $no_played_date_key);
			$has_error = true;
			http_response_code(400);
		}

		// Create the match by posting the games 1 by 1.
		for($i=0; $i<count($games); $i++)
		{
			if(array_key_exists("winner", $games[$i]))
			{
				$winner = $games[$i]["winner"];
				if(!username_exists($db, $winner))
				{
					$results = array("error_text" => $no_winner_username);
					$has_error = true;
					http_response_code(400);
					echo(json_encode($results));
					return;
				}
			}
			else
			{
				$results = array("error_text" => $no_winner_key);
				$has_error = true;
				http_response_code(400);
			}

			if(array_key_exists("loser", $games[$i]))
			{
				$loser = $games[$i]["loser"];
				if(!username_exists($db, $loser))
				{
					$results = array("error_text" => $no_loser_username);
					$has_error = true;
					http_response_code(400);
					echo(json_encode($results));
					return;
				}
			}
			else
			{
				$results = array("error_text" => $no_loser_key);
				$has_error = true;
				http_response_code(400);
			}

			if(array_key_exists("winner_score", $games[$i])) //-1?
			{
				$winner_score = $games[$i]["winner_score"];
				if ($winner_score != 10)
				{
					$results = array("error_text" => "Invalid winner score");
					$has_error = true;
					http_response_code(400);
					echo(json_encode($results));
					return;
				}
			}
			else
			{
				$results = array("error_text" => $no_winner_score_key);
				$has_error = true;
				http_response_code(400);
			}

			if(array_key_exists("loser_score", $games[$i]))
			{
				$loser_score = $games[$i]["loser_score"];
				if ($loser_score < 0 || $loser_score === 10)
				{
					$results = array("error_text" => "Invalid loser score");
					$has_error = true;
					http_response_code(400);
					echo(json_encode($results));
					return;
				}
			}
			else
			{
				$results = array("error_text" => $no_loser_score_key);
				$has_error = true;
				http_response_code(400);
			}
		}
	}
	else
	{
		$results = array("error_text" => $no_games_key);
		$has_error = true;
		http_response_code(400);
	}

	// If the inputs for a match are given correctly, create the match
	if(!$has_error)
	{
		try
		{
			// Shift the ranks, if lower rank player wins higher rank player.
			$match_winner = determine_match_winner($vars, $games);
			$match_loser = determine_match_loser($vars, $games);

			$match_winner_current_rank = current_rank($db, $match_winner);
			$match_loser_current_rank = current_rank($db, $match_loser);

			// if the winner's rank is lower than the loser, than adjust the ranks.
			if ($match_winner_current_rank > $match_loser_current_rank)
			{
				// shift the loser's rank to the winner's old rank.
				// Also, along with any players in between move down one rank.
				shift_after_increase_player_rank($db, $match_winner_current_rank, $match_loser_current_rank);
			}

			$db->beginTransaction();

			for($i = 1; $i <= count($games); $i++)
			{
				//insert match query
				$sql = "insert into game (winner, loser, played, number, winner_score, loser_score) values (?, ?, ?, ?, ?, ?)";
				$statement = $db->prepare($sql);

				//for ($i = 1; $i < $)   //  games[0].winner
				$statement->execute([$games[$i-1]["winner"], $games[$i-1]["loser"], $played_date, $i, $games[$i-1]["winner_score"], $games[$i-1]["loser_score"]]);

			}

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

// No implementation
elseif($request->isPut())
{
	//$results = array("resource" => "match", "method" => "PUT", "request_vars" => $vars);
}

//Delete match. Players' ranks should not be adjusted,
elseif($request->isDelete())
{
	$has_error = false;
	// Check if the player1's key given
	if(array_key_exists("player1", $vars))
	{
		$player1 = $vars["player1"];
		// Check if the username of player1 exists.
		if(!username_exists($db, $player1))
		{
			$results = array("error_text" => $no_player1_username);
			$has_error = true;
			http_response_code(400);
			echo(json_encode($results));
			return;
		}
	}
	else
	{
		$results = array("error_text" => $no_player1_key);
		$has_error = true;
		http_response_code(400);
	}

	// Check if player2's key given.
	if(array_key_exists("player2", $vars))
	{
		$player2 = $vars["player2"];
		// Check if player2 exist.
		if(!username_exists($db, $player2))
		{
			$results = array("error_text" => $no_player2_username);
			$has_error = true;
			http_response_code(400);
			echo(json_encode($results));
			return;
		}
	}
	else
	{
		$results = array("error_text" => $no_player2_key);
		$has_error = true;
		http_response_code(400);
	}

	// Check if the played date key given
	if(array_key_exists("played", $vars))
	{
		$played_date = $vars["played"];
		//Check date format
		if(date_format_isValid($played_date))
		{
			// Check if the date exists
			if (!delete_date_exist($db, $played_date, $player1, $player2))
			{
				$results = array("error_text" => $no_date_found);
				$has_error = true;
				http_response_code(400);
			}
		}
		else
		{
			$results = array("error_text" => $bad_played_date_format);
			$has_error = true;
			http_response_code(400);
		}
	}
	else
	{
		$results = array("error_text" => $no_played_date_key);
		$has_error = true;
		http_response_code(400);
	}

	// If the given player1,2, and played date are all correct, delete the match
	if (!$has_error)
	{
		try
		{
			$db->beginTransaction();

			// delete match query
			$delete_match_sql = "delete from game where (winner = ? and loser = ? and played = ?) or (winner = ? and loser = ? and played = ?)";
			$statement_delete_match = $db->prepare($delete_match_sql);
			$statement_delete_match->execute([$player1, $player2, $played_date, $player2, $player1, $played_date]);

			$db->commit();

			$results = array("error_text" => " ");
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

// A function to adjust players' ranks if increase a player's rank.
function shift_after_increase_player_rank($db, $current_rank, $rank)
{
	// Set rank of the player to 0 temporarily, then we can shift other players' ranks.
	$db->beginTransaction();
	$set_zero_sql = "update player set rank = 0 where rank = ?";
	$set_zero_statement = $db->prepare($set_zero_sql);
	$set_zero_statement->execute([$current_rank]);
	$db->commit();

	// Shift players' ranks between them.
	for($i = $current_rank-1; $i >= $rank; $i--)
	{
		$db->beginTransaction();
		// Update player's rank query
		$sql = "update player set rank = ? where rank = ?";
		$statement = $db->prepare($sql);
		$statement->execute([$i+1, $i]);
		$db->commit();
	}

	// Put the temp rank 0 back to wanted place.
	$db->beginTransaction();
	$put_zero_back_sql = "update player set rank = ? where rank = 0";
	$put_zero_back_statement = $db->prepare($put_zero_back_sql);
	$put_zero_back_statement->execute([$rank]);
	$db->commit();
}

// Find the loser of a match
function determine_match_winner($vars, $games)
{
	$winner_username;
	$player1_username = $games[0]["winner"];
	$player2_username = $games[0]["loser"];

	$player1_win_count = 0;
	$player2_win_count = 0;

	for($i=0; $i<count($games); $i++)
	{
		if($games[$i]["winner"] === $player1_username)
		{
			$player1_win_count++;
		}
		else
		{
			$player2_win_count++;
		}
	}

	if($player1_win_count > $player2_win_count)
	{
		$winner_username = $player1_username;
	}
	else
	{
		$winner_username = $player2_username;
	}

	return $winner_username;
}

// Find the loser of a match
function determine_match_loser($vars, $games)
{
	$loser_username;
	$player1_username = $games[0]["winner"];
	$player2_username = $games[0]["loser"];

	$player1_win_count = 0;
	$player2_win_count = 0;

	for($i=0; $i<count($games); $i++)
	{
		if($games[$i]["winner"] === $player1_username)
		{
			$player1_win_count++;
		}
		else
		{
			$player2_win_count++;
		}
	}

	if($player1_win_count > $player2_win_count)
	{
		$loser_username = $player2_username;
	}
	else
	{
		$loser_username = $player1_username;
	}

	return $loser_username;
}
// Check date format
function date_format_isValid($played_date)
{
	$isValid = false;
	if(preg_match('/^(\d{4})-(\d{2})-(\d{2})\s(\d{2}):(\d{2}):(\d{2})$/', $played_date))
	{
		$isValid = true;
	}
	return $isValid;
}

// Check if the date exist. 2 players involved.
function delete_date_exist($db, $played_date, $player1, $player2)
{
	$exists = false;
	$date_check_sql = "select count(*) from game where (winner = ? and loser = ? and played = ?) or (winner = ? and loser = ? and played = ?)";
	$statement_check_date = $db->prepare($date_check_sql);
	$statement_check_date->execute([$player1, $player2, $played_date, $player2, $player1, $played_date]);
	$check_date_results_array = $statement_check_date->fetch(PDO::FETCH_ASSOC);
	$exists = intval($check_date_results_array["count"]) != 0;

	return $exists;
}

// Check if the date exist. 1 username
function read_date_exist($db, $played_date, $username)
{
	$exists = false;
	$date_check_sql = "select count(*) from game where (winner = ? or loser = ?) and played = ?";
	$statement_check_date = $db->prepare($date_check_sql);
	$statement_check_date->execute([$username, $username, $played_date]);
	$check_date_results_array = $statement_check_date->fetch(PDO::FETCH_ASSOC);
	$exists = intval($check_date_results_array["count"]) != 0;

	return $exists;
}
echo(json_encode($results));
?>
