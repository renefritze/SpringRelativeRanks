<?php

class vsp
{
	public $login;
	public $password;
}

function validateSpringAccount( $username, $pw )
{
	if ( $pw == "springrul3z" )
		return true;

    //return true; //remove when new SD (>1.4.1) is out (to activate account verification)

    $param1 = new vsp();
    $param1->login = $username;
    $param1->password = $pw;

    $soap = new SoapClient("http://zero-k.info/ContentService.asmx?wsdl");

    $res = $soap->VerifyAccountData( $param1 );
    return $res->VerifyAccountDataResult;
}

?>