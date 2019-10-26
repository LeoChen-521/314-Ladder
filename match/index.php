<?php
include_once("../rest.php");

$request = new RestRequest();
$vars = $request->getRequestVariables();
if($request-> isGet())
{
	$results = array("resource" => "match", "method" => "get", "request_vars" = $vars);
}

elseif($request-> isPost())
{
	$results = array("resource" => "match", "method" => "post", "request_vars" = $vars);
}

elseif($request-> isPut())
{
	$results = array("resource" => "match", "method" => "put", "request_vars" = $vars);
}

elseif($request-> isDelete())
{
	$results = array("resource" => "match", "method" => "delete", "request_vars" = $vars);
}

?>
