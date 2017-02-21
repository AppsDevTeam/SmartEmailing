<?php

namespace ADT;

/**
 * SmartEmailing API v2
 * http://docs.smartemailing.apiary.io/
 */
class SmartEmailing extends \Nette\Object
{

	const STATE_SUCCESS = "SUCCESS";

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
		$details = [];

		$details['emailaddress'] = $email;

		foreach ($properties as $key => $val) {
			$details[$key] = $val;
		}

		$details['customfields'] = $customfields;

		$contactlistsData = [];

		foreach ($contactlists as $id => $status) {
			$data = [];

			$data['id'] = $id;
			$data['status'] = $status;

			$contactlistsData[] = $data;
		}

		$details['contactliststatuses'] = $contactlistsData;

		$data = [
			'username' => $this->username,
			'usertoken' => $this->token,
			'requesttype' => 'Contacts',
			'requestmethod' => 'createupdate',
			'details' => $details,
		];

		$response = $this->callSmartemailingApiWithCurl($data);

		return $response;
	}


	/**
	 * get Smartemailing contact by email address
	 *
	 * @param  String $email
	 *
	 * @return \SimpleXMLElement
	 */
	public function getOneByEmail($email) {
		$data = [
			'username' => $this->username,
			'usertoken' => $this->token,
			'requesttype' => 'Contacts',
			'requestmethod' => 'getOne',
			'details' => [
				'emailaddress' => $email,
			],
		];

		$response = $this->callSmartemailingApiWithCurl($data);

		return $response;
	}


	/**
	 * get Smartemailing contact by ID
	 *
	 * @param  int $id
	 *
	 * @return [ty\SimpleXMLElement
	 */
	public function contactGetOneByID($id) {
		$data = [
			'username' => $this->username,
			'usertoken' => $this->token,
			'requesttype' => 'Contacts',
			'requestmethod' => 'getOne',
			'details' => [
				'id' => $id,
			],
		];

		$response = $this->callSmartemailingApiWithCurl($data);

		return $response;
	}


	/**
	 * delete Smartemailing contact by email address
	 *
	 * @param  String $email
	 *
	 * @return \SimpleXMLElement
	 */
	public function contactDeleteByEmail($email) {
		$data = [
			'username' => $this->username,
			'usertoken' => $this->token,
			'requesttype' => 'Contacts',
			'requestmethod' => 'delete',
			'details' => [
				'emailaddress' => $email,
			],
		];

		$response = $this->callSmartemailingApiWithCurl($data);

		return $response;
	}


	/**
	 * return all unsubscribed contacts from all lists of useraccount
	 *
	 * @return \SimpleXMLElement
	 */
	public function getAllUnsubscribedContacts() {
		$data = [
			'username' => $this->username,
			'usertoken' => $this->token,
			'requesttype' => 'Contacts',
			'requestmethod' => 'getAllUnsubscribed',
			'details' => [],
		];

		$response = $this->callSmartemailingApiWithCurl($data);

		return $response;
	}


	/**
	 * batch insertion of contacts
	 *
	 * @param  Array $contacts 	[pepa@seznam.cz' => ['name' => 'Pepa', 'surname' => 'Novak', 'lists' => [...]], novak@seznam.cz => ... ]
	 *
	 * 'lists' =>	['id' => 6676, 'status' => 'confirmed', 'added' => '2016-08-21 21:59:35']
	 *
	 * @return \SimpleXMLElement
	 */
	public function multipleContactsInsert($contacts) {
		$dateTime = new \DateTime();

		$contactsArray = [];

		foreach ($contacts as $email => $cData) {

			$contactData = [
				'emailaddress' => $email,
				'name' => $cData['name'],
				'surname' => $cData['surname'],
				'email' => $email,
				'contactliststatuses' => $cData['lists'],
			];

			$contactsArray[] = $contactData;
		}


		$data = [
			'username' => $this->username,
			'usertoken' => $this->token,
			'requesttype' => 'Contacts',
			'requestmethod' => 'createupdateBatch',
			'details' => $contactsArray,
		];

		$response = $this->callSmartemailingApiWithCurl($data);

		return $response;
	}


	/**
	 * convert array to xml
	 */
	protected function arrayToXml($array, &$xml) {
		foreach($array as $key => $value) {
			if (is_array($value)) {

				if (!is_numeric($key)) {
					$subnode = $xml->addChild("$key");
					$this->arrayToXml($value, $subnode);

				} else {
					$subnode = $xml->addChild('item');
					$this->arrayToXml($value, $subnode);
				}

			} else {
				$xml->addChild("$key", htmlspecialchars("$value"));
			}
		}
	}


	/**
	 * creating simple xml
	 */
	protected function createSimpleXml($array, $rootElementName) {
		$xml = new \SimpleXMLElement('<' . $rootElementName . '></' . $rootElementName . '>');

		$this->arrayToXml($array, $xml);

		return $xml->asXML();
	}


	protected function callSmartemailingApiWithCurl($data) {
		try {
			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, $this->url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_HEADER, FALSE);
			curl_setopt($ch, CURLOPT_POST, TRUE);

			$postFields = $this->createSimpleXml($data, 'xmlrequest');

			curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));

			$response = curl_exec($ch);

			curl_close($ch);

		} catch (\Exception $e) {
			$xml = new \SimpleXMLElement('<response></response>');
			$errorData = ['code' => $e->getCode(), 'message' => $e->getMessage()];

			$this->arrayToXml($errorData, $xml);
			
			return $xml;
		}


		return new \SimpleXMLElement($response);
	}


}
