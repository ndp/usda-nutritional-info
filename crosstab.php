<?php 
require_once("ndpsoftware.php");
require_once("usdalib.php");
ndp_session_info();

if (isset($_GET["style"])) $_SESSION["style"] = $_GET["style"];
if (isset($_GET["r"])) $_SESSION["r"] = $_GET["r"];
if (isset($_GET["c"])) $_SESSION["c"] = $_GET["c"];
if (isset($_GET["filter"])) $_SESSION["filter"] = $_GET["filter"];


// Make sure they have properly set everything up.
if (!isset($_SESSION["area"]) || strlen($_SESSION["area"]) == 0)
{
	ndp_return_to_sender();
	exit;
}
if (!isset($_SESSION["style"]) || strlen($_SESSION["style"]) == 0)
{
	ndp_return_to_sender();
	exit;
}

print "GOT HERE";
exit;
$debug = true;
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
 "http://www.w3.org/TR/1999/REC-html401-19991224/strict.dtd">
<html>
	<head>
		<title>CrossTab Report</title>
		<style>
		<?php
		printStyle($_SESSION["style"]); 
		?>
		</style>
	</head>

	<body>
<?php
exit();
	$result = connectArea($_SESSION["area"]);
	$inMainTable 	= $result["table"];
	$conn_data 		= $result["connection"];

	// Retrieve row(s) and column(s) data
	// Dimension is row levels + col levels
	// This will all go in the rawData[] array, by dimension.
	$dim = 2; // for now
	$dimFieldID[0] = $_SESSION["c"][0];
	$dimFieldID[1] = $_SESSION["r"][0];
	$_SESSION["c"][1] = $_SESSION["r"][0]; // for export to list views! FIX!
	$headers = array();
	
	$numRows = 0;
	for ($i = 0; $i < $dim; $i++)
	{
		
		$metaData = getMetaData($dimFieldID[$i]);

		// Remember what field this is...		
		if ($metaData["select_group"] == "")
			$selector[$i] = $metaData["select"];
		else
			$selector[$i] = $metaData["select_group"];		

		// Calculate the header
		$headers[$i] = $metaData["name"];
		if (strlen($metaData["description"]) > 0)
			print "<br>(".$metaData["description"].")";

		// Build the SQL statement
		$sql = "select ".$selector[$i];
		$sql .= " from ".$inMainTable." ".$metaData["from"];
		$sql .= " where ".$_SESSION["filter"];
		$sql .= " ".$metaData["where"];
		if ($debug) print $sql."<br />";
		$res = $conn_data->query($sql);
		$rawData[$i] = $res;
		if (PEAR::isError($rawData[$i])) 
			print $rawData[i]->getMessage();
	
		$numRows = $rawData[$i]->numRows() - 1;
	}
	
	if ($numRows == 0)
		exit;
		
	// Calculate headings
	// This running through the data, and building arrays of all values.
	// Calculate cell values
	// This involves running through the data and incrementing a value for each cell
	$dataColHeads = array();
	$cellCounts = array();
	for ($j = 1; $j <= $numRows; $j++)
	{
		$cellHash = "";
		for ($i = 0; $i < $dim; $i++)
		{
			if ($j == 1)
				$heads = array();
			else
				$heads = $dataColHeads[$i];
			$theRow = &$rawData[$i]->fetchRow(DB_FETCHMODE_DEFAULT, $j);
			$theData = &$theRow[0];
			if (isset($heads["$theData"]))
				$heads["$theData"]++;
			else
				$heads["$theData"] = 1;
			$cellHash .= $theData.";";
			$dataColHeads[$i] = $heads; // array of arrays of heading values
		}
		if (isset($cellCounts["$cellHash"]))
			$cellCounts["$cellHash"]++;
		else
			$cellCounts["$cellHash"] = 1;
	}
	

	// Draw table & headings
	print "<table class=\"crosstab\">";
	
	// Draw column headings
	$colHeads = $dataColHeads[0];
	ksort( $colHeads );
	
	?>
	<tr>
	<th class="topleft" rowspan="2">
	<?php print $headers[1]; ?>
	</th>
	<th colspan=
	<?php print "\"".(count($dataColHeads[0])+1)."\""; ?>
	>
	<?php print $headers[0]; ?>
	</th></tr>
	<tr>
	<?php
	while (list($ckey,$cvalue)=each($colHeads))
	{
		print "<th class=\"col\" width=\"20%\">";//<a href=\"";
		//print "list.php?c%5B%5D=food&c%5B%5D=kcals&c%5B%5D=fat&c%5B%5D=fiber&narrow=".urlencode(" AND $selector[0]=\"$ckey\"");
		//print "\">$ckey</a></th>";
		print "$ckey</th>";
		// by specifying a 20%, we encourage the browser to evenly space columns.
	}
	print "<th class=\"col\">Total</th>";
	print "</tr>";
	
	
	// Draw each of the rows
	$rowHeads = $dataColHeads[1];
	ksort( $rowHeads );
	reset( $rowHeads );
	$counterR = 1;
	while (list($rkey,$rvalue)=each($rowHeads))
	{
		print "<tr class=\"r".($counterR % 12)."\"><th class=\"row\">$rkey</th>";
		
		reset( $colHeads );
		$counterC = 0;
		while (list($ckey,$cvalue)=each($colHeads))
		{
			$cellHash = $ckey.";".$rkey.";";
			if (isset($cellCounts["$cellHash"]))
			{
				$cellValue = $cellCounts["$cellHash"];
				print "<td class=\"c".($counterC % 12)."\">$cellValue</td>";
			}
			else
				print '<td>&nbsp;</td>';
			$counterC++;
		}
		print "<td>$rvalue</td>"; // total
		print "</tr>";
		$counterR++;
	}
	
	print "<tr><th>Total</th>";
	reset( $colHeads );
	while (list($ckey,$cvalue)=each($colHeads))
	{
		print "<td>$cvalue</td>";
	}
	
	?>
		<td><?php print $numRows; ?></td>
	
	</table>

</body>
</html>
<?php
		$conn_meta->free;
		
?>