<!DOCTYPE html>
<html lang='en'>
<head>
	<meta charset='UTF-8'>
	<meta name='viewport' content='width-device-width, initial-scale=1.0'>
	<title>Make An Access Token</title>
	<meta name='description' content='Create a new access token'>
</head>
<body>
<header>
	<h1>Create a New Access Key</h1>
</header>
<main>
<p>You'd never put this in a directory that is browseable, like this one, in a publically-accessible application.</p>
<h2>Note that if you press "Create New Access Token" below, you will need to update your access token with this value in every API-enabled app which connects to this system.</h2>
<p>API Keys are not stored in readable format in the database.  You must copy this API key after it is created.</p>
<?php
// make-access-token.php
if (!defined ('INSIDE_APPLICATION'))
define ('INSIDE_APPLICATION', true);

require_once ("/var/lib/secrets/sh-dbinfo.php");
require_once ("sh-common-noauth.php");

$databaseStatus = "";
$requiredTableExists = TRUE;
try
{
	$dbPath = DB_PATH;
	$conn = new PDO("sqlite:$dbPath");
	// The line below makes errors manifest as exceptions.
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		// First, let's see if the "important strings" table exists.
		$databaseStatus = DatabaseIsReady ($conn);
		if (MAJOR_STRINGS_TABLE_DOES_NOT_EXIST == $databaseStatus)
			$requiredTableExists = FALSE;
		if (isset ($_POST["Submitting"]) && ("1" == $_POST["Submitting"]))
		{
			// Submitting...
			PrepareDatabase ($conn);
		}
		else
			PrintNonSubmitMsg($requiredTableExists);
		// Note that with PDO there is no need to explicitly close a connection.
	}
catch (Exception $ex)
	{
		print "<h3 class='error'>Connection failed due to error [" . $ex->getMessage() . "]</h3>";
	}

function PrepareDatabase ($conn)
{
	print "<p>Need to prepare database.</p>";
	$conn->exec("PRAGMA foreign_keys = ON");

	// There isn't a need to check if this table exists before we execute this
	// because the statement itself does the checking.
	$sql0 = 
		"CREATE TABLE IF NOT EXISTS " . MAJOR_STRINGS_TABLE . " (" .
			"rcdid INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, " .
			"string_key TEXT UNIQUE NOT NULL, " .
			"string_value TEXT NOT NULL DEFAULT '', " .
			"created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP " . // No native datetime data type.
			");";
	$conn->exec($sql0);

	// We don't need to confirm that a record exists before we delete it.
	$sql1 = $conn->prepare("delete from " . MAJOR_STRINGS_TABLE . " where string_key = :string_key;");
	$sql1->execute([':string_key' => HASHED_API_KEY_COLUMN]);

	// Is there a salt value?  If not, create one.
	$sql2 = $conn->prepare("select count(*) as 'recordCount' from " . MAJOR_STRINGS_TABLE . " where string_key = :string_key;");
	$sql2->execute([':string_key' => SALT_COLUMN]);
	$recordCount = (int) $sql2->fetchColumn();
	if (0 == $recordCount)
	{
		print "No salt column.<br/>";
		$proposedSalt = GenerateRandomString();
//		print "Proposed salt is [" . htmlspecialchars($proposedSalt) ."]<br/>";
		$sql3 = $conn->prepare("insert into " . MAJOR_STRINGS_TABLE . " (string_key, string_value) values (:string_key, :string_value);");
		$sql3->execute([":string_key" => SALT_COLUMN, ":string_value" => $proposedSalt]);
	}

	// Get the salt value from the database.
	$sql4 = $conn->prepare("select string_value from " . MAJOR_STRINGS_TABLE . " where string_key = :string_key;");
	$sql4->execute([":string_key" => SALT_COLUMN]);
	$actualSalt = $sql4->fetchColumn();
	//print "Salt to be used is [" . htmlspecialchars($actualSalt) . "]<br/>";

	// Propose an api key.
	$proposedAPIKey = GenerateRandomString($length=KEY_LENGTH, $alphaNumOnly=TRUE);
	$hashedCombo = HashApiKeySaltAndPepper($proposedAPIKey, $actualSalt);
	//print "Hashed value is [" . htmlspecialchars($hashedCombo) . "]<br/>";

	// Save the combo hash.
	$sql5 = $conn->prepare("insert into " . MAJOR_STRINGS_TABLE . " (string_key, string_value) values (:string_key, :string_value);");
	$sql5->execute([":string_key" => HASHED_API_KEY_COLUMN, ":string_value" => $hashedCombo]);

	print "<label for='apiKey'><h2>Below is your API key.  Copy it and store it in your code.  Once you navigate away from here you won't see it again. </h2></label>\n<pre id='apiKey' tabindex='0'>" . htmlspecialchars($proposedAPIKey) ."</pre>\n";
}

function PrintNonSubmitMsg ($requiredTableExists)
{

	if ($requiredTableExists)
	{
		// Do nothing.
	}
	else
	{
		print ("<p>The table " . MAJOR_STRINGS_TABLE . " doesn't exist.  When you create the new API key by clicking \"Create New Access Token\" it will be created.</p>"); 
	}
}


?>

<script>
	function Confirm ()
	{
		if (confirm ("Are you sure you want to do this?\n\t* This cannot be undone.\n\t* You will need to update all of your applications to use the new key.\n"))
		{
			document.forms[0].Submitting.value='1';
			return true;
		}
		return false;
	}
</script>
<form method='post' name='mainForm' action="<?php echo $_SERVER['PHP_SELF'];?>" onsubmit='return Confirm();'>
<input type='hidden' name='Submitting' value='0'>
<input type='submit' value='Create New Access Token' >
</form>
</main>
</body>
</html>
