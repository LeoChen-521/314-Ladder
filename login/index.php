<?php
include_once("../rest.php");

//die("abcd");

$request = new RestRequest();
$vars = $request->getRequestVariables();

$db = new PDO("pgsql:dbname=ladder_xchen13 host=localhost password=1846485 user=xchen13");
//$db = new PDO("pgsql:dbname=ladder host=localhost password=314dev user=dev");
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$no_username_key = "Username is not provided.";
$no_password_key = "Username is not provided.";


$no_username = "Username does not exists.";
$no_password = "No password entered.";
$wrong_password_or_username = "Password and username are not matched.";

// Q: If the combination of username and password, establish a server side session?
if($request->isGet())
{
	// Set the username variable and echo the results as JSON.
	session_start();
	$_SESSION["username"] = $username;
	$results = '{"username": "$username"}';
}

//Returns the username of the current logged-in user
elseif($request->isPost())
{
	// Get the current username and return it
	$has_error = false;
	$has_player = false;
	if(array_key_exists("username", $vars))
	{
		if($vars["username"] != null)
		{
			$username = $vars["username"];
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
		$results = array("error_text" => $no_username_key);
		$has_error = true;
		http_response_code(400);
		echo(json_encode($results));
		return;
	}

	if(array_key_exists("password", $vars))
	{
		if($vars["password"] != null)
		{
			$input_password = $vars["password"];
		}
		else
		{
			$results = array("error_text" => $no_password);
			$has_error = true;
			http_response_code(400);
			echo(json_encode($results));
			return;
		}
	}
	else
	{
		$results = array("error_text" => $no_password_key);
		$has_error = true;
		http_response_code(400);
		echo(json_encode($results));
		return;
	}

	// if username is valid (exist), and password is provided. Then check the combination.

	//die(password_hash($input_password, PASSWORD_DEFAULT));

	//$has_player = find_password_username($db, $username，$input_password);
	//$database_password = database_hashed_password($db, $username);

	if (password_verify($input_password, database_hashed_password($db, $username)))
	{
		$has_player = true;
	}



	// If matched, then establish a server side session.
	if(!$has_player)
	{
		$results = array("error_text" => $wrong_password_or_username);
		$has_error = true;
		http_response_code(403);
		echo(json_encode($results));
	}

	// if inputs are all valid and matched. Start the session
	if(!$has_error)
	{
		//start session
		session_start();
		// session_register("myusername");
		$_SESSION["username"] = $username;
		http_response_code(200);
	}
}

// Ends the server side session for the currently logged-in user
elseif($request->isDelete())
{
	session_start();
	session_unset();
	session_destroy();
	http_response_code(200);
}

//Check the username exists.
// function username_exists($db, $username)
// {
// 	$exists = false;
// 	$username_check_sql = "select count(*) from player where username = ?";
// 	$statement_check_username = $db->prepare($username_check_sql);
// 	$statement_check_username->execute([$username]);
// 	$check_username_results_array = $statement_check_username->fetch(PDO::FETCH_ASSOC);
// 	$exists = intval($check_username_results_array["count"]) == 1;

// 	return $exists;
// }

// Find the password hash from the database by using the username.
// function find_password_username($db, $username, $password)
// {
// 	$exists = false;
// 	$sql = "select count(*) from player where username = ? and password = ?";
// 	$statement = $db->prepare($sql);
// 	$statement->execute([$username, $password]);
// 	$statement->fetch(PDO::FETCH_ASSOC);
// 	$exists = intval($statement["count"]) == 1;

// 	return $exists;
// }

function database_hashed_password($db, $username)
{
	$sql = "select password from player where username = ?";
	$statement = $db->prepare($sql);
	$statement->execute([$username]);
	$result = $statement->fetch(PDO::FETCH_ASSOC);
	$hashed_password = $result["password"];

	return $hashed_password;
}





//echo(json_encode($results));
?>
