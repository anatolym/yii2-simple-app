<?php

use yii\db\Migration;

class m160830_140906_create_table_User extends Migration
{
    public function up()
    {
        $this->createTable('User', [
            'userId'             => $this->primaryKey(),
            'username'           => $this->string(64)->notNull()->unique(),
            'passwordHash'       => $this->string(64),
            'passwordResetToken' => $this->string(64),
            'status'             => $this->string(16),
        ]);
    }

    public function down()
    {
        $this->dropTable('User');
    }

}
