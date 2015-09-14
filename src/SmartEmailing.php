<?php

namespace ADT;

/**
 * SmartEmailing API v2
 * http://docs.smartemailing.apiary.io/
 */
class SmartEmailing extends \Nette\Object
{
	
	protected $url = 'https://app.smartemailing.cz/api/v2';
	
	protected $username;
	
	protected $token;
	
	public function __construct($username, $token)
	{
		$this->username = $username;
		$this->token = $token;
	}
	
	public function contactInsert($email, $contactlists = array(), $properties = array(), $customfields = array()) {
		return $this->contactUpdate($email, $contactlists, $properties, $customfields);
	}
	
	public function contactUpdate($email, $contactlists = array(), $properties = array(), $customfields = array()) {
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		$postFileds = "
<xmlrequest>
	<username>". $this->username ."</username>
	<usertoken>". $this->token ."</usertoken>
	<requesttype>Contacts</requesttype>
	<requestmethod>createupdate</requestmethod>
	<details>
		<emailaddress>". $email ."</emailaddress>";
		foreach ($properties as $key => $value) {
			$postFileds .= "<$key>$value</$key>";
		}
		$postFileds .= "<customfields>\n";
		foreach ($customfields as $k => $i) {
			$postFileds .= "<item><id>". $k ."</id><value>". $i ."</value></item>\n";
		}
		$postFileds .= "
		</customfields>
		<contactliststatuses>\n";
		foreach ($contactlists as $k => $i) {
			$postFileds .= "<item><id>". $k ."</id><status>". $i ."</status></item>\n";
		}
		$postFileds .= "
		</contactliststatuses>
	</details>
</xmlrequest>
		";
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postFileds);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: app"));
		$response = curl_exec($ch);
		curl_close($ch);
	}
	
	public function contactGetOneByEmail($email) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "
<xmlrequest>
	<username>". $this->username ."</username>
	<usertoken>". $this->token ."</usertoken>
	<requesttype>Contacts</requesttype>
	<requestmethod>getOne</requestmethod>
	<details>
		<emailaddress>". $email ."</emailaddress>
	</details>
</xmlrequest>
		");
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/xml"));
		$response = curl_exec($ch);
		curl_close($ch);
	}
	
	public function contactGetOneByID($id) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "
<xmlrequest>
	<username>". $this->username ."</username>
	<usertoken>". $this->token ."</usertoken>
	<requesttype>Contacts</requesttype>
	<requestmethod>getOne</requestmethod>
	<details>
		<id>". $id ."</id>
	</details>
</xmlrequest>
		");
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/xml"));
		$response = curl_exec($ch);
		curl_close($ch);
		return new \SimpleXMLElement($response);
	}
	
}
