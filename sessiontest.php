<?php

# Include this code somewhere if you're having trouble with $_SESSION not
# seeming to work as advertised.
class MyClass
{
	var $count=0;
	
	function inc()
	{
		return $this->count++;
	}
}


//session_start();

function dump_session_vars()
{
	print "<br><br>";
	foreach(array_keys($_SESSION) as $k) 
	{
		print "<br>_SESSION[$k]: $_SESSION[$k]<br>";
		if (is_object($_SESSION[$k]))
		{
			var_dump($_SESSION[$k]);
		}
	}

}

if (isset($_SESSION["count"]))
	$_SESSION["count"]++;
else
	$_SESSION["count"] = 0;

print "Session #".$_SESSION["count"]."<br>";

dump_session_vars();

if (isset($HTTP_SESSION_VARS["tt"]))
{
  $z=&$HTTP_SESSION_VARS["tt"];
}
else {
  $z=new MyClass();
  session_register("tt");
  $HTTP_SESSION_VARS["tt"]=&$z;
}
//print $z->inc();

dump_session_vars();
return;


/*
function Work_around_session_bogosity() {
	foreach(array_keys($GLOBALS['HTTP_SESSION_VARS']) as $k) 
	{
		print "<br>session_unregister: ";
		if (!isset($_SESSION[$k])) 
		{
			session_unregister($k);
			print $k." ";
		}
	}
	
	foreach($_SESSION as $k=>$v) 
	{
		print "<br>session_register: ";
		if (!session_is_registered($k)) 
		{
			session_register($k);
			print $k." ";
		}
		$GLOBALS['HTTP_SESSION_VARS'][$k] = $v;
		$GLOBALS[$k] = $v;
	}
}

//register_shutdown_function('Work_around_session_bogosity');

Work_around_session_bogosity();             



#############################################
# REFERENCE JUGGLING WITH HTTP_SESSION_VARS 
#   needed for session vars when            
#   register_globals is off                 
#############################################
function & sess_reg($x){
global $HTTP_SESSION_VARS;
   session_register( & $HTTP_SESSION_VARS[$x]);
   return $HTTP_SESSION_VARS[$x];
}  

session_start();
$foo = & sess_reg("foo");

//main program
echo $foo++;

var_dump($HTTP_SESSION_VARS);
*/
/*
class MyClass
{
	var $count=0;
	
	function inc()
	{
		return $this->count++;
	}
	
}

function session_regglobals_workaround() // by Hans Spath
{
	global $HTTP_SESSION_VARS;

	if ( ini_get('register_globals') )
		foreach ( $HTTP_SESSION_VARS as $varname=>$value )
			if ( session_is_registered($varname) )
				$GLOBALS[$varname] = $value;
}

session_start();

session_regglobals_workaround();

if (isset($HTTP_SESSION_VARS["z"]))
{
  $z=&$HTTP_SESSION_VARS["z"];
}
else {
  $z=new MyClass();
  session_register("z");
  $HTTP_SESSION_VARS["z"]=&$z;
}
print $z->inc();
*/
?>