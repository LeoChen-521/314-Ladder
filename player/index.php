<?php
include_once("../rest.php");

$request = new RestRequest();
$vars = $request->getRequestVariables();
if($request->isGet())
{
	$results = array("resource" => "player", "method" => "GET", "request_vars" => $vars);
}

elseif($request->isPost())
{
	$results = array("resource" => "player", "method" => "POST", "request_vars" => $vars);
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
