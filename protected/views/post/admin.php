<?php
/* @var $this PostController */
/* @var $model Post */

$this->breadcrumbs=array(
	'Posts'=>array('index'),
	'Manage',
);


$roleId = Yii::app()->user->getState('role_id');
$permissions = Yii::app()->user->getState('permissions', []);
$isAdmin = ($roleId == 1);

$this->menu = array();


 $this->menu[] = array('label' => 'List Post', 'url' => array('index'));

//  if ($roleId != 1 || in_array('createPost', $permissions)) {
if ($roleId == 2 && in_array('createPost', $permissions)) {
    $this->menu[] = array('label' => 'Create Post', 'url' => array('create'));
}

//  $this->menu=array(
// 	array('label'=>'List Post', 'url'=>array('index')),
// 	array('label'=>'Create Post', 'url'=>array('create')),
// );





Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$('#post-grid').yiiGridView('update', {
		data: $(this).serialize()
	});
	return false;
});
");
?>

<h1>Manage Posts</h1>

<p>
You may optionally enter a comparison operator (<b>&lt;</b>, <b>&lt;=</b>, <b>&gt;</b>, <b>&gt;=</b>, <b>&lt;&gt;</b>
or <b>=</b>) at the beginning of each of your search values to specify how the comparison should be done.
</p>

<?php 
//echo CHtml::link('Advanced Search','#',array('class'=>'search-button')); 
?>
<div class="search-form" style="display:none">
<?php $this->renderPartial('_search',array(
	'model'=>$model,
)); ?>
</div><!-- search-form -->

<?php $this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'post-grid',
	'dataProvider'=>$model->search(),
	//'filter'=>$model,
	'columns'=>array(
		'id',
		 array(
            'name' => 'user_id',
            'value' => '$data->user->name', // displays user's name
            'filter' => CHtml::listData(User::model()->findAll(), 'id', 'name'),
        ),
        array(
            'name' => 'category_id',
            'value' => '$data->category->name', // displays category name
            'filter' => CHtml::listData(Category::model()->findAll(), 'id', 'name'),
        ),
		'title',
		//'content',
		array(
			 'class'=>'CButtonColumn',
			'template' => '{view} {update} {delete}', // Define the template for actions
            'buttons' => array(
                'view' => array(
                   // 'visible' => 'Yii::app()->user->role_id == 1 || in_array("viewPost", Yii::app()->user->getState("permissions"))', // Admin can view, or based on permission
                ),
                'update' => array(
                    'visible' => 'Yii::app()->user->role_id == 1 || in_array("updatePost", Yii::app()->user->getState("permissions"))', // Admin can update, or based on permission
                ),
                'delete' => array(
                    'visible' => 'Yii::app()->user->role_id == 1 || in_array("deletePost", Yii::app()->user->getState("permissions"))', // Admin can delete, or based on permission
                ),
            ),
		),
	),
)); ?>
