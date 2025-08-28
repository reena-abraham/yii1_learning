<?php

class PostController extends Controller
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
				'allow', // admin has full access
				'expression' => 'Yii::app()->user->role_id == 1',
			),
			array(
				'allow', // role 2 users can view only their own posts and access create/update/delete based on permissions
				'actions' => array('index', 'view', 'admin'),
				'expression' => 'Yii::app()->user->role_id == 2',
			),
			array(
				'allow',
				'actions' => array('create', 'update', 'delete'),
				'expression' => 'Yii::app()->user->role_id == 2 && (
                (Yii::app()->controller->action->id == "create" && in_array("createPost", Yii::app()->user->getState("permissions", []))) ||
                (Yii::app()->controller->action->id == "update" && in_array("updatePost", Yii::app()->user->getState("permissions", []))) ||
                (Yii::app()->controller->action->id == "delete" && in_array("deletePost", Yii::app()->user->getState("permissions", [])))
            )',
			),
			array(
				'deny', // deny all others
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
		$model = new Post;

		// Always define categories first
		$categories = CHtml::listData(Category::model()->findAll(), 'id', 'name');

		if (isset($_POST['Post'])) {
			$model->attributes = $_POST['Post'];
			$model->user_id = Yii::app()->user->id;
			// Check if an image file was uploaded
			if (isset($_FILES['Post']['name']['image']) && $_FILES['Post']['name']['image'] !== '') {
				// Handle the uploaded file

				$model->image = CUploadedFile::getInstance($model, 'image');
				$filename = uniqid() . '.' . $model->image->extensionName;
			}

			if ($model->save()) {

				if ($model->image !== null) {
					$uploadPath = Yii::getPathOfAlias('webroot') . '/uploads/';
					if (!is_dir($uploadPath)) {
						mkdir($uploadPath, 0777, true);
					}
					$model->image->saveAs($uploadPath . $filename);
				}
				$this->redirect(array('view', 'id' => $model->id));
			}
		}

		$this->render('create', array(
			'model' => $model,
			'categories' => $categories,
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

		$categories = CHtml::listData(Category::model()->findAll(), 'id', 'name');

		if (isset($_POST['Post'])) {
			$model->attributes = $_POST['Post'];

			if (isset($_FILES['Post']['name']['image']) && $_FILES['Post']['name']['image'] !== '') {
				// Handle the uploaded file
				$model->image = CUploadedFile::getInstance($model, 'image');
				// Generate a unique filename for the uploaded image
				$filename = uniqid() . '.' . $model->image->extensionName;

				// Delete the old image if necessary (optional, if you want to remove the old image)
				if ($model->image) {
					$oldImagePath = Yii::getPathOfAlias('webroot') . '/uploads/' . $model->image;
					if (file_exists($oldImagePath)) {
						unlink($oldImagePath); // Delete the old image file
					}
				}

				// Save the new image
				$uploadPath = Yii::getPathOfAlias('webroot') . '/uploads/';
				if (!is_dir($uploadPath)) {
					mkdir($uploadPath, 0777, true);
				}
				$model->image->saveAs($uploadPath . $filename);

				// Save the new filename in the database
				$model->image = $filename;
			}
			if ($model->save())
				$this->redirect(array('view', 'id' => $model->id));
		}

		$this->render('update', array(
			'model' => $model,
			'categories' => $categories,
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
	// public function actionIndex()
	// {
	// 	$dataProvider = new CActiveDataProvider('Post');
	// 	$this->render('index', array(
	// 		'dataProvider' => $dataProvider,
	// 	));
	// }
	public function actionIndex()
	{
		// Check the role of the logged-in user
		$roleId = Yii::app()->user->role_id;

		if ($roleId == 1) {
			// If the user is an admin, fetch all posts created by users (role_id = 2)
			$dataProvider = new CActiveDataProvider('Post', array(
				'criteria' => array(
					'condition' => 'user_id IN (SELECT u.id FROM users u INNER JOIN user_roles ur ON u.id = ur.user_id WHERE ur.role_id = 2)', // Fetch posts by regular users
					'order' => 'id DESC', // Order by creation date
				),
			));
		} else {
			// If the user is a regular user, fetch only their own posts
			$dataProvider = new CActiveDataProvider('Post', array(
				'criteria' => array(
					'condition' => 'user_id = :user_id', // Fetch only posts created by the logged-in user
					'params' => array(':user_id' => Yii::app()->user->id),
					'order' => 'id DESC', // Order by creation date
				),
			));
		}

		// Render the view and pass the dataProvider to display the posts
		$this->render('index', array(
			'dataProvider' => $dataProvider,
		));
	}


	/**
	 * Manages all models.
	 */
	// public function actionAdmin()
	// {
	// 	$model = new Post('search');
	// 	$model->unsetAttributes();  // clear any default values
	// 	if (isset($_GET['Post']))
	// 		$model->attributes = $_GET['Post'];

	// 	$this->render('admin', array(
	// 		'model' => $model,
	// 	));
	// }
	// 	public function actionAdmin() 
	// {

	// 		$model = new Post('search');
	// 		$model->unsetAttributes();  // clear any default values
	// 		if (isset($_GET['Post']))
	// 		{
	// 			$model->attributes = $_GET['Post'];
	// 		}
	//     // Get the role of the logged-in user
	//     $roleId = Yii::app()->user->role_id;
	//     if ($roleId == 1) {
	//         // Admin: fetch all posts created by users with role_id = 2 (regular users)
	//         $dataProvider = new CActiveDataProvider('Post', array(
	//             'criteria' => array(
	//                 'condition' => 'user_id IN (SELECT u.id FROM users u INNER JOIN user_roles ur ON u.id = ur.user_id WHERE ur.role_id = 2)', // Posts by regular users
	//                 'order' => 'id DESC', // Order by creation date
	//             ),
	//         ));
	//     } else {

	//         // Regular user: fetch only posts created by the logged-in user
	//         $dataProvider = new CActiveDataProvider('Post', array(
	//             'criteria' => array(
	//                 'condition' => 'user_id = :user_id', // Fetch only posts created by the logged-in user
	//                 'params' => array(':user_id' => Yii::app()->user->id),
	//                 'order' => 'id DESC', // Order by creation date
	//             ),
	//         ));
	//     }
	//     // Render the view and pass the dataProvider to display the posts
	//     $this->render('admin', array(
	//         'dataProvider' => $dataProvider,
	// 		'model' => $model,
	//     ));
	// }
	public function actionAdmin()
	{
		$model = new Post('search');
		// $model->unsetAttributes(); // clear any default values

		// Get the role of the logged-in user
		$roleId = Yii::app()->user->role_id;

		if ($roleId == 1) {
			// Admin: Fetch all posts created by users with role_id = 2 (regular users)
			$dataProvider = new CActiveDataProvider('Post', array(
				'criteria' => array(
					'condition' => 'user_id IN (SELECT id FROM users WHERE role_id = 2)', // Posts created by regular users
					'order' => 'id DESC', // Order by creation date
				),
			));
		} else {
			// Regular user: Fetch only posts created by the logged-in user
			$dataProvider = new CActiveDataProvider('Post', array(
				'criteria' => array(
					'condition' => 'user_id = :user_id', // Fetch posts created by the logged-in user
					'params' => array(':user_id' => Yii::app()->user->id), // Use the logged-in user's ID
					'order' => 'id DESC', // Order by creation date
				),
			));
		}

		// Render the view and pass the dataProvider to display the posts
		$this->render('admin', array(
			'dataProvider' => $dataProvider,
			'model' => $model
		));
	}


	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return Post the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model = Post::model()->findByPk($id);
		if ($model === null)
			throw new CHttpException(404, 'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param Post $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if (isset($_POST['ajax']) && $_POST['ajax'] === 'post-form') {
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}
