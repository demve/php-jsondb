<?php
	echo "<pre>";
	include_once("jsondb.php");
	$json=file_get_contents("example.jsondb");
	$jsondb=new JsonDB($json);
	echo $jsondb->getSQL();
	echo "</pre>";