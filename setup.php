<?php
include_once("usdalib.php");
session_start();

	// First step is to define the area


	if (!isset($_SESSION["area"]))
	{
		if (!isset($_GET["area"]))
		{
			$is_valid = true;
			
			$errors["area_id"] = "";
			
			if (isset(_GET["area_id"]))
			{
				$id = $_GET["area_id"];
				if (!ereg("[a-z][A-Z]+", $id)) $is_valid = false;
				// see if it already exists
				if ($is_valid && 
			}
			

			$_SESSION["area"] = xxx;	
			// Output a form for defining the area.
			// Fields needed are:
			// id name database table
			?>
<h1>Define a new area</h1>
<form action="setup.php" method="get">

<table border="1" cellspacing="3" cellpadding="10" bgcolor="#666666">
	<tr>
		<td>Name</td>
		<td><input type="text" name="area_name" size="20" maxlength="20"></td>
		<td>The name the user sees. Include spaces and punctuation as appropriate.</td>
	</tr>
	<tr>
		<td>ID</td>
		<td><input type="text" name="area_id" size="20" maxlength="20"></td>
		<td>This is a short code (invisible to users) to identify this database area.</td>
	</tr>
	<tr>
		<td>Database</td>
		<td><input type="text" name="area_database" size="20" maxlength="20"></td>
		<td>Exact name of the database.</td>
	</tr>
	<tr>
		<td>Primary Table</td>
		<td><input type="text" name="area_table" size="20" maxlength="20"></td>
		<td>Primary table which contains one row/element.</td>
	</tr>
</table>			
		<input type="submit">
</form>	
			
			
			<?php
		}
		else
		{
			$is_valid = true;
			
			$id = $_GET["area_id"];
			if (!ereg("[a-z][A-Z]+", $id)) $is_valid = false;
			if ($is_valid && 
			

			$_SESSION["area"] = xxx;	
		}
	
	
	}
?>