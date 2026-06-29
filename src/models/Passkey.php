<?php

declare(strict_types=1);

namespace Afernandes\Yii2Passkey\Models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int|string $user_id
 * @property string $credential_id
 * @property string $public_key
 * @property string|null $user_handle
 * @property string|null $aaguid
 * @property int $sign_count
 * @property string|null $transports
 * @property string|null $attestation_type
 * @property int $backup_eligible
 * @property int $backup_state
 * @property string|null $device_name
 * @property int $enabled
 * @property string $created_at
 * @property string|null $last_used_at
 */
class Passkey extends ActiveRecord
{
    public const STATUS_DISABLED = 0;
    public const STATUS_ENABLED = 1;

    public static function tableName(): string
    {
        return '{{%passkey}}';
    }

    public function rules(): array
    {
        return [
            [['user_id', 'credential_id', 'public_key'], 'required'],

            [['user_id'], 'safe'],

            [['public_key'], 'string'],

            [['sign_count'], 'integer'],

            [['backup_eligible', 'backup_state', 'enabled'], 'boolean'],

            [['created_at', 'last_used_at'], 'safe'],

            [['credential_id'], 'string', 'max' => 1024],
            [['user_handle'], 'string', 'max' => 255],
            [['aaguid'], 'string', 'max' => 64],
            [['transports'], 'string', 'max' => 255],
            [['attestation_type'], 'string', 'max' => 50],
            [['device_name'], 'string', 'max' => 255],

            [['credential_id'], 'unique'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'user_id' => 'User',
            'credential_id' => 'Credential ID',
            'public_key' => 'Public Key',
            'device_name' => 'Device',
            'last_used_at' => 'Last Used',
            'created_at' => 'Created At',
        ];
    }

    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        $now = date('Y-m-d H:i:s');

        if ($insert) {
            $this->created_at ??= $now;
            $this->enabled ??= true;
            $this->sign_count ??= 0;
            $this->backup_eligible ??= false;
            $this->backup_state ??= false;
        }

        return true;
    }

    public function touch(): bool
    {
        $this->last_used_at = date('Y-m-d H:i:s');

        return $this->save(false, ['last_used_at']);
    }

    public function updateSignCount(int $count): bool
    {
        $this->sign_count = $count;

        return $this->save(false, ['sign_count']);
    }

    public function enable(): bool
    {
        $this->enabled = true;

        return $this->save(false, ['enabled']);
    }

    public function disable(): bool
    {
        $this->enabled = false;

        return $this->save(false, ['enabled']);
    }

    public function isEnabled(): bool
    {
        return (bool)$this->enabled;
    }
}