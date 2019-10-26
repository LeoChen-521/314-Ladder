<?php
include_once("../rest.php");

$request = new RestRequest();
$vars = $request->getRequestVariables();
if($request-> isGet())
{
	$results = array("resource" => "player", "method" => "get", "request_vars" = $vars);
}

elseif($request-> isPost())
{
	$results = array("resource" => "player", "method" => "post", "request_vars" = $vars);
}

elseif($request-> isPut())
{
	$results = array("resource" => "player", "method" => "put", "request_vars" = $vars);
}

elseif($request-> isDelete())
{
	$results = array("resource" => "player", "method" => "delete", "request_vars" = $vars);
}
echo(json_encode($results));
?>
