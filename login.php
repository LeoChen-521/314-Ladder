<?php
session_start();

if($_SERVER["REQUEST_METHOD"] == "POST") 
{
   // username and password sent from form 
   
   // $myusername = mysqli_real_escape_string($db,$_POST['username']);
   // $mypassword = mysqli_real_escape_string($db,$_POST['password']); 
   
   // $sql = "SELECT id FROM admin WHERE username = '$myusername' and passcode = '$mypassword'";
   // $result = mysqli_query($db,$sql);
   // $row = mysqli_fetch_array($result,MYSQLI_ASSOC);
   // $active = $row['active'];
   
   // $count = mysqli_num_rows($result);
   
   // If result matched $myusername and $mypassword, table row must be 1 row
	
   // if($count == 1) 
   // {
      // session_register("myusername");
      // $_SESSION['login_user'] = $myusername;
      
      //header("location: welcome.php");
   // }else {
   //    $error = "Your Login Name or Password is invalid";
   // }
}


include_once("../rest.php");

$request = new RestRequest();
$vars = $request->getRequestVariables();

$db = new PDO("pgsql:dbname=ladder_xchen13 host=localhost password=1846485 user=xchen13");
//$db = new PDO("pgsql:dbname=ladder host=localhost password=314dev user=dev");
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


if($request->isPost())
{
   header("location: welcome.php");
   // $has_error = false;
   // if(array_key_exists("username", $vars))
   // {
   //    if($vars["username"] != null)
   //    {
   //       $username = $vars["username"];
   //    }
   //    else
   //    {
   //       $results = array("error_text" => $no_username);
   //       $has_error = true;
   //       http_response_code(403);
   //       echo(json_encode($results));
   //       return;
   //    }
   // }
   // else
   // {
   //    $results = array("error_text" => $no_username_key);
   //    $has_error = true;
   //    http_response_code(403);
   //    echo(json_encode($results));
   //    return;
   // }

   // if(array_key_exists("password", $vars))
   // {
   //    if($vars["password"] != null)
   //    {
   //       $input_password = $vars["password"];
   //    }
   //    else
   //    {
   //       $results = array("error_text" => $no_password);
   //       $has_error = true;
   //       http_response_code(403);
   //       echo(json_encode($results));
   //       return;
   //    }
   // }
   // else
   // {
   //    $results = array("error_text" => $no_password_key);
   //    $has_error = true;
   //    http_response_code(403);
   //    echo(json_encode($results));
   //    return;
   // }

   // // if username is valid (exist), and password is provided. Then check the combination.
   // $password = find_password($db, $username);
   // // If matched, then establish a server side session.
   // if($password != $input_password)
   // {
   //    $results = array("error_text" => $wrong_password_or_username);
   //    $has_error = true;
   //    http_response_code(403);
   //    echo(json_encode($results));
   // }

   // // if inputs are all valid and matched. Start the session
   // if(!$has_error)
   // {
   //    try
   //    {
   //       //start session
   //       session_start();
   //       $_SESSION["username"] = $username;

         
         
         
   //    }
   //    catch(PDOException $e)
   //    {
   //       $results = array("error_text" => $e->getMessage());
   //       http_response_code(400);
   //    }
         
   // }
}






?>
<html>
   
   <head>
      <title>Login Page</title>
      
      <style type = "text/css">
         body {
            font-family:Arial, Helvetica, sans-serif;
            font-size:14px;
         }
         label {
            font-weight:bold;
            width:100px;
            font-size:14px;
         }
         .box {
            border:#666666 solid 1px;
         }
      </style>
      
   </head>
   
   <body bgcolor = "#FFFFFF">
	
      <div align = "center">
         <div style = "width:300px; border: solid 1px #333333; " align = "left">
            <div style = "background-color:#333333; color:#FFFFFF; padding:3px;"><b>Login</b></div>
				
            <div style = "margin:30px">
               
               <form action = "" method = "post">
                  <label>UserName  :</label><input type = "text" name = "username" class = "box"/><br /><br />
                  <label>Password  :</label><input type = "password" name = "password" class = "box" /><br/><br />
                  <input type = "submit" value = " Submit "/><br />
               </form>
               
               <div style = "font-size:11px; color:#FFFFFF; margin-top:10px"><?php echo "Hi, welcome to 314. This is an online game that players can challenge each other to compete rank. Any users who sign up and access to the system can play. "; ?></div>
					
            </div>
				
         </div>
			
      </div>

   </body>
</html>