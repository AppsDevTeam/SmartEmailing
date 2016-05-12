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

	/**
	 * delete Smartemailing contact by email address
	 *
	 * @param  String $email
	 *
	 * @return \SimpleXMLElement
	 */
	public function contactDeleteByEmail($email) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_POST, TRUE);

		$postFields = '
			<xmlrequest>
			    <username>' . $this->username . '</username>
			    <usertoken>' . $this->token . '</usertoken>

			    <requesttype>Contacts</requesttype>
			    <requestmethod>delete</requestmethod>

			    <details>
			      <emailaddress>' . $email . '</emailaddress>
			    </details>
			</xmlrequest>
		';

		curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/xml"));

		$response = curl_exec($ch);

		curl_close($ch);

		return new \SimpleXMLElement($response);
	}


	/**
	 * return all unsubscribed contacts from all lists of useraccount
	 *
	 * @return \SimpleXMLElement
	 */
	public function getAllUnsubscribedContacts() {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_POST, TRUE);

		$postFields = '
			<xmlrequest>
			    <username>' . $this->username . '</username>
			    <usertoken>' . $this->token . '</usertoken>

			    <requesttype>Contacts</requesttype>
					<requestmethod>getAllUnsubscribed</requestmethod>

					<details></details>
			</xmlrequest>
		';

		curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/xml"));

		$response = curl_exec($ch);

		curl_close($ch);

		return new \SimpleXMLElement($response);
	}


	/**
	 * batch insertion of contacts
	 *
	 * @param  Array $contacts 	['firstName' => 'Pepa', 'familyName' => 'Novak', 'email' => 'pepa@seznam.cz']
	 * @param  Array $lists 		[6676 => 'confirmed']
	 *
	 * @return \SimpleXMLElement
	 */
	public function multipleContactsInsert($contacts, $lists) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_POST, TRUE);

		$dateTime = new \DateTime();

		$postFields = '
			<xmlrequest>
				<username>' . $this->username . '</username>
				<usertoken>' . $this->token . '</usertoken>

				<requesttype>Contacts</requesttype>
				<requestmethod>createupdateBatch</requestmethod>

				<details>
		';

		foreach ($contacts as $contact) {
			$postFields .= '
				<item>
					<emailaddress>' . $client['email'] . '</emailaddress>
					<name>' . $client['firstName'] . '</name>
					<surname>' . $client['familyName'] . '</surname>
					<email>' . $client['email'] . '</email>

					<contactliststatuses>
			';

			foreach ($lists as $id => $status) {
				$postFields .= '
					<item>
						<id>' . $id . '</id>
						<status>' . $status . '</status>
						<added>' . $dateTime->format('Y-M-d H:i:s') . '</added>
					</item>
				';
			}

			$postFields .= '
					</contactliststatuses>
				</item>
      ';
		}

		$postFields .= '
				</details>
			</xmlrequest>
		';

		curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/xml"));

		$response = curl_exec($ch);

		curl_close($ch);

		return new \SimpleXMLElement($response);
	}

}
