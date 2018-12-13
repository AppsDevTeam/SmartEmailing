<?php

namespace ADT;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Nette\Utils\Json;

/**
 * SmartEmailing API v3
 * http://docs.smartemailing.apiary.io/
 */
class SmartEmailing
{
	const NODE_PING = 'ping';
	const NODE_CHECK_CREDENTIALS = 'check-credentials';
	const NODE_CONTACTLISTS = 'contactlists';
	const NODE_CUSTOMFIELDS = 'customfields';
	const NODE_CUSTOMFIELDS_OPTIONS = 'customfield-options';
	const NODE_CONTACT_CUSTOMFIELDS = 'contact-customfields';
	const NODE_CHANGE_EMAILADDRESS = 'change-emailaddress';
	const NODE_CONTACT_FORGET = 'contacts/forget';
	const NODE_CONTACTS = 'contacts';
	const NODE_IMPORT = 'import';
	const NODE_PURPOSES = 'purposes';
	const NODE_PURPOSE_CONNECTIONS = 'purpose-connections';

	const METHOD_GET = 'GET';
	const METHOD_POST = 'POST';
	const METHOD_PUT = 'PUT';
	const METHOD_DELETE = 'DELETE';

	protected $url = 'https://app.smartemailing.cz/api/v3';

	protected $username;

	protected $token;

	/**
	 * SmartEmailing constructor.
	 *
	 * @param string $username
	 * @param string $token
	 */
	public function __construct($username, $token)
	{
		$this->username = $username;
		$this->token = $token;
	}


	/**
	 * Aliveness test
	 *
	 * @return array
	 * @throws SmartEmailingException
	 */
	public function ping()
	{
		return $this->call(self::METHOD_GET, self::NODE_PING);
	}


	/**
	 * Login test
	 *
	 * @return array
	 * @throws SmartEmailingException
	 */
	public function checkCredentials()
	{
		return $this->call(self::METHOD_GET, self::NODE_CHECK_CREDENTIALS);
	}


	/**
	 * @param string $name
	 * @param string $senderName
	 * @param string $senderEmail email
	 * @param string $replyTo email
	 * @param string|null $publicName
	 * @throws SmartEmailingException
	 */
	public function createContactlist($name, $senderName, $senderEmail, $replyTo, $publicName = NULL)
	{
		$data = [
			'name' => $name,
			'sendername' => $senderName,
			'senderemail' => $senderEmail,
			'replyto' => $replyTo,
		];
		if (is_string($publicName)) {
			$data['publicname'] = $publicName;
		}
		return $this->call(self::METHOD_POST, self::NODE_CONTACTLISTS, $data);
	}


	/**
	 * @return array
	 * @throws SmartEmailingException
	 */
	public function getContactlists()
	{
		return $this->call(self::METHOD_GET, self::NODE_CONTACTLISTS);
	}


	/**
	 * @param $id
	 * @return array
	 * @throws SmartEmailingException
	 */
	public function getContactlist($id)
	{
		return $this->call(self::METHOD_GET, self::NODE_CONTACTLISTS . "/" . $id);
	}

	/**
	 * @param $id
	 * @param string|null $name
	 * @param string|null $senderName
	 * @param string|null $senderEmail
	 * @param string|null $replyTo
	 * @param string|null $publicName
	 * @return array
	 * @throws SmartEmailingException
	 */
	public function updateContactlist($id, $name = NULL, $senderName = NULL, $senderEmail = NULL, $replyTo = NULL, $publicName = NULL)
	{
		$data = [];
		if ($name !== NULL) $data['name'] = $name;
		if ($senderName !== NULL) $data['sendername'] = $senderName;
		if ($senderEmail !== NULL) $data['senderemail'] = $senderEmail;
		if ($replyTo !== NULL) $data['replyto'] = $replyTo;
		if ($publicName !== NULL) $data['publicname'] = $publicName;
		if ($publicName !== NULL) $data['publicname'] = $publicName;

		return $this->call(self::METHOD_PUT, self::NODE_CONTACTLISTS . "/" . $id, $data);
	}


	/**
	 * @param string $from
	 * @param string $to
	 * @return array
	 * @throws SmartEmailingException
	 */
	public function changeContactEmail($from, $to)
	{
		return $this->call(self::METHOD_POST, self::NODE_CHANGE_EMAILADDRESS, [
			'from' => $from,
			'to' => $to,
		]);
	}


	/**
	 * Deletes contact and anonymizes all his leftover data. This action cannot be undone.
	 * This is GDPR complaint method to secure contact's right to be forgotten.
	 *
	 * @param $id
	 * @return array
	 * @throws SmartEmailingException
	 */
	public function deleteContact($id)
	{
		return $this->call(self::METHOD_DELETE, self::NODE_CONTACT_FORGET . "/" . $id);
	}


	/**
	 * @return array
	 * @throws SmartEmailingException
	 */
	public function getContacts()
	{
		return $this->call(self::METHOD_GET, self::NODE_CONTACTS);
	}


	/**
	 * @return array
	 * @throws SmartEmailingException
	 */
	public function getContact($id)
	{
		return $this->call(self::METHOD_GET, self::NODE_CONTACTS . "/" . $id);
	}


	/**
	 * https://app.smartemailing.cz/docs/api/v3/index.html#api-Import-Import_contacts
	 *
	 * @param $email
	 * @param array|NULL $contactLists
	 * @param array|NULL $properties
	 * @param array|NULL $customFields
	 * @param array|NULL $purposes
	 * @param array|NULL $settings
	 */
	public function importContact($email, array $contactLists = NULL, array $properties = NULL, array $customFields = NULL, array $purposes = NULL, array $settings = NULL)
	{
		$contact = [
			'emailaddress' => $email,
		];

		if (is_array($contactLists)) {
			$contact['contactlists'] = [];
			foreach ($contactLists as $id => $status) {
				$contact['contactlists'][] = [
					'id' => $id,
					'status' => $status,
				];
			}
		}

		if (is_array($properties)) {
			foreach ($properties as $name => $value) {
				$contact['data'][$name] = $value;
			}
		}

		if (is_array($customFields)) {
			$contact['customfields'] = $customFields;
		}

		if (is_array($purposes)) {
			$contact['purposes'] = $purposes;
		}

		$data = [
			'data' => [
				$contact,
			],
		];

		if (is_array($settings)) {
			$data['settings'] = $settings;
		}

		return $this->call(self::METHOD_POST, self::NODE_IMPORT, $data);
	}

	/**
	 * connect to Smartemailing API v3
	 *
	 * @param string $method
	 * @param string $node
	 * @param array|null $data
	 * @param array|null $query
	 * @return array
	 * @throws SmartEmailingException
	 */
	protected function call($method, $node, $data = NULL, $query = NULL)
	{
		$options = [
			'auth' => [$this->username, $this->token],
		];
		if (is_array($data)) {
			$options['json'] = $data;
		}
		if (is_array($query)) {
			$options['query'] = $query;
		}

		$client = new Client();

		try {
			$response = $client->request($method, $this->url . "/" . $node, $options);
		} catch (ClientException $e) {
			$response = $e->getResponse();
			$body = Json::decode((string) $response->getBody(), Json::FORCE_ARRAY);
			throw new SmartEmailingException($body['message'], $response->getStatusCode());
		}

		return Json::decode((string) $response->getBody(), Json::FORCE_ARRAY);
	}


}
