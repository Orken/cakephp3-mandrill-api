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

class MandrillApi
{
    public $http;
    public $key = null;
    public $defaultOptions = [
	    'merge_language'	=> 'handlebars',
	    'inline_css'		=> true,
    ];

}

