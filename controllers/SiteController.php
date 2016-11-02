<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\data\Pagination;
use app\models\LoginForm;
use app\models\ContactForm;
use yii\widgets\ActiveForm;
use yii\web\Response;
use yii\helpers\Url;
use yii\helpers\Html;
use app\models\FormRegister;
use app\models\Usuario;
use app\models\email;

class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        $msg = null;
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            if($model->validate())//mirar porque no funciona el validar
            {
                    $table = new email;

                    $table->name_email = $model->name;
                    $table->direccion_email = $model->email;
                    $table->content_email = $model->body;
                    $table->subject_email = $model->subject;
                    if($table->insert())
                    {
                        $msg= "felicidades";
                        $model->name = null;
                        $model->email = null;
                        $model->body = null;
                        $model->subject = null;
                    }
                    else
                    {
                        $msg = "ha ocurrido un error";
                    }
            }
         else
        {
            $model->getErrors();
        }

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
            'msg' => $msg
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }
    // funcion que muestra resultados conexion base de datos en la pagina saluda
    public function actionTienda()
    {
 
        $query = Usuario::find();

        $pagination = new Pagination([
            'defaultPageSize' => 5,
            'totalCount' => $query->count(),
        ]);

        $usuarios = $query->orderBy('username')
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        return $this->render('tienda', [
            'usuarios' => $usuarios,
            'pagination' => $pagination,
        ]);
    
    }
    // nos conecta con la vista cita
    public function actionCita()
    {
        return $this->render('cita');
    }

    public function actionFormacion()
    {
        return $this->render('formacion');
    }

    public function actionFormulario($mensaje = null){

        return $this->render('formulario',

            [
                'mensaje' => $mensaje
            ]);
    }

    // si ha introducido correctamente 
    public function actionRequest(){

        $mensaje = null;
        if(isset($_REQUEST['nombre'])&&isset($_REQUEST['apellido'])){
            $mensaje = 'Has introducido el nombre correctamente '.$_REQUEST['nombre'].' '.$_REQUEST['apellido'];
        }
        $this->redirect(['site/formulario','mensaje' => $mensaje]);
    }
    public function actionValidarformulario()
    {

        $model = new ValidarFormulario;
  
          if ($model->load(Yii::$app->request->post()))
          {
              if($model->validate())
                    {
                        //Por ejemplo, consultar en una base de datos
                    }
                    else
                    {
                        $model->getErrors();
                    }
          }
  
        return $this->render("validarformulario", ["model" => $model]);
    }

    //metodos para el registro de usuarios
    // metodo para generar claves aleatorias para las columnas authKey y accesToken
    private function randKey($str='', $long = 0)
    {
        $key = null;
        $str = str_split($str);
        $start = 0;
        $limit = count($str)-1;
        for($x=0;$x<$long;$x++)
        {
            $key .= $str[rand($start, $limit)];
        }
        return $key;
    }
    //metodo para activar el usuario cuando este confirme el email que se le envia
   public function actionConfirm()
 {

    $table = new Usuario;

    if (Yii::$app->request->get())
    {
   
        //Obtenemos el valor de los parámetros get
        $id = Html::encode($_GET["id"]);
        $authKey = $_GET["authKey"];
    
        if ((int) $id)
        {
            //Realizamos la consulta para obtener el registro
            $model = $table
            ->find()
            ->where("id=:id", [":id" => $id])
            ->andWhere("authKey=:authKey", [":authKey" => $authKey]);
 
            //Si el registro existe
            if ($model->count() == 1)
            {
                $activar = Usuario::findOne($id);
                $activar->activate = 1;
                if ($activar->update())
                {
                    echo "Enhorabuena registro llevado a cabo correctamente, redireccionando ...";
                    echo "<meta http-equiv='refresh' content='8; ".Url::toRoute("site/login")."'>";
                }
                else
                {
                    echo "Ha ocurrido un error al realizar el registro, redireccionando ...";
                    echo "<meta http-equiv='refresh' content='8; ".Url::toRoute("site/login")."'>";
                }
             }
            else //Si no existe redireccionamos a login
            {
                return $this->redirect(["site/login"]);
            }
        }
        else //Si id no es un número entero redireccionamos a login
        {
            return $this->redirect(["site/login"]);
        }
    }
 }

    public function actionRegister()
    {
      //Creamos la instancia con el model de validación
      $model = new FormRegister;
       
      //Mostrará un mensaje en la vista cuando el usuario se haya registrado
      $msg = null;
       
      //Validación mediante ajax
      if ($model->load(Yii::$app->request->post()) && Yii::$app->request->isAjax)
            {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            }
       
      //Validación cuando el formulario es enviado vía post
      //Esto sucede cuando la validación ajax se ha llevado a cabo correctamente
      //También previene por si el usuario tiene desactivado javascript y la
      //validación mediante ajax no puede ser llevada a cabo
      if ($model->load(Yii::$app->request->post()))
      {
       if($model->validate())
       {
        //Preparamos la consulta para guardar el usuario
        $table = new Usuario;
        $table->username = $model->username;
        $table->email = $model->email;
        //Encriptamos el password
        $table->password = crypt($model->password, Yii::$app->params["salt"]);
        //Creamos una cookie para autenticar al usuario cuando decida recordar la sesión, esta misma
        //clave será utilizada para activar el usuario
        $table->authKey = $this->randKey("abcdef0123456789", 200);
        //Creamos un token de acceso único para el usuario
        $table->accessToken = $this->randKey("abcdef0123456789", 200);
         
        //Si el registro es guardado correctamente
        if ($table->insert())
        {
         //Nueva consulta para obtener el id del usuario
         //Para confirmar al usuario se requiere su id y su authKey
         $user = $table->find()->where(["email" => $model->email])->one();
         $id = urlencode($user->id);
         $authKey = urlencode($user->authKey);
          
         $subject = "Confirmar registro";
         $body = "<h1>Haga click en el siguiente enlace para finalizar tu registro</h1>";
         $body .= "<a href='http://yii.local/site/confirm?id=".$id."&authKey=".$authKey."'>Confirmar</a>";
        
         //Enviamos el correo
         Yii::$app->mailer->compose()
         ->setTo($user->email)
         ->setFrom([Yii::$app->params["adminEmail"] => Yii::$app->params["title"]])
         ->setSubject($subject)
         ->setHtmlBody($body)
         ->send();
         
         $model->username = null;
         $model->email = null;
         $model->password = null;
         $model->password_repeat = null;
         
         $msg = "Enhorabuena, ahora sólo falta que confirmes tu registro en tu cuenta de correo";
        }
        else
        {
         $msg = "Ha ocurrido un error al llevar a cabo tu registro";
        }
         
       }
       else
       {
        $model->getErrors();
       }
      }
      return $this->render("register", ["model" => $model, "msg" => $msg]);
    }
    
}