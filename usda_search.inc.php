<?php

/* Copyright (c) 2002 by Andrew J. Peterson, ndp@mac.com  All Rights Reserved. */

define('SEARCH_STATE_UNKNOWN', 0);
define('SEARCH_STATE_START', 1);
define('SEARCH_STATE_NO_FOOD_FOUND', 2);
define('SEARCH_STATE_BROWSING_FOODS', 3);
define('SEARCH_STATE_FOUND_FOOD', 100);

class USDASearch
{
	function USDASearch()
	{
		$this->narrow_search_page = "";
		$this->food_info_page = "";
		$this->state = SEARCH_STATE_UNKNOWN;

		//$this->reset();
		$this->ndb_no = "";
		$this->group = "";
		$this->searchfor = "";
		$this->prefix = "";
		$this->where_clause = "";
		$this->commas = 0;
		$this->state = SEARCH_STATE_UNKNOWN;

		global $PHP_SELF;
		//$this->set_narrow_search_page($PHP_SELF);
		//$this->set_food_info_page($PHP_SELF);
		$this->narrow_search_page = $PHP_SELF;
		$this->food_info_page = $PHP_SELF;
	}
	
	function reset()
	{
		$this->ndb_no = "";
		$this->group = "";
		$this->searchfor = "";
		$this->prefix = "";
		$this->where_clause = "";
		$this->commas = 0;
		$this->state = SEARCH_STATE_UNKNOWN;
	}
	
	function get_state()
	{
		if ($this->state == SEARCH_STATE_UNKNOWN)
		{
			if ($this->group == "" && $this->searchfor == "" && $this->prefix == "")
				$this->state = SEARCH_STATE_START;
			else if (strlen($this->ndb_no))
				$this->state = SEARCH_STATE_FOUND_FOOD;
			else 
			{
				// They have entered some stuff, and the only way to tell the state is to actually 
				// search the database.
				$conn 	= mysql_connect('127.0.0.1', 'mysql', 'mysql');
				$db 	= mysql_select_db('usda', $conn);
				$this->calc_where_clause();
				$sql 	= "SELECT COUNT(*) FROM FOOD_DES ".$this->where_clause; 
				$result = mysql_query($sql, $conn);
				$row 	= mysql_fetch_row($result);
				$count  = $row[0];
				
				if ($count == 0)
					$this->state = SEARCH_STATE_NO_FOOD_FOUND;
				else if ($count == 1)
				{
					$this->state = SEARCH_STATE_FOUND_FOOD;
					$sql 	= "SELECT NDB_No FROM FOOD_DES ".$this->where_clause; 
					$result = mysql_query($sql, $conn);
					$row 	= mysql_fetch_row($result);
					$this->ndb_no = $row[0];
				}
				else
					$this->state = SEARCH_STATE_BROWSING_FOODS;
			}
		}
		return $this->state;
	}
	
	function get_ndb_no()
	{
		return $this->ndb_no;
	}
	
	function start_browse_food_group($inGroup)
	{
		$this->group = $inGroup;
		$this->calc_where_clause();
	}

	function start_query_search($inQuery)
	{
		$this->searchfor = $inQuery;
		$this->calc_where_clause();
	}

	function set_food_prefix($inPrefix)
	{
		$this->prefix = $inPrefix;
		$this->commas = count(split(",", $this->prefix));
		$this->calc_where_clause();
	}

	function set_narrow_search_page($inPage)
	{
		$this->narrow_search_page = $inPage;
	}

	function set_food_info_page($inPage)
	{
		$this->food_info_page = $inPage;
	}
	
	function calc_where_clause()
	{
		// Build a reusable "where" clause.
		$this->where_clause = "";
		if (strlen($this->group))
		{
			$this->where_clause = "WHERE FdGp_Cd=$this->group ";
		}
		if (strlen($this->prefix) > 0)
		{
			$this->where_clause .= strlen($this->where_clause) ? "AND " : "WHERE ";
			$this->where_clause .= "Description LIKE \"$this->prefix%\" "; 
		}
		if (strlen($this->searchfor) > 0)
		{
			$this->where_clause .= strlen($this->where_clause) ? "AND " : "WHERE ";
			$this->where_clause .= "Description LIKE \"%$this->searchfor%\"";
		}
	}

	function starting_page()
	{
		global $HTTP_GET_VARS;
		if (isset($HTTP_GET_VARS['prefix']))
			$this->set_food_prefix($HTTP_GET_VARS['prefix']);
		if (isset($HTTP_GET_VARS['fdgp_cd']))
			$this->start_browse_food_group($HTTP_GET_VARS['fdgp_cd']);
		if (isset($HTTP_GET_VARS['searchfor']))
			$this->start_query_search($HTTP_GET_VARS['searchfor']);
		if (isset($HTTP_GET_VARS['ndb_no']))
			$this->ndb_no = $HTTP_GET_VARS['ndb_no'];
		$this->state = SEARCH_STATE_UNKNOWN;
	}

	function get_new_search_html($inSeachForName="searchfor")
	{
		$html = "";
		$html .= '<form action="'.$this->narrow_search_page.'" method="get">';
		$html .= '<input type="hidden" name="reset" value="yes"></input>';
		$html .= 'New Search: ';
		$html .= '<input type="text" length="15" name="'.$inSeachForName.'" value="'.$this->searchfor.'"></input>';
		$html .= '<input type="submit" value="Go"></input>';
		$html .= '</form>';
		return $html;
	}
	
	function get_reset_html()
	{
		$html = "";
		$html .= '<form action="'.$this->narrow_search_page.'" method="get">';
		$html .= '<input type="hidden" name="reset" value="yes"></input>';
		$html .= '<input type="submit" value="Start Over"></input>';
		$html .= '</form>';
		return $html;
	}

	function get_search_html()
	{
		$html = "";
	
		//$html .= "<br>commas = $this->commas<br>";
		//$html .= "<br>searchfor = $this->searchfor<br>";
		//$html .= "<br>prefix = $this->prefix<br>";
		//$html .= "<br>group = $this->group<br>";
	
		//$this->commas++;
		$kMinRows = 3;
		$kMaxRows = 20;
		$kMaxCommas = 5;
		
		$conn 	= mysql_connect('127.0.0.1', 'mysql', 'mysql');
		$db 	= mysql_select_db('usda', $conn);	
	
		if (!strlen($this->where_clause))
		{
			$sql = "SELECT * FROM FD_GROUP ORDER BY FdGp_Desc"; 
			$result = mysql_query ($sql, $conn);
			$html .= '<h3>Please select a food group:</h3>'."\n";
			$html .= '<ul>'."\n";
			while ($row = mysql_fetch_row($result)) 
			{ 
				$html .= '  <li>'."\n";
				$html .= '    <a href="'.$this->narrow_search_page.'?fdgp_cd='.$row[0].'">';
				$html .= $row[1];
				$html .= "</a>\n";
				$html .= '  </li>'."\n";
			}
			$html .= '</ul>'."\n";
		}
		else
		{
			
			// First, see if we have narrowed it down to a reasonable number.
			$sql 	= "SELECT COUNT(*) FROM FOOD_DES $this->where_clause"; 
			$result = mysql_query ($sql, $conn);
			$row 	= mysql_fetch_row($result);
			$countAll = $row[0];
			if ($countAll < $kMaxRows)
			{
				$this->commas = 20; // Get the whole description
			}
			
			$done=0;
		
			while ($done==0)
			{
				// If we have iterated enough, simply list all the available items.
				if ($this->commas > $kMaxCommas)
				{
					$sql 	= "SELECT Description, Description, 1, ndb_no, 0 FROM FOOD_DES $this->where_clause ORDER BY Description"; 
					$result = mysql_query ($sql, $conn);
					$done=1;
				}
				else
				{
					$sql 	= "SELECT Description, SUBSTRING_INDEX(Description,\",\",$this->commas), COUNT(*), ndb_no, $this->commas FROM FOOD_DES $this->where_clause GROUP BY SUBSTRING_INDEX(Description,\",\",$this->commas) ORDER BY Description"; 
					$result = mysql_query ($sql, $conn);
					if (mysql_num_rows($result) < $kMinRows)
					{
						$this->commas++;
					}
					else
					{
						$done=1;
					}
				}
			}
			
			$html .= "<h3>Foods ";
			if (strlen($this->prefix)) $html .= " starting with \"".$this->prefix."\"";
			if (strlen($this->searchfor)) $html .= " containing \"".$this->searchfor."\"";
			if (strlen($this->group)) $html .= " within category \"".$this->group."\"";
			$html .= ":</h3>\n";
			
			$html .= "<ul>\n";
			while ($row = mysql_fetch_row($result)) 
			{
				$count = $row[2];
				
				 // If we have a unique name, show it all. Otherwise, show the unique part that
				 // groups several together.
				$uniqueName = $row[($count == 1 ? 0 : 1)];			
				
				// If everything in the list uses a this->prefix, don't bother repeating it.
				if (strlen($this->prefix) > 0)
				{
					$uniqueName = substr($uniqueName, strlen($this->prefix)+1, strlen($uniqueName));
				}
				
				// If the user has specified a search string, hilight it for her.
				if (strlen($this->searchfor))
				{
					if (preg_match('/^(.*)('.$this->searchfor.')(.*)$/i', $uniqueName, $subpatterns))
					{
						$uniqueName = $subpatterns[1]."<b>".$subpatterns[2]."</b>".$subpatterns[3];
					}
				}
				
				$html .= "<li>";
				$html .= "<a href=\"";
				// If there's only one food in the group, then go to the nutritional analysis.
				// Otherwise, drill down to the next set of ,s 
				// NOTE: Will this terminate?
				
				if ($count == 1)
				{
					$html .= $this->food_info_page."?ndb_no=$row[3]";
					$html .= "\">$uniqueName</a>"; // Give them the whole thing
				}
				else
				{
					$html .= $this->narrow_search_page."?";
					//if (strlen($this->group)) $html .= "fdgp_cd=$this->group&";
					$html .= "prefix=";
					$html .= rawurlencode($row[1]);
					//$html .= "&commas=$this->commas";
					//if (strlen($this->searchfor)) $html .= "&searchfor=".urlencode($this->searchfor);
					$html .= "\">$uniqueName <sup>$count</sup></a>"; // Give them what is left after the ","
				}
				$html .= "</li>\n";
			}
		}
		$html .= "</ul>";
		
		return $html;
	}
	
}

?>