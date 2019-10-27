<?php
include_once("../rest.php");

$request = new RestRequest();
$vars = $request->getRequestVariables();

//$db = new PDO("pgsql:dbname=ladder_xchen13 host=localhost password=1846485 user=xchen13");
$db = new PDO("pgsql:dbname=ladder host=localhost password=314dev user=dev");
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if($request->isGet())
{
	if (count($vars) !== 1)
	{
		http_response_code(400);
	}
	else
	{
		$username = $vars["username"];
		http_response_code(200);

		$username_check_sql = "select count(*) from player where username = ?";
		$statement_check = $db->prepare($username_check_sql);
		$statement_check->execute([$username]);
		$check_results_array = $statement_check->fetch(PDO::FETCH_ASSOC);
		$check_results = intval($check_results_array["count"]);

		if($check_results !== 1)
		{
			$results = array("player" => "");	
		}
		else
		{
			try
			{
				$player_sql = "select name, email, rank, phone, username from player where username = ?";

				//prepare the statement i.e. make the statement for orders.
				$statement_player = $db->prepare($player_sql);

				//run the query
				$statement_player->execute([$username]);

				//get the results from orders
				$player_results = $statement_player->fetch(PDO::FETCH_ASSOC);



				//match win percentage
				//$match_win_percentage_sql = "select ";
				//$statement_match_win_percentage = $db->prepare($username);
				//$statement_match_win_percentage->execute([$username]);
				//$statement_match_win_percentage_result = $statement_match_win_percentage->fetch(PDO::FETCH_ASSOC);


				//game win percentage
				// //$game_win_percentage_sql = "select ";
				// $statement_game_win_percentage = $db->prepare($username);
				// $statement_game_win_percentage->execute([$username]);
				// $statement_game_win_percentage_result = $statement_game_win_percentage->fetch(PDO::FETCH_ASSOC);


				// $winning_margin = "select avg(winner_score - loser_score) from game where"
				// $statement_winning_margin = $db->prepare($username);
				// $statement_winning_margin->execute([$username]);
				// $statement_winning_margin_result = $statement_winning_margin->fetch(PDO::FETCH_ASSOC);

				// $losing_margin = "select"
				// $statement_losing_margin = $db->prepare($username);
				// $statement_losing_margin->execute([$username]);
				// $statement_losing_margin_result = $statement_losing_margin->fetch(PDO::FETCH_ASSOC);

				// $results = array_merge($player_results, $statement_match_win_percentage_result, $statement_game_win_percentage_result, $statement_winning_margin_result, $statement_losing_margin_result);
				$results = array_merge($player_results);
			}
			catch(PDOException $e)
			{
				$results = array("error_text" => $e->getMessage());
			}
		}
	}
	
	
	//$results = array("resource" => "player", "method" => "GET", "request_vars" => $vars)
}

elseif($request->isPost())
{
	// Check missing any inputs
	$input_array_length = count($vars);

	if($input_array_length === 5)
	{
		$name = $vars["name"];
		$email = $vars["email"];
		$phone = $vars["phone"];
		$username = $vars["username"];
		$password = $vars["password"];

		if(!preg_match("/^[0-9]{3}-[0-9]{4}-[0-9]{4}$/", $phone))
		{
			$results = array("error_text" => "Invalid phone number.");
		}
		if(filter_var($email, FILTER_VALIDATE_EMAIL))
		{
			$results = array("error_text" => "Invalid email address.");
		}
	}
	else
	{
		$results = array("error_text" => "Missing input(s).");
	}

	//unique username
	$username_check_sql = "select count(*) from player where username = ?";
	$statement_check_username = $db->prepare($username_check_sql);
	$statement_check_username->execute([$username]);
	$check_username_results_array = $statement_check_username->fetch(PDO::FETCH_ASSOC);
	$check_username_results = intval($check_username_results_array["count"]);

	//if the username is already exist, error text.
	if($check_username_results !== 0)
	{
		$results = array("error_text" => "This username has been used.");
	}

	try
	{
		$db->beginTransaction();

		$highest_rank_sql = "select max(rank) from player";
		$statement_highest_rank = $db->prepare($highest_rank_sql);
		$statement_highest_rank->execute();
		$highest_rank_result = $statement_highest_rank->fetch(PDO::FETCH_ASSOC);

		$current_rank = $highest_rank_result + 1;


		$sql = "insert into player (name, email, rank, phone, username, password) values (?, ?, ?, ?, ?)";
		$statement = $db->prepare($sql);
		$statement->execute($name, $email, $current_rank, $phone, $username, $password);

		$db->commit();

		$results = array("error_text" => "202");
	}
	catch(PDOException $e)//400
	{
		$results = array("error_text" => $e->getMessage());
	}	
	
	//$results = array("resource" => "player", "method" => "POST", "request_vars" => $vars);
}

elseif($request->isPut())
{
	$results = array("resource" => "player", "method" => "PUT", "request_vars" => $vars);
}

elseif($request->isDelete())
{
	$results = array("resource" => "player", "method" => "DELETE", "request_vars" => $vars);
}
echo(json_encode($results));
?>
