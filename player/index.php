<?php
include_once("../rest.php");

$request = new RestRequest();
$vars = $request->getRequestVariables();

$db = new PDO("pgsql:dbname=ladder_xchen13 host=localhost password=1846485 user=xchen13");
//$db = new PDO("pgsql:dbname=ladder host=localhost password=314dev user=dev");
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$results = array();

//No input key strings
$no_username_key = "No username given";
$no_name_key = "No name given";
$no_phone_key = "No phone number given";
$no_email_key = "No email given";
$no_password_key = "No password given";
//Invalid input strings
$bad_username = "Invalid username";
$username_not_exist = "Username does not exist";
$bad_phone_number_format = "Invalid phone number format";
$bad_email_format = "Invalid email address format";

// View a player's infomation
if($request->isGet())
{
	// If there is no "username" key.
	if (!array_key_exists("username", $vars))
	{
		$results = array("error_text" => $no_username_key);
		http_response_code(400);
	}
	else
	{
		$username = $vars["username"];
		http_response_code(200);

		//If there is no player matched, then the player should be empty
		if(!username_exists($db, $username))
		{
			//$results = array("player" => "");// error text?
			$results = array("error_text" => $username_not_exist);
		}
		else
		{
			try
			{
				$player_sql = "select name, email, rank, phone, username from player where username = ?";
				//prepare the statement i.e. make the statement for player.
				$statement_player = $db->prepare($player_sql);
				//run the query
				$statement_player->execute([$username]);
				//get the results from player.
				$player_results = $statement_player->fetch(PDO::FETCH_ASSOC);

				//$total_games_count_sql

				//match win percentage
				$match_win_percentage_sql = "select round(coalesce(win_count,0)/round((coalesce(win_count,0)+ coalesce(loss_count, 0)),2),2) from player left join (Select winner, count(*) as win_count from match_view group by winner) as w on w.winner = username left join (Select loser, count(*) as loss_count from match_view group by loser) as l on l.loser = username where username = ?";
				//Prepare the statement
				$statement_match_win_percentage = $db->prepare($match_win_percentage_sql);
				//Run the query
				$statement_match_win_percentage->execute([$username]);
				//get the result
				$statement_match_win_percentage_result = $statement_match_win_percentage->fetch(PDO::FETCH_ASSOC);

				$match_win_percentage_array = ["match_win_percentage" => $statement_match_win_percentage_result["round"]];

				//game win percentage
				$game_win_percentage_sql = "select round(coalesce(win_count,0)/round((coalesce(win_count,0)+ coalesce(loss_count, 0)),2),2) from player left join (Select winner, count(*) as win_count from game group by winner) as w on w.winner = username left join (Select loser, count(*) as loss_count from game group by loser) as l on l.loser = username where username = ? ";
				//Prepare the statement
				$statement_game_win_percentage = $db->prepare($game_win_percentage_sql);
				//Run the query
				$statement_game_win_percentage->execute([$username]);
				//get the result
				$statement_game_win_percentage_result = $statement_game_win_percentage->fetch(PDO::FETCH_ASSOC);

				$game_win_percentage_array = ["game_win_percentage" => $statement_game_win_percentage_result["round"]];

				//Winning margin query
				$winning_margin_sql = "select avg(winner_score - loser_score) from game where winner = ?";
				//Prepare the statement
				$statement_winning_margin = $db->prepare($winning_margin_sql);
				//Run the query
				$statement_winning_margin->execute([$username]);
				//get the result
				$statement_winning_margin_result = $statement_winning_margin->fetch(PDO::FETCH_ASSOC);

				$winning_margin_array = ["winning_margin" => $statement_winning_margin_result["avg"]];

				//Losing margin query.
				$losing_margin_sql = "select avg(winner_score - loser_score) from game where loser = ?";
				//Prepare the statement
				$statement_losing_margin = $db->prepare($losing_margin_sql);
				//Run the query
				$statement_losing_margin->execute([$username]);
				//get the result
				$statement_losing_margin_result = $statement_losing_margin->fetch(PDO::FETCH_ASSOC);

				$losing_margin_array = ["losing_margin" => $statement_losing_margin_result["avg"]];

				//Combine the info of the player together.
				$results = array_merge($player_results, $match_win_percentage_array, $game_win_percentage_array, $winning_margin_array, $losing_margin_array);
			}
			catch(PDOException $e)
			{
				$results = array("error_text" => $e->getMessage());
			}
		}
	}
	//$results = array("resource" => "player", "method" => "GET", "request_vars" => $vars)
}

// Create a player with the given inputs.
elseif($request->isPost())
{
	// An error flag.
	$has_error = false;
	// Check the inputs given or not.
	// name
	if(array_key_exists("name", $vars))
	{
		$name = $vars["name"];
	}
	else
	{
		// If there is no "name" key word, print the error.
		$results = array("error_text" => $no_name_key);
		$has_error = true;
		http_response_code(400);
	}

	//validate email 
	if(array_key_exists("email", $vars))
	{
		$email = $vars["email"];
		// If there is an "email" key word, but the format is bad, print the error.
		if(!email_format_isValid($email))
		{
			$results = array("error_text" => $bad_email_format);
			$has_error = true;
			http_response_code(400);
		}
	}
	else
	{
		// If there is no "email" key word, print the error.
		$results = array("error_text" => $no_email_key);
		$has_error = true;
		http_response_code(400);
	}

	// Phone number
	if(array_key_exists("phone", $vars))
	{
		$phone = $vars["phone"];
		// If there is a "phone" key word, but the format is bad, print the error.
		if(!phone_number_format_isValid($phone))
		{
			$results = array("error_text" => $bad_phone_number_format);
			$has_error = true;
			http_response_code(400);
		}
	}
	else
	{
		// If there is no "phone" key word, print the error.
		$results = array("error_text" => $no_phone_key);
		$has_error = true;
		http_response_code(400);
	}

	// Username
	if(array_key_exists("username", $vars))
	{
		$username = $vars["username"];
		//unique username
		//if the username is already exist, error text.
		if(username_exists($db, $username))
		{
			$results = array("error_text" => "This username has been used.");
			$has_error = true;
			http_response_code(400);
		}
	}
	else
	{
		// If there is no "username" key word, print the error.
		$results = array("error_text" => $no_username_key);
		$has_error = true;
		http_response_code(400);
	}

	// Password
	if(array_key_exists("password", $vars))
	{
		$password = $vars["password"];
	}
	else
	{
		// If there is no "password" key word, print the error.
		$results = array("error_text" => $no_password_key);
		$has_error = true;
		http_response_code(400);
	}

	// If the inputs keys and formats are all accepted so far, create the player.
	if(!$has_error)
	{
		try
		{
			$db->beginTransaction();

			// Get the current highest rank number.
			$current_rank = highest_rank($db) + 1;

			//insert player query
			$sql = "insert into player (name, email, rank, phone, username, password) values (?, ?, ?, ?, ?, ?)";
			$statement = $db->prepare($sql);
			$statement->execute([$name, $email, $current_rank, $phone, $username, password_hash($password, PASSWORD_DEFAULT)]);
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

//updates the player with the given parameters.
elseif($request->isPut())
{
	// An error flag.
	$has_error = false;
	// Check if there is a key of username.
	if(!array_key_exists("username", $vars))
	{
		//no username key, print the error.
		$results = array("error_text" => $no_username_key);
		$has_error = true;
		http_response_code(400);
	}
	else
	{
		$username = $vars["username"];
		// check if the username exists in the database.
		if(!username_exists($db, $username))
		{
			$results = array("error_text" => $username_not_exist);
			$has_error = true;
			http_response_code(400);
		}
		else
		{
			// Check if there is a key of name.
			if(array_key_exists("name", $vars))
			{
				$name = $vars["name"];
			}
			else
			{
				//no username key, print the error.
				$results = array("error_text" => $no_name_key);
				$has_error = true;
				http_response_code(400);
			}

			// Check if there is a key of email.
			if(array_key_exists("email", $vars))
			{
				$email = $vars["email"];
				//If the email key exist, but the format is bad.
				if(!email_format_isValid($email))
				{
					// Bad email format, print the error
					$results = array("error_text" => $bad_email_format);
					$has_error = true;
					http_response_code(400);
				}
			}
			else
			{
				//no email key, print the error.
				$results = array("error_text" => $no_email_key);
				$has_error = true;
				http_response_code(400);
			}

			// Check if there is a key of phone
			if(array_key_exists("phone", $vars))
			{
				$phone = $vars["phone"];
				// If the phone key exist, but the format is bad.
				if(!phone_number_format_isValid($phone))
				{
					// Bad phone number format, print the error
					$results = array("error_text" => $bad_phone_number_format);
					$has_error = true;
					http_response_code(400);
				}
			}
			else
			{
				//no phone key, print the error.
				$results = array("error_text" => $no_phone_key);
				$has_error = true;
				http_response_code(400);
			}

			// Check if there is a key of rank
			if(array_key_exists("rank", $vars))
			{
				$rank = $vars["rank"];
				// Get the current highest rank number.
				$current_highest_rank = highest_rank($db);

				// The given rank has to be larger than 0 and less than the current highest rank + 1.
				if(($rank <= 0) || ($rank > $current_highest_rank) || !is_int($rank))//rank reasonable?
				{
					$results = array("error_text" => "Given rank number is out of range, unable to insert");
					$has_error = true;
					http_response_code(400);
				}
			}
			else
			{
				$results = array("error_text" => "No rank given");
				$has_error = true;
				http_response_code(400);
			}
			//check email is good. check phone# is good.(could same?4) check name(need exist). check rank is reasonable?

			// If the inputs keys/formats are good, update the player.
			if(!$has_error)
			{
				try
				{
					$current_rank = current_rank($db, $username);

					// If a new rank given, shift.
					// If increase rank (decrese rank number), players between them shift downward.
					if($current_rank > $rank)
					{
						shift_after_increase_player_rank($db, $username, $current_rank, $rank);
					}
					// If decrease rank (increase rank number), players between them shift upward.
					elseif($current_rank < $rank)
					{
						shift_after_decrease_player_rank($db, $username, $current_rank, $rank);
					}

					$db->beginTransaction();
					
					// Update player query
					$sql = "update player set name = ?, email = ?, rank = ?, phone = ? where username = ?";
					$statement = $db->prepare($sql);
					$statement->execute([$name, $email, $rank, $phone, $username]);
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
	}
}

// Delete a player by using a given username.
elseif($request->isDelete())
{
	//
	if(!array_key_exists("username", $vars))
	{
		//no username key, print the error.
		$results = array("error_text" => $no_username_key);
		http_response_code(400);
	}
	else
	{
		$username = $vars["username"];
		//If there is no player matched, then the player should be empty
		if(!username_exists($db, $username))
		{
			//$results = array("player" => "");// error text?
			$results = array("error_text" => "Player does not exist");
			http_response_code(400);
		}
		else
		{		
			try
			{
				$deleted_rank = current_rank($db, $username);

				$db->beginTransaction();

				// Delete player query
				$sql = "Delete from player where username = ?";
				$statement = $db->prepare($sql);
				$statement->execute([$username]);
				$db->commit();

				shift_after_delete($db, $username, $deleted_rank);

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

// Check phone number formate
function phone_number_format_isValid($phone)
{
	$isValid = false;
	if(preg_match("/^[0-9]{10}+$/", $phone))
	{
		$isValid = true;
	}
	return $isValid;
}

// Check email format
function email_format_isValid($email)
{
	$isValid = false;
	if(filter_var($email, FILTER_VALIDATE_EMAIL))
	{
		$isValid = true;
	}
	return $isValid;
}

// Returns the current highest rank number.
function highest_rank($db)
{
	$highest_rank_sql = "select max(rank) from player";
	$statement_highest_rank = $db->prepare($highest_rank_sql);
	$statement_highest_rank->execute();
	$highest_rank_result = $statement_highest_rank->fetch(PDO::FETCH_ASSOC);
	$highest_rank = $highest_rank_result["max"];

	return $highest_rank;
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

// Adjust player's rank after deleting.
function shift_after_delete($db, $username, $deleted_rank)
{
	$max_rank = highest_rank($db);
	// Shift players' ranks between them.
	for($i = $deleted_rank+1; $i <= $max_rank; $i++)
	{
		$db->beginTransaction();
		// Update player's rank query
		$sql = "update player set rank = ? where rank = ?";
		$statement = $db->prepare($sql);
		$statement->execute([$i-1, $i]);
		$db->commit();
	}
}

// A function to adjust players' ranks if increase a player's rank.
function shift_after_increase_player_rank($db, $username, $current_rank, $rank)
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

// A function to adjust players' ranks if decrease a player's rank.
function shift_after_decrease_player_rank($db, $username, $current_rank, $rank)
{
// Set rank of the player to 0 temporarily, then we can shift other players' ranks.
	$db->beginTransaction();
	$set_zero_sql = "update player set rank = 0 where rank = ?";
	$set_zero_statement = $db->prepare($set_zero_sql);
	$set_zero_statement->execute([$current_rank]);
	$db->commit();

	for($i = $current_rank+1; $i <= $rank; $i++)
	{
		$db->beginTransaction();
		// Update player's rank query
		$sql = "update player set rank = ? where rank = ?";
		$statement = $db->prepare($sql);
		$statement->execute([$i-1, $i]);
		$db->commit();
	}
	$db->beginTransaction();
	$put_zero_back_sql = "update player set rank = ? where rank = 0";
	$put_zero_back_statement = $db->prepare($put_zero_back_sql);
	$put_zero_back_statement->execute([$rank]);
	$db->commit();
}
echo(json_encode($results));
?>
