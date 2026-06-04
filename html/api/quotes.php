<?php

class Quotes extends OutputJSON
{
	private $helpMessage = "";
	private $conn = "";
	private $method = "";
	private $maxQuoteLength = 0;

	function CreateTableIfNeeded()
	{
		// There is nothing special about this table, just a PK, quote text, author and year.
		$sql0 = 
			"create table if not exists tbl_quotes (" .
				"rcdid integer primary key autoincrement not null, " .
				"quote_text text not null, " .
				"quote_author text not null, " .
				"quote_year int null, " .
				"created_at text not null default current_timestamp);";
		$this->conn->exec($sql0);
	}

	function Go ($inConn, $inMethod, $inMaxQuoteLength)
	{
		$this->maxQuoteLength = $inMaxQuoteLength;
		$this->method = $inMethod;
		//$this->InitIfNeeded (200, array("message" => "Hello from Quotes. Method was [" . $this->method . "]"));
		//$this->OutJSON();
		$this->conn = $inConn;
		$this->CreateTableIfNeeded();

		switch ($this->method)
		{
			case "POST" :
				try
				{
					$this->InsertQuote();
				}
				catch (Exception $ex)
				{
					$this->DeliverErrorMessageWithHTTPCode ($ex->getMessage());
				}
				break;
			case "GET" :
				try
				{
					$this->GetQuote();
				}
				catch (Exception $ex)
				{
					$this->DeliverErrorMessageWithHTTPCode ($ex->getMessage());
				}
				break;
			default:
				$this->InitIfNeeded (405, array("message" => "Method [" . $this->method . "] is not supported in Quotes."));
				$this->OutJSON();
				break;
		}
	}

	function InsertQuote ()
	{
		$quoteText = "";
		if (isset ($_POST['quote_text']))
			$quoteText = trim($_POST['quote_text']);
		$quoteAuthor = "";
		if (isset ($_POST['quote_author']))
			$quoteAuthor = trim($_POST['quote_author']);
		$quoteYear = null;
		if (isset ($_POST['quote_year']))
		{
			if (!is_int (trim($_POST['quote_year'])))
				throw new Exception ("400|If a year is specified it must be a positive integer.");
			$possibleYear = (int) trim($_POST['quote_year']);
			if ($possibleYear <= 0)
				throw new Exception ("400|Year specified wasn't a positive integer.");
			$quoteYear = $possibleYear;
		}

		if ("" == $quoteText)
			throw new Exception ("400|Quote text must be specified.");
		if (strlen ($quoteText) > $this->maxQuoteLength)
			throw new Exception ("431|Quote is too long.");
		if ("" == $quoteAuthor)
			throw new Exception ("400|Quote author must be specified.");

		$yearInfo = "  No year was specified.";
		if (!is_null ($quoteYear))
			$yearInfo = "  Quote year is [" . $quoteYear . "]";

		try
		{
			// Is the quote already in the database?  If yes throw an exception.
			$sql2 = $this->conn->prepare("select count(*) as 'recordCount' from " .
				"tbl_quotes where lower(quote_text)=lower(:possible_quote_text);");
			$sql2->execute([":possible_quote_text" => $quoteText]);
			$recordCount = (int) $sql2->fetchColumn();
			if (0 == $recordCount)
			{
				$sql1 = $this->conn->prepare("insert into tbl_quotes (quote_text, quote_author, quote_year) values (:quote_text, :quote_author, :quote_year);");
				$sql1->execute([":quote_text" => $quoteText, ":quote_author" => $quoteAuthor, ":quote_year" => $quoteYear]);

				$insertID = $this->conn->lastInsertId();
				$this->InitIfNeeded (200, array ("message" => "Success", "id", $insertID));
				$this->OutJSON();
			}
			else
				throw new Exception ("Quote is already in the database.");
		}
		catch (Exception $ex)
		{
			throw new Exception ("500|Failed due to error [" . $ex->getMessage() . "]");
		}

	}

	function GetQuote ()
	{
		// If no id is specified, just grab a random quote from the table.
		$id = -1;
		if (isset ($_GET["id"]))
		{
			
			// Is this a non-negative integer?
			if (!$this->IsInteger($_GET["id"]))
				throw new Exception ("400|If an id is specified it must be a " .
					"non-negative integer.");
			$possibleId = (int) $_GET["id"];
			if ($possibleId < 1)
				throw new Exception ("400|An integer id was specified but it " .
					"was non-negative.");
			$id = $possibleId;
			
		}
		$fetchedQuote = "";
		$fetchedAuthor = "";
		$fetchedID = -1;
		if (-1 == $id)
		{
			try
				{
					$sql0 = $this->conn->query("select * from tbl_quotes order by " .
						"random() limit 1;");
					// You can't get the rowcount before retrieving the data in SQLite/PDO.
					//$rowCount = $sql0->fetchColumn();
					//if ($rowCount < 1)
					//	throw new Exception ("500|There are no quotes in the database.");
					$row0 = $sql0->fetch();
					$fetchedQuote = $row0["quote_text"];
					$fetchedAuthor = $row0["quote_author"];
					$fetchedID = $row0["rcdid"];
					$this->InitIfNeeded (200, array ("message" => "Success", 
						"id" => $fetchedID, "text" => $fetchedQuote, 
						"author" => $fetchedAuthor));
					$this->OutJSON();
				}
			catch (Exception $ex)
				{
					throw new Exception ("500|Random GET failed due to error [" . $ex->getMessage() . "]");
				}
		}
		else
		{
			try
			{
				// SQLite will return a false value for the query object if the index doesn't
				// exist.  For now, I will count the number of records with the matching index
				// to see if its here.  Normally I wouldn't need to do this, but I can't do a
				// rowcount on a valid object without nulling out the retrieved data.

				$sql2 = $this->conn->prepare("select count(*) as 'recordCount' from tbl_quotes where rcdid=:inRcdid");
				$sql2->execute([":inRcdid" => $id]);
				$row2 = $sql2->fetch();
				$recordCount = (int)$row2["recordCount"];
				if (0 == $recordCount)
					throw new Exception ("No quote with that id exists.");

				$sql1 = $this->conn->prepare("select * from tbl_quotes where rcdid=:inRcdid");
				$sql1->execute([":inRcdid" => $id]);
				$row1 = $sql1->fetch();
				$fetchedQuote = $row1["quote_text"];
				$fetchedAuthor = $row1["quote_author"];
				$fetchedID = $id;
				$this->InitIfNeeded (200, array ("message" => "Success",
					"id" => $fetchedID, "text" => $fetchedQuote,
					"author" => $fetchedAuthor));
				$this->OutJSON();
			}
			catch (Exception $ex)
			{
				throw new Exception ("500|Specific index GET failed due to error [" . $ex->getMessage() . "]");
			}
		}
	}
}

?>
