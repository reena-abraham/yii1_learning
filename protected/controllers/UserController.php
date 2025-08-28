<?php

class UserController extends Controller
{
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
	public $layout = '//layouts/column2';

	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
			'postOnly + delete', // we only allow deletion via POST request
		);
	}

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			array(
				'allow',  // allow admin users to access all actions in UserController
				'actions' => array('index', 'view', 'create', 'update', 'admin', 'delete', 'permissions'), // list all user management actions here
				'expression' => function ($user) {
					// check if user is logged in and has role 'admin'
					return !$user->isGuest && Yii::app()->user->getState('role_id') == 1; // assuming role_id 1 = admin
				},
			),
			array(
				'deny',  // deny all other users
				'users' => array('*'),
			),
		);
	}

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id)
	{
		$this->render('view', array(
			'model' => $this->loadModel($id),
		));
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model = new User;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if (isset($_POST['User'])) {
			$model->attributes = $_POST['User'];
			if ($model->save())
				$this->redirect(array('view', 'id' => $model->id));
		}

		$this->render('create', array(
			'model' => $model,
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		$model = $this->loadModel($id);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if (isset($_POST['User'])) {
			$model->attributes = $_POST['User'];
			if ($model->save())
				$this->redirect(array('view', 'id' => $model->id));
		}

		$this->render('update', array(
			'model' => $model,
		));
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id)
	{
		$this->loadModel($id)->delete();

		// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		if (!isset($_GET['ajax']))
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{

		$loggedInUserId = Yii::app()->user->id;
		// Modify the data provider to exclude the logged-in user
		$dataProvider = new CActiveDataProvider('User', array(
			'criteria' => array(
				'condition' => 'id != :id',  // Exclude the logged-in user
				'params' => array(':id' => $loggedInUserId),
			),
		));

		// Render the index view with the data provider
		$this->render('index', array(
			'dataProvider' => $dataProvider,
		));
	}

	/**
	 * Manages all models.
	 */
	public function actionAdmin()
	{
		$model = new User('search');
		$model->unsetAttributes();  // clear any default values
		if (isset($_GET['User']))
			$model->attributes = $_GET['User'];

		$this->render('admin', array(
			'model' => $model,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return User the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model = User::model()->findByPk($id);
		if ($model === null)
			throw new CHttpException(404, 'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param User $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if (isset($_POST['ajax']) && $_POST['ajax'] === 'user-form') {
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}


	public function actionPermissions($id)
	{
		$user = User::model()->findByPk($id);
		if (!$user) throw new CHttpException(404, 'User not found.');

		$allPermissions = Permission::model()->findAll();

		$assignedPermissions = Yii::app()->db->createCommand()
			->select('permission_id')
			->from('user_permissions')
			->where('user_id=:id', array(':id' => $id))
			->queryColumn();

		if (isset($_POST['permissions'])) {
			// Remove existing permissions
			Yii::app()->db->createCommand()->delete('user_permissions', 'user_id=:id', array(':id' => $id));

			// Add new permissions
			foreach ($_POST['permissions'] as $permId) {
				Yii::app()->db->createCommand()->insert('user_permissions', array(
					'user_id' => $id,
					'permission_id' => $permId,
				));
			}

			Yii::app()->user->setFlash('success', 'Permissions updated successfully.');
			$this->redirect(array('user/admin'));
		}

		$this->render('permission', array(
			'user' => $user,
			'allPermissions' => $allPermissions,
			'assignedPermissions' => $assignedPermissions,
		));
	}
}
