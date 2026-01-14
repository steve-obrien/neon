<?php
namespace neon\core\mail\transport;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Swift_Mime_SimpleMessage;

class MailgunTransport extends Transport
{
	/**
	 * @var \GuzzleHttp\ClientInterface
	 */
	protected $_client;

	/**
	 * The Mailgun API key.
	 *
	 * @var string
	 */
	protected $key;

	/**
	 * The Mailgun email domain.
	 *
	 * @var string
	 */
	protected $domain;

	/**
	 * The Mailgun API endpoint.
	 *
	 * @var string
	 */
	protected $endpoint;

	/**
	 * Create a new Mailgun transport instance.
	 *
	 * @param  string  $key
	 * @param  string  $domain
	 * @param  string|null  $endpoint
	 * @return void
	 */
	public function __construct($key, $domain, $endpoint = null)
	{
		$this->key = $key;
		$this->endpoint = $endpoint ?? 'api.mailgun.net';

		$this->setDomain($domain);
	}

	/**
	 * Get the Guzzle client object
	 *
	 * @return \GuzzleHttp\ClientInterface
	 */
	public function getClient()
	{
		if ($this->_client === null)
			$this->_client = new Client();
		return $this->_client;
	}

	/**
	 * Set the Guzzle client object
	 *
	 * @param \GuzzleHttp\ClientInterface $client
	 */
	public function setClient($client)
	{
		$this->_client = $client;
	}

	/**
	 * {@inheritdoc}
	 */
	public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients = null)
	{
		$this->beforeSendPerformed($message);

		$to = $this->getTo($message);

		$bcc = $message->getBcc();

		$message->setBcc([]);

		$response = $this->getClient()->request(
			'POST',
			"https://{$this->endpoint}/v3/{$this->domain}/messages.mime",
			$this->payload($message, $to)
		);

		$message->getHeaders()->addTextHeader(
			'X-Mailgun-Message-ID', $this->getMessageId($response)
		);

		$message->setBcc($bcc);

		$this->sendPerformed($message);

		return $this->numberOfRecipients($message);
	}

	/**
	 * Get the HTTP payload for sending the Mailgun message.
	 *
	 * @param  \Swift_Mime_SimpleMessage  $message
	 * @param  string  $to
	 * @return array
	 */
	protected function payload(Swift_Mime_SimpleMessage $message, $to)
	{
		return [
			'auth' => [
				'api',
				$this->key,
			],
			'multipart' => [
				[
					'name' => 'to',
					'contents' => $to,
				],
				[
					'name' => 'message',
					'contents' => $message->toString(),
					'filename' => 'message.mime',
				],
			],
		];
	}

	/**
	 * Get the "to" payload field for the API request.
	 *
	 * @param  \Swift_Mime_SimpleMessage  $message
	 * @return string
	 */
	protected function getTo(Swift_Mime_SimpleMessage $message)
	{
		return collect($this->allContacts($message))->map(function ($display, $address) {
			return $display ? $display." <{$address}>" : $address;
		})->values()->implode(',');
	}

	/**
	 * Get all of the contacts for the message.
	 *
	 * @param  \Swift_Mime_SimpleMessage  $message
	 * @return array
	 */
	protected function allContacts(Swift_Mime_SimpleMessage $message)
	{
		return array_merge((array) $message->getTo(), (array) $message->getCc(), (array) $message->getBcc());
	}

	/**
	 * Get the message ID from the response.
	 *
	 * @param  \Psr\Http\Message\ResponseInterface  $response
	 * @return string
	 */
	protected function getMessageId($response)
	{
		return object_get(json_decode($response->getBody()->getContents()), 'id');
	}

	/**
	 * Get the API key being used by the transport.
	 *
	 * @return string
	 */
	public function getKey()
	{
		return $this->key;
	}

	/**
	 * Set the API key being used by the transport.
	 *
	 * @param  string  $key
	 * @return string
	 */
	public function setKey($key)
	{
		return $this->key = $key;
	}

	/**
	 * Get the domain being used by the transport.
	 *
	 * @return string
	 */
	public function getDomain()
	{
		return $this->domain;
	}

	/**
	 * Set the domain being used by the transport.
	 *
	 * @param  string  $domain
	 * @return string
	 */
	public function setDomain($domain)
	{
		return $this->domain = $domain;
	}

	/**
	 * Get the API endpoint being used by the transport.
	 *
	 * @return string
	 */
	public function getEndpoint()
	{
		return $this->endpoint;
	}

	/**
	 * Set the API endpoint being used by the transport.
	 *
	 * @param  string  $endpoint
	 * @return string
	 */
	public function setEndpoint($endpoint)
	{
		return $this->endpoint = $endpoint;
	}
}
