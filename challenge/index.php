<?php
include_once("../rest.php");

$request = new RestRequest();
$vars = $request->getRequestVariables();
if($request->isGet())
{
	$results = array("resource" => "challenge", "method" => "GET", "request_vars" = $vars);
}

elseif($request->isPost())
{
	$results = array("resource" => "challenge", "method" => "POST", "request_vars" = $vars);
}

elseif($request->isPut())
{
	$results = array("resource" => "challenge", "method" => "PUT", "request_vars" = $vars);
}

elseif($request->isDelete())
{
	$results = array("resource" => "challenge", "method" => "DELETE", "request_vars" = $vars);
}
echo(json_encode($results));

?>
