<?php
include_once("../rest.php");

$request = new RestRequest();
$vars = $request->getRequestVariables();
if($request->isGet())
{
	$results = array("resource" => "match", "method" => "GET", "request_vars" => $vars);
}

elseif($request->isPost())
{
	$results = array("resource" => "match", "method" => "POST", "request_vars" => $vars);
}

elseif($request->isPut())
{
	$results = array("resource" => "match", "method" => "PUT", "request_vars" => $vars);
}

elseif($request->isDelete())
{
	$results = array("resource" => "match", "method" => "DELETE", "request_vars" => $vars);
}
echo(json_encode($results));
?>
