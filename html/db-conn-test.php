<!DOCTYPE html>
<html lang='en'>
<head>
	<meta charset='UTF-8'>
	<meta name='viewport' content='width-device-width, initial-scale=1.0'>
	<title>Test Database Connection</title>
	<meta name='description' content='Makes sure you can connect to the SQLite database'>
</head>
<body>
<header>
	<h1>Database Connection Information</h1>
</header>
<main>
<p>You'd never put this in a directory that is browseable, like this one, in a publicly-accessible application.</p>
<?php
if (!defined ('INSIDE_APPLICATION'))
        define ('INSIDE_APPLICATION', true);

require_once ("/var/lib/secrets/sh-dbinfo.php");

print "<h2>sqlite Database is stored [" . DB_PATH ."]</h2>";

try
	{
		$dbPath = DB_PATH;
		$conn = new PDO("sqlite:$dbPath");
		// The line below makes errors manifest as exceptions.
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		print "<h3>Connected to the database successfully.</h3>";
		// Note that with PDO there is no need to explicitly close a connection.
	}
catch (Exception $ex)
	{
		print "<h3 class='error'>Connection failed due to error [" . $ex->getMessage() . "]</h3>";
	}
?>
</main>
</body>
</html>
