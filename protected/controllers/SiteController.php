<?php

class SiteController extends Controller
{


	public function accessRules()
	{
		return array(
			array(
				'allow',
				'actions' => array('login', 'signup'),
				'users' => array('?'), // guests only
			),
			array(
				'allow',
				'actions' => array('logout', 'index', 'otherAction'),
				'users' => array('@'), // authenticated users only
			),
			array(
				'deny',
				'users' => array('*'), // deny all others
			),
		);
	}
	/**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array(
			// captcha action renders the CAPTCHA image displayed on the contact page
			'captcha' => array(
				'class' => 'CCaptchaAction',
				'backColor' => 0xFFFFFF,
			),
			// page action renders "static" pages stored under 'protected/views/site/pages'
			// They can be accessed via: index.php?r=site/page&view=FileName
			'page' => array(
				'class' => 'CViewAction',
			),
		);
	}

	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionIndex()
	{
		// renders the view file 'protected/views/site/index.php'
		// using the default layout 'protected/views/layouts/main.php'
		// /print_r(Yii::app()->user->getState('role_id'));exit;
		$this->render('index');
	}

	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError()
	{
		if ($error = Yii::app()->errorHandler->error) {
			if (Yii::app()->request->isAjaxRequest)
				echo $error['message'];
			else
				$this->render('error', $error);
		}
	}

	/**
	 * Displays the contact page
	 */
	public function actionContact()
	{
		$model = new ContactForm;
		if (isset($_POST['ContactForm'])) {
			$model->attributes = $_POST['ContactForm'];
			if ($model->validate()) {
				$name = '=?UTF-8?B?' . base64_encode($model->name) . '?=';
				$subject = '=?UTF-8?B?' . base64_encode($model->subject) . '?=';
				$headers = "From: $name <{$model->email}>\r\n" .
					"Reply-To: {$model->email}\r\n" .
					"MIME-Version: 1.0\r\n" .
					"Content-Type: text/plain; charset=UTF-8";

				mail(Yii::app()->params['adminEmail'], $subject, $model->body, $headers);
				Yii::app()->user->setFlash('contact', 'Thank you for contacting us. We will respond to you as soon as possible.');
				$this->refresh();
			}
		}
		$this->render('contact', array('model' => $model));
	}

	/**
	 * Displays the login page
	 */
	public function actionLogin()
	{

		if (!Yii::app()->user->isGuest) {
			$this->redirect(Yii::app()->homeUrl); // or any other page you want to redirect to
			return; // stop further execution
		}
		$model = new LoginForm;

		// if it is ajax validation request
		if (isset($_POST['ajax']) && $_POST['ajax'] === 'login-form') {
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}

		// collect user input data
		if (isset($_POST['LoginForm'])) {
			$model->attributes = $_POST['LoginForm'];
			// validate user input and redirect to the previous page if valid
			if ($model->validate() && $model->login()) {
				// Get the logged-in user model
				$user = User::model()->findByPk(Yii::app()->user->id);
				$roleId = $user->userRole ? $user->userRole->role_id : null;
				// print_r($user->role_id);die();
				// Save role_id in session
				Yii::app()->user->setState('role_id', $roleId);

				// Save permissions in session
				$permissions = [];
				foreach ($user->permissions as $perm) {  // Adjust relation name if needed
					$permissions[] = $perm->name;  // or permission code/key depending on your table structure
				}
				Yii::app()->user->setState('permissions', $permissions);
				$this->redirect(Yii::app()->user->returnUrl);
			}
		}
		// display the login form
		$this->render('login', array('model' => $model));
	}

	public function actionSignup()
	{

		if (!Yii::app()->user->isGuest) {
			$this->redirect(Yii::app()->homeUrl); // or any other page you want to redirect to
			return; // stop further execution
		}
		
		$model = new User;

		if (isset($_POST['User'])) {
			$model->attributes = $_POST['User'];

			// Hash the raw password (assuming form input name is 'password')
			$model->password = password_hash($model->password, PASSWORD_BCRYPT);

			if ($model->save()) {
				Yii::app()->db->createCommand()->insert('user_roles', array(
					'user_id' => $model->id,
					'role_id' => (int)$model->role_id
				));
				Yii::app()->user->setFlash('message', 'Registration successful');
				$this->redirect(array('site/login'));
			} else {
				Yii::app()->user->setFlash('error', 'Registration failed');
			}
		}
		$roles = CHtml::listData(Role::model()->findAll(), 'id', 'name');
		$this->render('signup', array('model' => $model, 'roles' => $roles));
	}

	/**
	 * Logs out the current user and redirect to homepage.
	 */
	public function actionLogout()
	{
		Yii::app()->user->logout();
		$this->redirect(Yii::app()->homeUrl);
	}
}
