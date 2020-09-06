<?php

use App\Helper\Constante;

return [
	'app' => [
		'url' => 'http://slim.dev',
		'hash' => [
			'algo' => PASSWORD_BCRYPT,
			'cost' => 10
		]
	],

	'auth' => [
		'session'   => 'user_id',
		'group'     => 'group_id',
		'remember'  => 'user_r'
	],

	'settings' => [
		'displayErrorDetails' => true,
		'addContentLengthHeader' => false,

		// Renderer settings
		'renderer' => [
			'template_path' => __DIR__ . '/../templates/',
		],

		// Monolog settings
		'logger' => [
			'name' => 'slim-app',
			'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
			
			'level' => \Monolog\Logger::DEBUG,
		],

		'view' => [
			'template_path' => __DIR__ . '/../templates/',
			'twig' => [
				'cache' => __DIR__ . '/../cache/twig',
				'debug' => true,
				'auto_reload' => true,
				'charset' => 'UTF-8'
			],
		],

		'determineRouteBeforeAppMiddleware' => false,
		'displayErrorDetails' => true,

	// *************************************************************************************** CONEXIONES A BASES DE DATOS ******************************************************************************************
		// BD DESARROLLO
		'db' => [
			'driver' => 'mysql',
			'host' => 'localhost',
			'database' => 'apps_cms',
			'username' => 'root',
			'password' => '',
			'charset'   => 'utf8',
			'collation' => 'utf8_general_ci',
			'prefix'    => '',
		],
	// **************************************************************************************************************************************************************************************************************
		
		'paypal' => [
			'idCliente' => 'AR53mr55bTVfuH4E4HTQHl4nLrsY7ZG6XJYkhQngN5aDtve01DfCJlOxXtgeluBAj-oWessgmaLbRDmC',
			'secretCliente' => 'EJ_62qFkFs9AF6QwajzrAist-pIKq49mEwHrivyAwbYWLCXf6jQwdYvxkaa8SW2mhS8lBLVqWBMrgn56',
		],

		'redes' => [
			"base_url" => Constante::DOMAINSITE."hybrid.php",

			"providers" => array(
				// openid providers
				"OpenID" => array(
					"enabled" => true,
				),

				"Yahoo" => array(
					"enabled" => true,
					"keys" => array("id" => "", "secret" => ""),
				),

				"AOL" => array(
					"enabled" => true,
				),

				"Google" => array (
					"enabled" => true,
					"keys" => array ( "id" => "954846870997-bpgk2vr7pkg6csqa200gt1tc30h2t5lq.apps.googleusercontent.com", "secret" => "wZRJd7zmT88rMyOjkz7DZiad"),
					"scope" =>	"https://www.googleapis.com/auth/plus.login ".
								"https://www.googleapis.com/auth/plus.me ".
								"https://www.googleapis.com/auth/plus.profile.emails.read",
					"access_type" => "offline",
					"approval_prompt" => "force",
					"hd" => Constante::DOMAINSITE
				),

				"Facebook" => array(
					"enabled" => true,
					"keys" => array("id" => "896355703854794", "secret" => "3eccfe8027724f1409c94cdf8df00a6d"),
					"trustForwarded" => false
				),

				"Twitter" => array(
					"enabled" => true,
					"keys" => array("key" => "PC0bgHtL1DzIiIN7464ezfaeb", "secret" => "gguObYFu75aaEo4RQXfN2kN8X7culPnGRleynj3h6YdTUJFEvJ"),
					"includeEmail" => true,
				),

				"Live" => array(
					"enabled" => true,
					"keys" => array("id" => "311019ce-ade8-45dd-872f-d3510057646d", "secret" => "scKH94{;%vhekkDCOGJ150%"),
				),

				"LinkedIn" => array(
					"enabled" => true,
					"keys" => array("id" => "", "secret" => ""),
					"fields" => array(),
				),

				"Foursquare" => array(
					"enabled" => true,
					"keys" => array("id" => "", "secret" => ""),
				),
			),

			"debug_mode" => false,
			"debug_file" => "",
		],
	],
];