<?php
/**
 * Send mail using Mandrill Api (with MailChimp)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 *
 * @author        Michel Subotkiewiez (http://lbc2rss.superfetatoire.com)
 * @link          http://lbc2rss.superfetatoire.com
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace MandrillApi\Network\Email;

use Cake\Core\Configure;
use Cake\Utility\Hash;
use InvalidArgumentException;
use Cake\Network\Http\Client;
use Cake\Network\Exception\SocketException;
use Cake\I18n\Time;


class MandrillApi
{
	/**
	 * Holds the regex pattern for email validation
	 *
	 * @var string
	 */
	const EMAIL_PATTERN = '/^((?:[\p{L}0-9.!#$%&\'*+\/=?^_`{|}~-]+)*@[\p{L}0-9-.]+)$/ui';
	/**
	 * Regex for email validation
	 *
	 * If null, filter_var() will be used. Use the emailPattern() method
	 * to set a custom pattern.'
	 *
	 * @var string
	 */
	protected $_emailPattern = self::EMAIL_PATTERN;


	public $http;
	public $config = null;
	public $defaultConfig = [
		'merge_language'	=> 'handlebars',
		'inline_css'		=> true,
		'global_merge_vars'	=> [ ],
		'template_content'	=> [],
	];

	public function __construct($config=[])
	{
		$this->config = Hash::merge($this->defaultConfig,Configure::read('Mandrill'),$config);
	}

	/**
	 * Set the email subject
	 * @param  string $subject email subject
	 * @return array|$this          MandrillApi object
	 */
	public function subject($subject)
	{
		$this->config['subject'] = $subject;
		return $this;
	}

	/**
	 * To
	 * @param  string|array $email String with email,
     *   Array with email as key, name as value or email as value (without name)
	 * @return array|$this       MandrillApi object
	 */
	public function to($email)
	{
		if (is_string($email))
		{
			$this->_addTo($email);
		}
		if (is_array($email))
		{
			foreach ($email as $key => $value)
			{
				if (is_int($key))
				{
					$this->_validateEmail($value);
					$this->_addTo($value);
				} else {
					$this->_validateEmail($key);
					$this->_addTo($key,$value);
				}
			}
		}
		return $this;
	}

	/**
	 * Reply to
	 * @param  string $email String with email,
	 * @return array|$this       MandrillApi object
	 */
	public function replyTo($email)
	{
    $this->config['headers']['Reply-To'] = $email;
		return $this;
	}

	public function attachment($name,$content,$mimetype)
	{
		$content_encoded = base64_encode($content);
		$this->config['attachments'][] = [
			'type'		=> $mimetype,
			'name'		=> $name,
			'content'	=> $content_encoded
		];
		return $this;
	}

	public function template($template)
	{
		$this->config['template_name'] = $template;
		return $this;
	}
	/**
	 * Configure le From du message
	 * @param  string  $email adresse email du sender
	 * @param  string|boolean $name  nom du sender, false si aucun
	 * @return $this         object MandrillApi
	 */
	public function from($email,$name=false)
	{
		$this->config['from_email'] = $email;
		if ($name!==false)
		{
			$this->config['from_name'] = $name;
		}
		return $this;
	}

	/**
	 * configure mes informations de destinataire ainsi que les variables du template
	 * @param  array $values tableau contenant les variables sous la forme 'email' => ['name'=>'content',...]
	 * @return $this         object MandrillApi
	 */
	public function data($values)
	{
		$datas = [];
		foreach ($values as $email => $vars)
		{
			$_data = [
				'rcpt' => $email
			];
			foreach ($vars as $name => $content)
			{
				$_data['vars'][] = [
					'name' => $name,
					'content' => $content,
				];
			}
			$datas[] = $_data;
 		}
 		$this->config['merge_vars'] = $datas;
 		return $this;
	}

	/**
	 * Envoie les informations à la plateforme mandrill
	 * @return string Reponse en json de la plateforme mandrill
	 */
	public function send()
	{
		$this->http = new Client([
			'host'   => 'mandrillapp.com',
			'scheme' => 'https',
			'headers' => [
				'User-Agent' => 'CakePHP Mandrill API Plugin'
			]
		]);
		return $this->_sendTemplate();
	}


	protected function _sendTemplate()
	{
		$this->config['tags'] = [$this->config['template_name']];
		$payload = [
			'key'				=> $this->config['apikey'],
			'template_name'		=> $this->config['template_name'],
			'template_content'	=> $this->config['template_content'],
			'message'			=> $this->config,
			'async'				=> false,
			'ip_pool'			=> 'Main Pool',
		];

		$response = $this->http->post(
			'/api/1.0/messages/send-template.json',
			json_encode($payload),
			['type' => 'json']
		);
		if (!$response) {
			throw new SocketException($response->code);
		}
		return $response->json;
	}

	/**
	 * Ajoute un email au tableau des To
	 * @param string  $email adresse email
	 * @param string $name  nom prenom de l'utilisateur
	 */
	protected function _addTo($email,$name=false)
	{
		$_email = [
			'email' =>$email,
			'type'  =>'to'
		];
		if ($name!==false) {
			$_email['name'] = $name;
		}
		$this->config['to'][] = $_email;
	}

	/**
	 * Validate email address
	 *
	 * @param string $email Email address to validate
	 * @return void
	 * @throws \InvalidArgumentException If email address does not validate
	 */
	protected function _validateEmail($email)
	{
		if ($this->_emailPattern === null)
		{
			if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
				return;
			}
		} elseif (preg_match($this->_emailPattern, $email))
		{
			return;
		}
		throw new InvalidArgumentException(sprintf('Invalid email: "%s"', $email));
	}

}

