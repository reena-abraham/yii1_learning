<?php
class Permission extends CActiveRecord
{
    public static function model($className=__CLASS__) {
        return parent::model($className);
    }

    public function tableName() {
        return 'permissions';
    }

    public function rules() {
        return [['name', 'required'], ['name', 'unique']];
    }

    public function relations() {
        return [
            'roles' => [self::MANY_MANY, 'Role', 'role_permission(permission_id, role_id)']
        ];
    }
}
