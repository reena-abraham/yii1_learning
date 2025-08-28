<?php
/* @var $this PostController */
/* @var $dataProvider CActiveDataProvider */

$this->breadcrumbs=array(
	'Posts',
);

$roleId = Yii::app()->user->getState('role_id');
$permissions = Yii::app()->user->getState('permissions', []);
$isAdmin = ($roleId == 1);
$this->menu = array();

// if ($roleId != 1 || in_array('createPost', $permissions)) {
if ($roleId == 2 && in_array('createPost', $permissions)) {
    $this->menu[] = array('label' => 'Create Post', 'url' => array('create'));
}
 $this->menu[] = array('label' => 'Manage Post', 'url' => array('admin'));

// $this->menu=array(
// 	array('label'=>'Create Post', 'url'=>array('create')),
// 	array('label'=>'Manage Post', 'url'=>array('admin')),
// );
?>

<h1>Posts</h1>

<?php $this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$dataProvider,
	'itemView'=>'_view',
)); ?>
