<?php

class rankAlgo
{
	var $dbConn;
	var $nodes = array();
	var $visitedIds = array();
	var $curNodeIdx = 0;
	var $debug = false;
	
	var $targetId;
	
	function __construct( $dbConnection, $debug ) 
	{
		$this->dbConn = $dbConnection;
		$this->debug = $debug;
	}

	function buildNode( $id, $dist )
	{
		return array( $id, $dist );
	}
	
	//use this function to generate a error return value (e.g. source or target user not found or no route found)
	function buildInvalidValue()
	{
		return array( -1, 1.0 );
	}
	
	function getRank($sourceName, $destName)
	{
		$srcId = $this->dbConn->getUserIdByName( $sourceName );
		if ( $srcId == -1 )
			return $this->buildInvalidValue(); //unknown source user
		
		$this->targetId = $this->dbConn->getUserIdByName( $destName );
		if ( $id == -1 )
			return $this->buildInvalidValue(); //unknown target user	
		
		$this->nodes[0] = $this->buildNode( $srcId, 1.0 );
		$this->curNodeIdx = 0;

		return $this->searchRank();
	}
	
	function findSkillRatingInUser( $userId )
	{
		if ( $this->debug ) echo "<br/>FindSkill - Source: " . $userId . " Target: " . $this->targetId;
		$result = $this->dbConn->getSkillsById( $userId );	//get all skill ratings for that user
		while ( $row = mysql_fetch_array($result) ) 
		{
			if ( $row['TargetId'] == (string)$this->targetId )
			{
				if ( $this->debug ) echo "<br/>skill found";
				return $row['Skill'];
			}
		}
		
		if ( $this->debug ) echo "<br/>No skill found";
		return -1;
	}
	
	function searchRank()
	{
		$curNode = $this->nodes[$this->curNodeIdx];
		$curNodeId = $curNode[0];
		
		if ( $this->debug ) echo "<br/><br/>Iteration step.  Current User ID: " . $curNodeId;
		
		if ( $this->curNodeIdx == -1 )
		{
			if ( $this->debug ) echo "<br/>End Of Net: " . $this->curNodeIdx;
			return $this->buildInvalidValue();
		}
		
		$skill = $this->findSkillRatingInUser( $curNodeId );
		
		if ( $skill != -1 )
			return array( $skill, $curNode[1] );

		$this->findChildNodes();
		
		$this->visitedIds[] = $curNodeId;
		unset( $this->nodes[$this->curNodeIdx] ); //delete current node. we could not find the info here
		
		$this->selectNextNode();
		
		return $this->searchRank();
	}
	
	function findChildNodes()
	{
		$curNode = $this->nodes[$this->curNodeIdx];
		$curNodeId = $curNode[0];
		
		//remove current node (safe its id
		$friends = $this->dbConn->getConfidencesById( $curNodeId );	//get friends for that user
		while ( $row = mysql_fetch_array($friends) ) 
		{
			$targetId = (int)$row['TargetId'];
			if ( in_array( $targetId, $this->visitedIds ) )
			{
				if ( $this->debug ) echo "<br/>Ignoring node " . $targetID . " already visited";
				continue;
			}
			
			if ( $targetId == $this->targetId )
				continue;
			
			$combinedConf = ((float)$curNode[1]) * (float)($row['Confidence']);
			$this->nodes[] = $this->buildNode( $targetId,  $combinedConf );
			
			if ( $this->debug ) echo "<br>Added Node: " . $targetId . " / " . $curNodeId . " / " . $combinedConf;
		}
	}
	
	function selectNextNode()
	{
		$maxConf = 0.0;
		$bestId = -1;
		
		foreach ( $this->nodes as $i => $node ) 
		{
			if ( $node[1] >= $maxConf )
			{
				$maxConf = $node[1];
				$bestId = $i;
			}
		}
		
		$this->curNodeIdx = $bestId;
		
		if ( $this->debug ) echo "<br/>Count: " . count($this->nodes) . " NextUser: " . $this->nodes[$this->curNodeIdx][0] . " Conf: " . $this->nodes[$this->curNodeIdx][1];
	}
}

?>