<?php

declare(strict_types=1);

namespace Afernandes\Yii2Passkey\Models\ActiveRecord;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int|string $user_id
 * @property string $credential_id
 * @property string $source
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
            [['user_id', 'credential_id', 'source'], 'required'],
            [['user_id'], 'safe'],
            [['created_at', 'last_used_at'], 'safe'],
            [['credential_id'], 'string', 'max' => 1024],
            [['device_name'], 'string', 'max' => 255],
            [['source'], 'string'],
            [['credential_id'], 'unique'],
            [
                ['enabled'],
                'in',
                'range' => [
                    self::STATUS_DISABLED,
                    self::STATUS_ENABLED,
                ]
            ],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'user_id'       => 'User',
            'credential_id' => 'Credential ID',
            'source'        => 'Source',
            'device_name'   => 'Device',
            'last_used_at'  => 'Last Used',
            'created_at'    => 'Created At',
        ];
    }

    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if ($insert) {
            $this->created_at ??= (new \DateTimeImmutable())->format('Y-m-d H:i:s');
            $this->enabled ??= self::STATUS_ENABLED;
        }

        return true;
    }

    public function rename(string $deviceName): bool
    {
        $this->device_name = $deviceName;

        return $this->save(false, ['device_name']);
    }

    public function touch(): bool
    {
        $this->last_used_at = (new \DateTimeImmutable())->format('Y-m-d H:i:s');

        return $this->save(false, ['last_used_at']);
    }

    public function enable(): bool
    {
        $this->enabled = self::STATUS_ENABLED;

        return $this->save(false, ['enabled']);
    }

    public function disable(): bool
    {
        $this->enabled = self::STATUS_DISABLED;

        return $this->save(false, ['enabled']);
    }

    public function isEnabled(): bool
    {
        return $this->enabled === self::STATUS_ENABLED;
    }
}