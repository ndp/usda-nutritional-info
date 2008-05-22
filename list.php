<?php 
$debug = 0;

include_once("usdalib.php");

session_start();

// Make sure they have properly set everything up.
if (!isset($_SESSION["area"]) || strlen($_SESSION["area"]) == 0)
{
	print "area NOT SET\n";
	header("refresh: 2; pickarea.php");
	exit;
}

// make URL encoded parameters override globals
if (isset($_GET["style"]) && strlen($_GET["style"]))
{
	$_SESSION["style"] = $_GET["style"];
}
if (!isset($_SESSION["style"]) || strlen($_SESSION["style"]) == 0)
{
	print "STYLE NOT SET\n";
	header("refresh: 2; pickformat.php");
	exit;
}


$filter = "";
if (isset($_GET["filter"]) && strlen($_GET["filter"]))
	$filter = stripslashes($_GET["filter"]);

$narrow="";
if (isset($_GET["narrow"]))
{
	$_SESSION["narrow"] = stripslashes($_GET["narrow"]);
}

if ($debug)
{
	print "narrow=".$_SESSION["narrow"];
}



if (!isset($_SESSION["sSort"]) && isset($c))
{
	$_SESSION["sSort"] = $c;
}



//printHtmlHeadBody("Uscita");

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
 "http://www.w3.org/TR/1999/REC-html401-19991224/strict.dtd">
<html>
	<head>
		<title>List Report</title>
		<style>
		<?php
		printStyle($_SESSION["style"]); 
		?>
		</style>
	</head>

	<body>
<?php


?>
<table>
<?php


		// Table
		// First, the headers
		print("<tr>\n"); 
		$numCols = 0; 
		for ($i=0; isset($_GET["c"][$i]) && strlen($_GET["c"][$i]) > 0; $i++) 
		{ 
			$metaData = getMetaData($_GET["c"][$i]); 
			
			// First, output the heading 
			print "<th class=\"col\">".$metaData["name"]; 
			if (strlen($metaData["units"])>0) 
				print "<br>(".$metaData["units"].")"; 
			print "</th>"; 
			
			// Then, remember the phrases for retrieving the data 
			$fieldSelect[$i] = $metaData["select"]; 
			$fieldWhere[$i] = $metaData["where"]; 
			$fieldFrom[$i] = $metaData["from"]; 
			$numCols = $i+1; 
		} 
		print "</tr>\n"; 
		if ($debug) 
			print "connecting to area<br>"; 
		$result = connectArea($_SESSION["area"]); 
		$inMainTable = $result["table"]; 
		$conn_data = $result["connection"]; 
		//$debug = true;
		if ($debug) print "starting through columns ($numCols)<br>"; 
		for ($i = 0; $i < $numCols; $i++) 
		{ 
			$sql = 'select '.$fieldSelect[$i].' from '.$inMainTable;
			if (strlen($fieldFrom[$i]))
				$sql .= ','.$fieldFrom[$i];
			if (strlen($filter.$fieldWhere[$i]) > 0)
			{
				$sql .= ' where '.$filter;
				$sql .= ' '.$fieldWhere[$i]; 
			}
			if (isset($_SESSION["narrow"])) 
				$sql .= " ".$_SESSION["narrow"]; 
			if ($debug) print "SQL: ".$sql.";<br>"; 
			$results[$i] = $conn_data->query($sql); 
			if (PEAR::isError($results[$i])) 
			{ 
				print $results[$i]->getMessage(); 
			} 
		} 
		if ($debug) print "finished columns<br>"; 
		$numRows = $results[0]->numRows(); 
		
		// NOTE: Assuming there is one field! 
		if ($debug) print "starting through rows<br>"; 
		for ($ir = 0; $ir < $numRows; $ir++) 
		{ 
			print "<tr class=\"r".(($ir % 12) + 1)."\">	\n"; 
			for ($ic = 0; $ic < $numCols; $ic++ ) 
			{ 
				print "<td class=\"c".(($ic % 12) + 1)."\">\n"; 
				print " "; 
				$res = $results[$ic]; 
				if (!is_object($res) || (get_class($res) != "db_result" && is_subclass_of($res, "db_result"))) 
				{ 
					return new PEAR_ERROR("not a valid DB_result"); 
				} 
				
				if (is_array($row = $res->fetchRow(DB_FETCHMODE_DEFAULT, $ir)) ) 
				{ 
					print($row[0]); 
				} 
				print "</td>\n"; 
			} 
			print "</tr>\n"; 
		} 
		$r=0; 
		
		?> 
</table>
</body>
</html>
