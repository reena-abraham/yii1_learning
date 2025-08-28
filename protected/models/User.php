<?php

/**
 * This is the model class for table "users".
 *
 * The followings are the available columns in table 'users':
 * @property integer $id
 * @property string $name
 * @property string $username
 * @property string $email
 * @property string $created_at
 */
class User extends CActiveRecord
{
	public $role_id;
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'users';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name, username, email,password', 'required'),
			array('email', 'email'),
			array('password', 'length', 'min' => 6, 'max' => 255),
            array('email', 'unique'),
			array('role_id', 'required'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, name, username, email, created_at', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
        return [
			// Each user has one role through user_roles
            'userRole' => array(self::HAS_ONE,'UserRole','user_id'),
			'roles' => array(self::BELONGS_TO, 'Role', 'role_id'),
			'posts' => array(self::HAS_MANY,'Post','user_id'),
			'permissions' => array(self::MANY_MANY,'Permission','user_permissions(user_id, permission_id)'),
        ];
    }

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'name' => 'Name',
			'username' => 'Username',
			'email' => 'Email',
			'created_at' => 'Created At',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		// $criteria->compare('id',$this->id);
		// $criteria->compare('name',$this->name,true);
		// $criteria->compare('username',$this->username,true);
		// $criteria->compare('email',$this->email,true);
		// $criteria->compare('created_at',$this->created_at,true);
		// Join with user_roles table
    $criteria->with = array('userRole');

    // Filter: only users with role_id = 2 (non-admin)
    $criteria->compare('userRole.role_id', 2);

    $criteria->compare('t.id', $this->id);
    $criteria->compare('t.name', $this->name, true);
    $criteria->compare('t.username', $this->username, true);
    $criteria->compare('t.email', $this->email, true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return User the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
