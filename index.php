<?php

include('accVerify.php');
include('dbConnection.php');
include('config.php');
include('rankAlgo.php');

ini_set('display_errors', 1);
set_time_limit(3600); //Timeout limit 1 hour (could be a big file or slow con)

///////////////////////////////////////////////////////
$debug = isset( $_GET['dbg'] );
$mode = $_GET['m'];
$sourceUsername = $_GET['su'];
$password = $_GET['pw'];
$targetUsername = $_GET['tu'];

if ( !isset( $mode ) )
	die("\nResult: ERROR\nMessage: No mode given!");

if ( isset( $_GET['s'] ) )
	$skill = floatval( $_GET['s'] );

if ( isset( $_GET['c'] ) )
	$confidence = floatval( $_GET['c'] );

if ( !isset( $sourceUsername ) or !isset( $password ) )
	die("\nResult: ERROR\nMessage: Missing argument");



////////////////////////////////////////////////////////////
	
if ( !validateSpringAccount( $sourceUsername, $password ) )
	die("\nResult: ERROR\nMessage: Invalid login");
	
$dbConnect = new dbConnection;


switch ( $mode ) 
{
case 0:
	if ( !isset( $targetUsername ) or !isset( $confidence ) )
		die("\nResult: ERROR\nMessage: Missing argument");
    
	$dbresult = $dbConnect->addConfidence( $sourceUsername, $targetUsername, $confidence );
	
	if ( !$dbresult )
		die("\nResult: ERROR\nMessage: Addin confidence failed!");
	else
		echo "\nResult: OK\nMessage: Confidence added: " . $sourceUsername . " - " . $targetUsername . " : " . $confidence;
		
    break;
case 1:
	if ( !isset( $targetUsername ) or !isset( $skill ) )
		die("\nResult: ERROR\nMessage: missing argument");
    
	$dbresult = $dbConnect->addSkill( $sourceUsername, $targetUsername, $skill );
	
	if ( !$dbresult )
		die("\nResult: ERROR\nMessage: Adding skill failed!");
	else
		echo "\nResult: OK\nMessage: Skill added: " . $sourceUsername . " - " . $targetUsername . " : " . $skill;
		
    break;
case 2:
	if ( !isset( $targetUsername ) )
		die("\nResult: OK\nMessage: missing argument");
		
	$rankAlgo = new rankAlgo( $dbConnect, $debug );
	
	list( $rank, $conf ) = $rankAlgo->getRank( $sourceUsername, $targetUsername );
	echo "\nResult: OK\nRank: " .  $rank . "\nConfidence: " . $conf;
    break;

default:
		die("\nResult: ERROR\nMessage: Unknown mode!");
		break;
}

?>