<?php

	namespace app\models;
	use Yii;
	use yii\db\ActiveRecord;

	/**
	 * 
	 */
	 class Usuario extends ActiveRecord
	 {
	 	
	 	
	 	public static function tableName()
	 	{
	 		return 'cl_users';
	 	}

	 	public static function getDb()
	 	{
	 		return Yii::$app->db;
	 	}
	 } 