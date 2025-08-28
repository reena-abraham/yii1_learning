<?php

class UserPermission extends CActiveRecord
{
    public function tableName()
    {
        return 'user_permissions';
    }

    public function relations()
{
    return array(
        'user' => array(self::BELONGS_TO, 'User', 'user_id'),
        'permission' => array(self::BELONGS_TO, 'Permission', 'permission_id'),
    );
}

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
