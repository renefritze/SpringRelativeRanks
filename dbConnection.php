<?php

class dbConnection
{
	var $dbConfig;
	var $db_link = false;
	
	function __construct() 
	{
		$this->dbConfig = new DbConfig;
  		$this->connect( $this->dbConfig->host, $this->dbConfig->user, $this->dbConfig->pass, $this->dbConfig->dbname );
	}
		
	function __destruct() 
	{
       $this->closeDb();
	}
   
	function fix_for_mysql($value)
	{
		if (get_magic_quotes_gpc())
			$value = stripslashes($value);
		$value = mysql_real_escape_string($value);
		return $value;
	}

	function connect($host,$user,$pass,$dbname)
	{
		$this->db_link = @mysql_connect($host,$user,$pass) or die ("Could not connect to DB!");
		
		if ( $this->db_link == FALSE )
			die ("Could not connect to DB!");

		@mysql_select_db($dbname) or die ("Could not select DB: " . $dbname);
	}
	
	function closeDb()
	{
		if ( $db_link != false )
			mysql_close($this->db_link);
	}
	
	function query( $query )
	{
		$dbresult = mysql_query( $query, $this->db_link);
		return $dbresult;
	}
	
	////////////////////////////////////////
	//PUBLIC /////////////////////////////////////////////////
	
	function addConfidence( $sourceName, $destName, $confidence )
	{
		$this->deleteConfidence( $sourceName, $destName );

		$srcId = $this->addUser( $sourceName );
		if ( !srcId )
			die("Add user " . $sourceName . " failed!");
			
		$dstId = $this->addUser( $destName );
		if ( !$dstId )
			die("Add user " . $destName . " failed!");		
		
		$query = "insert into confidences (SourceId, TargetId, Confidence) VALUES  ('" . $srcId . "', '" . $dstId . "', '" . $confidence . "')";
		
		return $this->query( $query );
	}
	
	function getSkillsById( $userId )
	{
		$query = "select Skill, TargetId from skills where SourceId = " . $userId . "";
		return $this->query( $query );
	}
	
	function getConfidencesById( $userId )
	{
		$query = "select Confidence, TargetId from confidences where SourceId = " . $userId . "";
		return $this->query( $query );
	}
	
	function deleteConfidence( $sourceName, $destName )
	{
		$srcId = $this->addUser( $sourceName );
		if ( !srcId )
			die("Add user " . $sourceName . " failed!");
			
		$dstId = $this->addUser( $destName );
		if ( !$dstId )
			die("Add user " . $destName . " failed!");
		
		$query = "delete from confidences where SourceId = " . $srcId . " and TargetId = " . $dstId;
		return $this->query( $query );
	}
	
	function addSkill( $sourceName, $destName, $skill )
	{
		$this->deleteSkill( $sourceName, $destName );
	
		$srcId = $this->addUser( $sourceName );
		if ( !srcId )
			die("Add user " . $sourceName . " failed!");
			
		$dstId = $this->addUser( $destName );
		if ( !$dstId )
			die("Add user " . $destName . " failed!");
		
		$query = "insert into skills (SourceId, TargetId, Skill) VALUES  ('" . $srcId . "', '" . $dstId . "', '" . $skill . "')";
		return $this->query( $query );
	}
	
	function deleteSkill( $sourceName, $destName )
	{
		$srcId = $this->addUser( $sourceName );
		if ( !srcId )
			die("Add user " . $sourceName . " failed!");
			
		$dstId = $this->addUser( $destName );
		if ( !$dstId )
			die("Add user " . $destName . " failed!");
		
		$query = "delete from skills where SourceId = " . $srcId . " and TargetId = " . $dstId;
		return $this->query( $query );
	}
	
	/////////////////////////////////////////
	//PRIVATE ///////////////////////////////
	/////////////////////////////////////////
	function addUser( $name )
	{
		$id = $this->getUserIdByName( $name );
		if ( $id != -1 )
			return $id; //already existing

		$query = "insert into users (Username) VALUES ('" . $this->fix_for_mysql($name) . "')";
		if ( !$this->query( $query ) )
			return false;
			
		return $this->getUserIdByName( $name );
	}

	function getUserIdByName( $name )
	{
		$query = "select * from users where Username = '" . $this->fix_for_mysql($name) . "'";
		$result = $this->query( $query );
		
		if ( mysql_num_rows( $result) > 0 )
		{
			$user = mysql_fetch_assoc($result);
			return $user['Id'];
		}
		else
			return -1;
	}
}

?>