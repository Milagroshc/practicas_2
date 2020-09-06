<?php
namespace App\Helper;

/**
* 
*/
class Hash 
{
	
	protected $config;
    private $session;
    public function __construct($config)
    {
        $this->config = $config;
        $this->session = new \App\Helper\Session;
    }

    

    public  function password($password)
    {
        $config = $this->config;
        return password_hash(
            $password,
            $config['hash']['algo'],
            ['cost' => $config['hash']['cost']]
        );
    }

    public function passwordCheck($password, $hash)
    {
        return password_verify($password, $hash);
    }

    public static function hash($input) {
        return hash('sha256', $input);
      }

      public function md5Slim($input) {
        return md5($input);
      }
      public static function hash_equals($a, $b) {
          return substr_count($a ^ $b, "\0") * 2 === strlen($a . $b);
      }
      public function hashCheck($known, $user) {
        return $this->hash_equals($known, $user);
      }



}