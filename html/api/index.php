<?php
declare (strict_types=1);
if (!defined ('INSIDE_APPLICATION'))
        define ('INSIDE_APPLICATION', true);
require_once ("/var/lib/secrets/sh-dbinfo.php");
require_once ("../sh-common-noauth.php");

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri    = $_SERVER['REQUEST_URI'] ?? '/';
$path   = parse_url($uri, PHP_URL_PATH);

// Strip leading /api if desired
$path = preg_replace('#^/api#', '', $path);
$path = rtrim($path, '/') ?: '/';

// Headers (Apache + PHP-safe)
$headers = function_exists('getallheaders')
    ? getallheaders()
    : [];

// For the sake of simplicity, let's make the authortization header (but not its value!) case-insensitive:
$authHeader = "";
foreach ($headers as $header => $value)
{
	if ("authorization" == strtolower($header))
	{
		$authHeader = $value;
		break;
	}
}

// Raw request body (POST / PUT / PATCH / DELETE)
$rawBody = file_get_contents('php://input');

define ('ALLOWED_PATHS', 'VALIDATE|HELP|QUOTES');

// Open a connection to the database.  This is the only way we will talk to the database from outside of this container.
try
{
	$dbPath = DB_PATH;
	$conn = new PDO("sqlite:$dbPath");
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$databaseStatus = DatabaseIsReady ($conn);
	$reportingStatus = "";
	if ("" != $databaseStatus)
		$reportingStatus = $databaseStatus;
	// At this point, we are ready to switch actions based on the path and method.
	ProcessMethod ($conn, $method, $authHeader, $path, $rawBody, $reportingStatus);	
	//DeliverResponse (200, array("message" => "Hello, world!", "method" => $method, "API Key" => $authHeader, "Path" => $path, "Raw Body" => $rawBody, "Database Status" => $reportingStatus));
	// No need to close the connection.
}
catch (Exception $ex)
	{
		DeliverResponse (500, array("Process failed due to error [" . $ex->getMessage() . "]"));
	}

function Authorized ($conn, $inAuthHeader)
	{
		// Using a substring to get the key in order to prevent buffer overruns, just in case they're not caught earlier.
		$possibleKey = substr(str_replace ("Bearer ", "", $inAuthHeader), 0, KEY_LENGTH);
		//DeliverResponse (201, array("message" => "Found key[" . $possibleKey ."]"));
		
		// Get the Salt fron the database:
		$sql4 = $conn->prepare("select string_value from " . MAJOR_STRINGS_TABLE . " where string_key = :string_key;");
		$sql4->execute([":string_key" => SALT_COLUMN]);
		$actualSalt = $sql4->fetchColumn();
		//DeliverResponse (201, array("message" => "Found salt [" . $actualSalt . "]"));
		
		// Get the hashed combo, including the pepper.
		$hashedCombo = HashApiKeySaltAndPepper($possibleKey, $actualSalt);
		//DeliverResponse (201, array("message" => "Attempted Hashed Combo is [" . $hashedCombo . "]"));
		
		// Get the actual hashed combo, including the pepper, from the database.
		$sql5 = $conn->prepare("select string_value from " . MAJOR_STRINGS_TABLE . " where string_key = :string_key;");
		$sql5->execute([":string_key" => HASHED_API_KEY_COLUMN]);
		$actualHashedCombo = $sql5->fetchColumn();
		//DeliverResponse (201, array("message" => "Actual Hashed Combo is [" . $actualHashedCombo . "]"));

		if ($actualHashedCombo == $hashedCombo)
			return TRUE;
		return FALSE;
	}

function FilterPath ($inPath)
{
	// We want only the lowercase English letters of the path.  This helps to prevent any bad values from being inputted here.
	$outPath = "";
	for ($x = 0; $x < strlen ($inPath); $x++)
	{
		$ch = strtolower ($inPath[$x]);
		if ( (ord($ch) >= 97) && (ord($ch) <= 122) )
			$outPath .= $ch;
	}
	return $outPath;
}

function ProcessMethod ($conn, $method, $authHeader, $path, $rawBody, $reportingStatus)
{
	// If reportingStatus is anything other than blank, we cannot proceed.
	// Print out a message to that effect and return.
	if ("" == $reportingStatus)
	{
		// Make sure that we are properly authenticated.  If not, throe an exception that is caught by the code which calls this function.
		//DeliverResponse (201, array("message" => "Trace Statement 1", "method" => $method, "API Key" => $authHeader, "Path" => $path, "Raw Body" => "Trace Statement 1", "Database Status" => $reportingStatus));
		if (Authorized ($conn, $authHeader))
		{
			// Insert various API interfaces here.
			$filteredPath = FilterPath ($path);
			//DeliverResponse (201, array ("message" => "Filtered Path is [$filteredPath]"));
			// Are we using a valid Filtered Path?  This is a case-insensitive match.
			if (FALSE !== stristr("|" . ALLOWED_PATHS . "|", "|" . $filteredPath . "|"))
			{
				// Switch action based on the path specified.
				//DeliverResponse (201, array ("message" => "Allowed Path/Action is [$filteredPath]"));
				switch ($filteredPath)
				{
				case "help" :
					$response = new Help();
					$response->Go();
					break;
				case "quotes" :
					require "quotes.php";
					$response = new Quotes();
					$response->Go($conn, $method, MAX_QUOTE_LENGTH);
					break;
				}
			}
			else
				throw new Exception ("Invalid Path Specified");
		}
		else
			throw new Exception ("You are not authorized!  Go away!");
	}
	else
		DeliverResponse (503, array("message" => "No Database Configured.", "method" => $method, "API Key" => $authHeader, "Path" => $path, "Raw Body" => $rawBody, "Database Status" => $reportingStatus));
	return 0;
}

function DeliverResponse ($outStatus, $responseComponents)
	{
		$myResponse = new OutputJSON($outStatus, $responseComponents);
		$myResponse->OutJSON();
	}

class OutputJSON
{
	private $outStatus, $responseComponents;
	function __construct ($inOutStatus = null, $inResponseComponents = null)
	{
		if ( (!is_null ($inOutStatus)) && (!is_null($inResponseComponents)) )
			$this->InitIfNeeded ($inOutStatus, $inResponseComponents);
	}

	function IsInteger ($inString)
	{
		// So why use such an ancient and kludgy approach to data validation?
		// The reason is because functions like is_int expect an integer as
		// an argument, which is kinda useless.  The functions int and intval
		// are supposed to cast anything that isn't an integer into a 0 or 1,
		// but that can vary based on what the original data is.  I don't want
		// to take a chance with an unknown data type coming in, so I want to
		// limit the scope of input as much as possible.

		// So why not a regular expression?  I'm lazy.  I've used this code
		// for forever and it's bulletproof, so I am sticking with it.

		$strLength = strlen ($inString);
		$x = 0;
		while ($x < $strLength)
		{
			$ch = substr ($inString, $x, 1);
			if ( ( (0 == $x) && ('-' == $ch ) ) ||('0' == $ch) || ('1' == $ch) || ('2' == $ch) || ('3' == $ch) || ('4' == $ch) || ('5' == $ch) || ('6' == $ch) || ('7' == $ch) || ('8' == $ch) || ('9' == $ch) )
			{
				// Do nothing.
			}
			else
				return false;
			$x++;
		}
		return true;
	}

	function DeliverErrorMessageWithHTTPCode ($inMessage)
	{
		// For use with error messages that include an HTTP code, a bar, and a message.
		$code = 500;
		if (str_contains ($inMessage, "|"))
		{
			$messageParts = explode ("|", $inMessage);
			$this->InitIfNeeded ((int)$messageParts[0], array("message" =>
				"Request failed due to error [" . $messageParts[1] . "]"));
			$this->OutJSON();
		}

	}

	function InitIfNeeded ($inOutStatus, $inResponseComponents)
	{
		$this->outStatus = $inOutStatus;
		$this->responseComponents = $inResponseComponents;
	}

	function OutJSON ()
	{
		http_response_code($this->outStatus);
		if (!headers_sent())
		{
			header ('Content-Type: application/json; charset=utf-8');
		}
		print json_encode($this->responseComponents);
	}
}


class Help extends OutputJSON
{
	private $helpMessage = "";
	
	function Go () 
	{
		$allowedPathParts = explode ("|", ALLOWED_PATHS);
		$helpMessage = "Allowed actions are ";
		if (count($allowedPathParts) < 2)
			$helpMessage = "Allowed action is ";

		for ($x = 0; $x < count($allowedPathParts); $x++)
		{
			if (0 == $x)
				$helpMessage .= $allowedPathParts[$x];
			elseif (count($allowedPathParts)-1 == $x)
				$helpMessage .= ", or " . $allowedPathParts[$x];
			else
				$helpMessage .= ", " . $allowedPathParts[$x];
		}
		$this->InitIfNeeded (200, array ("message" => $helpMessage));
		$this->OutJSON();
	}


}

?>
