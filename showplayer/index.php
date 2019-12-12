<?php
include_once("../rest.php");

$request = new RestRequest();
$vars = $request->getRequestVariables();

$db = new PDO("pgsql:dbname=ladder_xchen13 host=localhost password=1846485 user=xchen13");
//$db = new PDO("pgsql:dbname=ladder host=localhost password=314dev user=dev");
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);



// View a player's infomation
if($request->isGet())
{
	$sql = "select name, username, rank from player order by rank";

	$statement = $db->prepare($sql);
	$statement->execute();
	$results = $statement->fetchAll(PDO::FETCH_ASSOC);
}


echo(json_encode($results));
?>
