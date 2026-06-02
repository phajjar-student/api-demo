<?php
// Some common functions.  Do not browse this file directly.
if (!defined ('INSIDE_APPLICATION'))
	die ("Program cannot continue.");

// This file is created in the Dockerfile.  It contains the 64 character pepper.
require_once ("/var/lib/secrets/pepper.php");

define ('MAJOR_STRINGS_TABLE', 'sh_shared_strings');
define ('MAJOR_STRINGS_TABLE_DOES_NOT_EXIST', "The database table " . MAJOR_STRINGS_TABLE . " does not exist.  Browse to make-access-token.php to fix this.");
define ('HASHED_API_KEY_COLUMN', 'hashed_api_key');
define ('API_KEY_NOT_SET', "The API key was never set.  You need to set it before you can go any firther.  Browse to make-access-token.php to fix this.");
define ('SALT_COLUMN', 'salt');
define ('HASH_ALGO', 'sha512');
define ('SALT_SIZE', 64);
define ('KEY_LENGTH', 64);

define ('MAX_QUOTE_LENGTH', 500);

function TableExists(PDO $pdo, string $table): bool
{
    $stmt = $pdo->prepare("
        SELECT 1
        FROM sqlite_master
        WHERE type = 'table'
          AND name = :table
        LIMIT 1
    ");

    $stmt->execute([':table' => $table]);

    return (bool) $stmt->fetchColumn();
}

function DatabaseIsReady ($conn): string
{
	// When the container is loaded, none of the tables are created and the API key doesn't exist.
	// These need to be created before you can do anything with the API.
	// This returns a blank string if the database is ready and an error condition otherwise.
	// Normally, most APIs aren't this public with their information, but this will help you learn
	// how these work
	$outStr = "";

	if (!TableExists ($conn, MAJOR_STRINGS_TABLE))
		return (MAJOR_STRINGS_TABLE_DOES_NOT_EXIST);

	// Is the API Key set?
	try
		{
			$sql0 = $conn->prepare("select count(*) as 'recordCount' from " . MAJOR_STRINGS_TABLE . " where string_key =:string_key;");
			$sql0->execute([':string_key' => HASHED_API_KEY_COLUMN]);
			$recordCount = (int) $sql0->fetchColumn();
			if (0 == $recordCount)
				return (API_KEY_NOT_SET);
		}
	catch (Exception $ex)
		{
			return ("Couldn't determine if API Key was set due to the error [" . $ex->getMessage() . "]");
		}
	return ($outStr);
}

function GenerateRandomString ($length = SALT_SIZE, $alphaNumOnly = FALSE)
{
	// Generate random numbers between 33 and 126.  These are ASCII values for punctuation, numbers, capital letters and lowercase letters.
	$outStr = "";

	for ($x = 0; $x < $length; $x++)
	{
		// Pasting an API key with non-alphanumeric characters into scripts can cause problems with escaping.
		$randInt = -1;
		while (-1 == $randInt)
			{
				$randInt = rand (33, 126);
				if 
					( 
					$alphaNumOnly && 
						( 
							($randInt <= 48) || 
							($randInt >=58 && $randInt <= 64) || 
							($randInt >=91 && $randInt <= 96) ||
							($randInt >= 123)
						)
					)
					// Keep looping until we get a randint that is alphanum only.
					$randInt = -1; 
			} // while (-1 == $randInt)
		$outStr .= chr($randInt);
	}
	return $outStr;
}

function HashApiKeySaltandPepper ($inApiKey, $inSalt)
{
	// Using a single function here so I don't have to remember the logic for other files.
	$comboToBeHashed = $inApiKey . $inSalt . PEPPER; 
	$hashedCombo = hash(HASH_ALGO, $comboToBeHashed);
	// In PHP, the hash function by default returns hex digits, so no need to base64.
	return $hashedCombo;
}

?>
