<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user}}`.
 */
class m241029_223840_create_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->createTable('user', [
            'id' => $this->primaryKey(),
            'username' => $this->string()->notNull(),
            'email' => $this->string()->unique()->notNull(),
            'password_hash' => $this->string()->notNull(),
            'role' => "tinyint(1) NOT NULL CHECK (role IN (0, 1))",
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropTable('user');
    }
}
