<?php
	/** 
	 * A class that interfaces with the crontab. (cjpa@audiophile.com)
	 *
	 * This class lets you manipulate the crontab. It lets you add delete update entries easily.
	 **/

define(CRON_COMMENT, 0);
define(CRON_ASSIGN, 1);
define(CRON_CMD, 2);
define(CRON_SPECIAL, 3);
define(CRON_EMPTY, 4);

	 class Crontab
	 {
	 	/* 
		   $crontabs: Array that holds all the different lines. Lines are associative arrays with the following fields: 
			"minute" : holds the minutes (0-59)
			"hour"	: holds the hour (0-23)
			"dayofmonth": holds the day of the month (1-31)
			"month" : the month (1-12 or the names)
			"dayofweek" : 0-7 (or the names)

			or a line can be a 2-value array that represents an assignment: "name" => "value"
			or a line can be a comment (string beginning with #)
			or it can be a special command (beginning with an @)
		*/	
		var $crontabs; 
		var $user; // the user for whom the crontab will be manipulated
	 	var $linetypes; // Lists the type of line of each line in $crontabs. can be: any of the CRON_* constants. so $linetype[5] is the type of $crontabs[5].

	 
		/** Methods */
		
		// The constructor. Initialises $this->crontabs
		function Crontab($user)
		{
			$this->user = $user;
			$this->readCrontab();
			
		}

		// This reads the crontab of $this->user and parses it in $this->crontabs
		function readCrontab()
		{
			exec("crontab -u $this->user -l", $crons, $return);

			foreach ($crons as $line)
			{
				$line = trim($line); // discarding all prepending spaces and tabs
				
				// empty lines..
				if (!$line)
				{
					$this->crontabs[] = "empty line";
					$this->linetypes[] = CRON_EMPTY;
					continue;
				}

				// checking if this is a comment
				if ($line[0] == "#")
				{
					$this->crontabs[] = trim($line);
					$this->linetypes[] = CRON_COMMENT;
					continue;
				}
				
				// Checking if this is an assignment 
				if (ereg("(.*)=(.*)", $line, $assign))
				{
					$this->crontabs[] = array("name" =>$assign[1], "value" =>$assign[2]);
					$this->linetypes[] = CRON_ASSIGN;
					continue;
				}	
				
				// Checking if this is a special @-entry. check man 5 crontab for more info
				if ($line[0] == '@')
				{
					$this->crontabs[] = split("[ \t]", $line, 2);
					$this->linetypes[] = CRON_SPECIAL;
					continue;
				}
				
				// It's a regular crontab-entry
				$ct = split("[ \t]", $line, 6);
				$this->addCron($ct[0], $ct[1], $ct[2], $ct[3], $ct[4], $ct[5], $ct[6]);
			}
		}

		// Writes the current crontab
		function writeCrontab()
		{
			global $DEBUG, $PATH;
			
			$filename = ($DEBUG ? tempnam("$PATH/crons", "cron") : tempnam("/tmp", "cron"));
			$file = fopen($filename, "w");

			for ($i = 0; $i < count($this->linetypes); $i++)
			{
				switch ($this->linetypes[$i])
				{
					case CRON_COMMENT : 
						$line = $this->crontabs[$i];
						break;
					case CRON_ASSIGN:
						$line = $this->crontabs[$i][name] ." = ".$this->crontabs[$i][value];
						break;
					case CRON_CMD:
						$line = implode(" ", $this->crontabs[$i]);
						break;
					case CRON_SPECIAL:
						$line = implode(" ", $this->crontabs[$i]);
						break;
					CASE CRON_EMPTYLINE:
						$line = "\n"; // an empty line in the crontab-file
						break;
					default: 
						unset($line); 
						echo "Something very weird is going on. This line ($i) has an unknown type.\n";
						break;
				}

				// echo "line $i : $line\n";
				
				if ($line)
					fwrite($file, $line."\n");
			}
			fclose($file);

			if ($DEBUG)
				echo "DEBUGMODE: not updating crontab. writing to $filename instead.\n";
			else
			{
				exec("crontab -u $this->user $filename", $returnar, $return);
				if ($return != 0)
					echo "Error running crontab ($return). $filename not deleted\n";
				else
					unlink($filename);	
			}
		}
	
	
		// Add a item of type CRON_CMD to the end of $this->crontabs
		function addCron($m, $h, $dom, $mo, $dow, $cmd)
		{
			$this->crontabs[] = array("minute" => $m, "hour" => $h, "dayofmonth" => $dom, "month" => $mo, "dayofweek" => $dow, "command" => $cmd);
			$this->linetypes[] = CRON_CMD;
		}


		// Add a comment to the cron to the end of $this->crontabs
		function addComment($comment)
		{
			$this->crontabs[] = "# $comment\n";
			$this->linetypes[] = CRON_COMMENT;
		}

		
		// Add a special command (check man 5 crontab for more information)
		function addSpecial($sdate, $cmd)
		{
			$this->crontabs[] = array("special" => $sdate, "command" => $cmd);
			$this->linetypes[] = CRON_SPECIAL;
		}
		
		
		// Add an assignment (name = value)
		function addAssign($name, $value)
		{
			$this->crontabs[] = array("name" => $name, "value" => $value);
			$this->linetypes[] = CRON_ASSIGN;
		}
		
		
		// Delete a line from the arrays. 
		function delEntry($index)
		{
			unset($this->crontabs[$index]);
			unset($this->linetypes[$index]);
		}


		// Get all the lines of a certain type in an array
		function getByType($type)
		{
			if ($type < CRON_COMMENT || $type > CRON_EMPTY)
			{
				trigger_error("Wrong type: $type", E_USER_WARNING);
				return 0;
			}

			$returnar = array();
			for ($i=0; $i < count($this->linetypes); $i++)
				if ($this->linetypes[$i] == $type)
					$returnar[] = $this->crontabs[$i];
			
			return $returnar;
		}
	 }
?>
