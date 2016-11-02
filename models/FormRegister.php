<?php
	namespace app\models;
	use Yii;
	use yii\base\model;
	use app\models\Usuario;

	class FormRegister extends model{

		public $username;
		public $email;
		public $password;
		public $password_repeat;

		public function rules(){

			return [
				    [['username', 'email', 'password', 'password_repeat'], 'required', 'message' => 'Campo requerido'],
		            ['username', 'match', 'pattern' => "/^.{3,50}$/", 'message' => 'Mínimo 3 y máximo 50 caracteres'],
		            ['username', 'match', 'pattern' => "/^[0-9a-z]+$/i", 'message' => 'Sólo se aceptan letras y números'],
		            ['username', 'username_existe'],
		            ['email', 'match', 'pattern' => "/^.{5,80}$/", 'message' => 'Mínimo 5 y máximo 80 caracteres'],
		            ['email', 'email', 'message' => 'Formato no válido'],
		            ['email', 'email_existe'],
		            ['password', 'match', 'pattern' => "/^.{8,16}$/", 'message' => 'Mínimo 6 y máximo 16 caracteres'],
		            ['password_repeat', 'compare', 'compareAttribute' => 'password', 'message' => 'Los passwords no coinciden'],
				];
		}

		public function email_existe($atribute, $params){

			//Buscar email en la tabla
			$tabla = Usuario::find() -> where("email=:email", [":email" => $this->email]);

			//Si el email existe se muestra un error
			if($tabla->count() == 1 )
			{
				$this->addError($atribute, "El email seleccionado ya existe");
			}
		}

		public function username_existe($atribute, $params)
		{
			$tabla = Usuario::find() -> where("username=:username", [":username" => $this->username]);

			if ($tabla->count() == 1)
  			{
                $this->addError($atribute, "El usuario seleccionado existe");
 			 }			
		}
	}