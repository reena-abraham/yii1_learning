<?php
class Role extends CActiveRecord
{
    public static function model($className=__CLASS__) {
        return parent::model($className);
    }
    public function tableName() 
    { 
        return 'roles'; 
    }
    public function relations() {
        return [
           'permissions' => array(self::MANY_MANY,'Permission','roles_permissions(role_id, permission_id)'),

            // HAS_MANY users via userRoles
            'users' => array(self::HAS_MANY,'User',array('user_id' => 'id'),'through' => 'userRoles'),

        ];
    }
}
