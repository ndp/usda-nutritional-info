<?php

require_once("usda_search.inc.php");
require_once("FoodInfo.php");
require_once("FoodReport.php");
require_once("RDI.php");

session_start();

if (isset($_SESSION["search_info"]))
{
	$search_info = &$_SESSION["search_info"];
	//phpinfo();
	if (isset($HTTP_GET_VARS['reset']))
	{
		$search_info->reset();
	}
}
else 
{
	$search_info = new USDASearch();
	$_SESSION["search_info"]=&$search_info;
	print 'creating new USDASearch';
}


//global $search_info;
//if (isset($search_info))
	//session_register("search_info");
//phpinfo();

$search_info->starting_page();

//session_start();

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
        "http://www.w3.org/TR/1999/REC-html401-19991224/loose.dtd">
<!-- 
	Copyright (c) 2001 by Andrew J. Peterson. All Rights Reserved.
	ndp@mac.com / ndpsoftware.com / 4104 24th St. #202 SF CA 94114
	Permission granted to IDEX to use with their website ONLY. 
	Distribution, publication, or any other usage is strictly prohibited, 
	without written permission from the author. 
-->
<html>
	<head>
		<title>
			Search for Food 
		</title>
		<meta http-equiv="Content-Type" content="text/html;">
		<style>
		html body {
			background-color: #ffc;
			border:0;
			margin: 0;
			padding: 0;
			font-family: verdana;
		}
		body>h1 {
			width: 100%;
			background-color: #dda;
			color: #060;
			margin: 0;
			padding: .5ex;
			letter-spacing: 1ex;
		}
		.newSearch {
			font-size: small;
			border-top: 1px solid #cc9;
			background-color: #fff;
			color: #555;
			margin: 0;
			padding:1ex 1ex 0 2ex;
			border-bottom: 1px solid #dda;
		}
		div.NF
		{
			border-color: black;
			border-width: 1pt 1pt 1pt 1pt;
			border-style: solid solid solid solid;
			padding: 3pt;
			width: 1.75inch;
			margin: 3pt 0 5pt 0; 
			background-color: #FFF;
		}
		
		.NF h1		
		{
			color: black;
			background-color: white;
			font-family: helvetica, sans-serif;
			font-size: 16pt;
			font-style: normal;
			font-weight: bolder;
			padding: 0; 
			margin: 0;

		}
		.NF h2	
		{
			color: black;
			font-family: helvetica, sans-serif;
			font-size: 9pt;
			font-style: normal;
			font-weight: normal;
			padding: 0; 
			margin: 0; 
		}
		.NF h6.NFamountPerServing
		{
			font-family: helvetica, sans-serif;
			font-size: 7pt;
			font-style: normal;
			font-weight: bolder;
			border-color: black;
			border-width: 4pt 0 0 0;
			border-style: solid none none none;
			padding: 2pt 0 0 0; 
			margin: 1pt 0 0 0; 
		}
		.NF table, .NF p
		{
			color: black;
			font-family: helvetica, sans-serif;
			font-size: 9pt;
			font-style: normal;
			font-weight: normal;
			padding: 2pt 0 0 0; 
			margin: 0pt 0 0 0; 
			border-color: black;
			border-width: 1pt 0 0 0;
			border-style: solid none none none;
		}
		 .NF table.NFCalories
		{
			border-color: black;
			border-width: 1pt 0 2pt 0;
			border-style: solid none solid none;
		}
		
		.NF table.NFSaturatedFat, .NF table.NFDietaryFiber, .NF table.NFSugars
		{
			text-indent: 10pt;
			text-align: left;
			font-weight: normal;
		}
		.NF table.NFProtein
		{
			border-color: black;
			border-width: 1pt 0 3pt 0;
			border-style: solid none solid none;
		}
		
		

		
		</style>
	</head>
	<body >
	<h1>USDA DATABASE</h1>

<table width="100%" class="newSearch">
	<tr>
		<td >
<?php print $search_info->get_new_search_html();?>
		</td>
		<td>&nbsp;</td>
		<td>
<?php print $search_info->get_reset_html();?>
		</td>
	</tr>
	</table>
<table border="0" cellpadding="8">
	<tr>
		<td colspan=3>
<?php 
		if ($search_info->get_state() == SEARCH_STATE_NO_FOOD_FOUND)
		{
			?><h3 style="color:red">No food found with that name.</h3><?php
			$search_info->reset();
		}

		if ($search_info->get_state() == SEARCH_STATE_FOUND_FOOD)
		{
			$report = new FoodReport($search_info->get_ndb_no() );
			//$report->get_ww_html();
			$report->get_nf_html();
		}
		else
		{
			print $search_info->get_search_html();
		}
?>
		</td>
		<td>&nbsp;</td>
	</tr>
</table>



</body>
</html>