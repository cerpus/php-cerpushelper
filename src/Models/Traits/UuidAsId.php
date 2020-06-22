<?php


namespace Cerpus\Helper\Models\Traits;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Primary key as UUID
 * Trait UuidAsId
 * @package Cerpus\Helper\Models\Traits
 */
trait UuidAsId
{
    protected static function bootUuidAsId()
    {
        static::creating(function ($model) {
            $model->keyType = 'string';
            $model->incrementing = false;

            /** @var Model $model */
            $primaryKey = $model->getKeyName();
            if ($model->isFillable($primaryKey) === false || empty($model->{$primaryKey})) {
                $model->{$primaryKey} = (string)Str::uuid();
            }
        });
    }

    public function getIncrementing()
    {
        return false;
    }

    public function getKeyType()
    {
        return 'string';
    }
}
