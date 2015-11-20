# CakePHP 3 Mandrill API plugin

Plugin Mandrill pour CakePHP 3 pour utiliser l'API.
Afin de l'utiliser vous devez disposer d'un compte Mandrill pour avoir une API key.

Pour installer ce plugin, la meilleure solution est d'utiliser Composer.

Rajoutez :

    "orken/cakephp3-mandrill-api": "*"

dans votre fichier `composer.json`
et lancer `composer update` .

## Configurer votre application CakePHP ##

Dans votre fichier de configuration (`app.php` ou un spécifique), rajouter une rubrique `Mandrill`.

    'Mandrill' => [
		'apikey'		=> '----votre cle---',
		'template_name'	=> 'nom-du-template'
    ]

Toutes les valeurs dans cette rubrique sont optionnelles et peuvent être assignées postérieurement au moment de l'instanciation de la classe.

## Envoyer des emails ##

Ajouter le namespace pour MandrillApi:

	use MandrillApi\Network\Email\MandrillApi;

Puis créé un email, assigner le template Mandrill/Mailchimp, donnez les destinataires ainsi que les valeurs particuliers, et envoyez.

	$email = new Mandrill(['template_name'=>'mon-template-mailchimp']);
    $email
        ->subject('Mon sujet Mandrill')
        ->from('contact@example.com',"Mon nom d'expéditeur")
        ->data([
        	'lemail1@domaine1.com'=> [
        		'displayname' => 'monsieur 1',
        		'texteperso' => "Lorem ipsum dolor sit amet."
        	],
        	'lemail2@domaine2.fr'=> [
        		'displayname' => 'madame 1',
        		'texteperso' => "Sunt saepe at, officiis quasi impedit?"
        	]
        ])
        ->send();

Pensez à valider le domaine qui envoie les mail dans Mandrill.