<?php
include_once("../rest.php");

$request = new RestRequest();
$vars = $request->getRequestVariables();

$db = new PDO("pgsql:dbname=ladder_xchen13 host=localhost password=1846485 user=xchen13");
//$db = new PDO("pgsql:dbname=ladder host=localhost password=314dev user=dev");
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$results = array();

// View a player's infomation
if($request->isGet())
{
	// If there is no "username" key, 
	if (!array_key_exists("username", $vars))
	{
		$results = array("error_text" => "No username given.");
		http_response_code(400);
	}
	else
	{
		$username = $vars["username"];
		http_response_code(200);

		//$check_results = username_exists($db, $username);

		// Check if there is a such username.
		$username_check_sql = "select count(*) from player where username = ?";
		$statement_check = $db->prepare($username_check_sql);
		$statement_check->execute([$username]);
		$check_results_array = $statement_check->fetch(PDO::FETCH_ASSOC);
		$check_results = intval($check_results_array["count"]);

		//If there is no player matched, then the player should be empty
		if($check_results === 0)
		{
			$results = array("player" => "");
			http_response_code(400);
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
		$results = array("error_text" => "No name given");
		$has_error = true;
		http_response_code(400);
	}

	//validate email 
	if(array_key_exists("email", $vars))
	{
		$email = $vars["email"];

		// If there is an "email" key word, but the format is bad, print the error.
		if(!filter_var($email, FILTER_VALIDATE_EMAIL))
		{
			$results = array("error_text" => "Invalid email format.");
			$has_error = true;
			http_response_code(400);
		}
	}
	else
	{
		// If there is no "name" key word, print the error.
		$results = array("error_text" => "No email given");
		$has_error = true;
	}


	// Phone number
	if(array_key_exists("phone", $vars))
	{
		$phone = $vars["phone"];

		// If there is a "phone" key word, but the format is bad, print the error.
		if(!preg_match("/^[0-9]{10}+$/", $phone))
		{
			$results = array("error_text" => "Invalid phone number format.");
			$has_error = true;
			http_response_code(400);
		}
	}
	else
	{
		// If there is no "phone" key word, print the error.
		$results = array("error_text" => "No phone number given");
		$has_error = true;
		http_response_code(400);
	}

	// Username
	if(array_key_exists("username", $vars))
	{
		$username = $vars["username"];
	}
	else
	{
		// If there is no "username" key word, print the error.
		$results = array("error_text" => "No username given");
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
		$results = array("error_text" => "No password given");
		$has_error = true;
		http_response_code(400);
	}

	// If the inputs are all accepted so far.
	if(!$has_error)
	{
		//unique username
		//if(username_exists($db, ))
		$username_check_sql = "select count(*) from player where username = ?";
		$statement_check_username = $db->prepare($username_check_sql);
		$statement_check_username->execute([$username]);
		$check_username_results_array = $statement_check_username->fetch(PDO::FETCH_ASSOC);
		$check_username_results = intval($check_username_results_array["count"]);

		//if the username is already exist, error text.
		if($check_username_results !== 0)
		{
			$results = array("error_text" => "This username has been used.");
			http_response_code(400);
		}
		try
		{
			$db->beginTransaction();

			// Get the current highest rank number.
			$highest_rank_sql = "select max(rank) from player";
			$statement_highest_rank = $db->prepare($highest_rank_sql);
			$statement_highest_rank->execute();
			$highest_rank_result = $statement_highest_rank->fetch(PDO::FETCH_ASSOC);

			$current_rank = $highest_rank_result["max"] + 1;

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
	//$results = array("resource" => "player", "method" => "POST", "request_vars" => $vars);
}

//updates the player with the given parameters.
elseif($request->isPut())
{
	//$username = $var["username"];// use which parameter as start?
	//$check_results = username_exists($db, $username);
	//check email is good. check phone# is good.(could same?4) check name(need exist). check rank is reasonable?
	// if(!check_results)
	// {
	// 	$results = array("error_text" => "Username not found.");
	// }
	// else
	// {

	// }

	$results = array("resource" => "player", "method" => "PUT", "request_vars" => $vars);
}

elseif($request->isDelete())
{
	$results = array("resource" => "player", "method" => "DELETE", "request_vars" => $vars);
}

// function username_exists($db, $username)
// {
// 	$exists = false;
// 	$username_check_sql = "select count(*) from player where username = ?";
// 	$statement_check_username = $db->prepare($username_check_sql);
// 	$statement_check_username->execute([$username]);
// 	$check_username_results_array = $statement_check_username->fetch(PDO::FETCH_ASSOC);
// 	$check_username_results = intval($check_username_results_array["count"]) == 1;

// 	return exists;
// }

echo(json_encode($results));
?>
