function validateSignUpForm()
{
	// Get the name value.
	var signUpName = document.forms["example2"]["yourname"].value;
	var signUpEmail = document.forms["example2"]["youremail"].value;
	var signUpPhone = document.forms["example2"]["phonenumber"].value;
	var signUpUsername = document.forms["example2"]["yourusername"].value;
	var signUpPassword = document.forms["example2"]["yourpassword"].value;

	if (signUpName == "")
	{
		alert("Name must be filled out");
		return false;
	}

	if (signUpEmail == "")
	{
		alert("Email must be filled out");
		return false;
	}
	if (signUpPhone == "")
	{
		alert("Phone number must be filled out");
		return false;
	}
	if (signUpUsername == "")
	{
		alert("Username must be filled out");
		return false;
	}
	if (signUpPassword == "")
	{
		alert("Password must be filled out");
		return false;
	}
}

function validateChallengeForm()
{
	// Get the name value.
	var challenger = document.forms["example3"]["challenger"].value;
	var challengee = document.forms["example3"]["challengee"].value;
	var scheduled = document.forms["example3"]["scheduled"].value;

	if (challenger == "")
	{
		alert("challenger must be filled out");
		return false;
	}

	if (challengee == "")
	{
		alert("challengee must be filled out");
		return false;
	}
	if (scheduled == "")
	{
		alert("scheduled number must be filled out");
		return false;
	}
}


function validateMatchForm()
{
	// Get the name value.
	var winner = document.forms["example4"]["winner"].value;
	var loser = document.forms["example4"]["loser"].value;
	var winnerScore = document.forms["example4"]["winnerScore"].value;
	var loseScore = document.forms["example4"]["loseScore"].value;
	var played = document.forms["example4"]["time"].value;

	if (winner == "")
	{
		alert("winner must be filled out");
		return false;
	}

	if (loser == "")
	{
		alert("loser must be filled out");
		return false;
	}
	if (winnerScore == "")
	{
		alert("winnerScore number must be filled out");
		return false;
	}
	if (loseScore == "")
	{
		alert("loseScore number must be filled out");
		return false;
	}
	if (played == "")
	{
		alert("played number must be filled out");
		return false;
	}
}





