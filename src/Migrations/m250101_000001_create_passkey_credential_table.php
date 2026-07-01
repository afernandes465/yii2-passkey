<?php

declare(strict_types=1);

use yii\db\Migration;

class m250101_000001_create_passkey_credential_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%passkey_credential}}', [

            'id' => $this->primaryKey(),

            'user_id' => $this->string(255)->notNull(),

            'credential_id' => $this->binary()->notNull(),

            'source' => $this->string(),

            'device_name' => $this->string(),

            'enabled' => $this->boolean()->notNull()->defaultValue(true),

            'last_used_at' => $this->dateTime(),

            'created_at' => $this->dateTime()->notNull(),
        ]);

        $this->createIndex(
            'idx_passkey_user',
            '{{%passkey_credential}}',
            'user_id'
        );

        $this->createIndex(
            'idx_passkey_credential_id',
            '{{%passkey_credential}}',
            'credential_id',
            true
        );

        $this->createIndex(
            'idx_passkey_enabled',
            '{{%passkey_credential}}',
            'enabled'
        );
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%passkey_credential}}');
    }
}