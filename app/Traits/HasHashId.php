<?php

namespace App\Traits;

use App\Support\HashId;

trait HasHashId
{
    public function initializeHasHashId()
    {
        $this->appends[] = 'hashid';
    }

    public function getRouteKey()
    {
        return HashId::encode($this->getKey());
    }

    public function resolveRouteBinding($value, $field = null)
    {
        if ($field === null) {
            $id = HashId::decodeOrFail($value);

            return $this->where('id', $id)->firstOrFail();
        }

        return parent::resolveRouteBinding($value, $field);
    }

    public function resolveChildRouteBinding($childType, $value, $field)
    {
        if ($field === null) {
            $id = HashId::decodeOrFail($value);

            return parent::resolveChildRouteBinding($childType, $id, 'id');
        }

        return parent::resolveChildRouteBinding($childType, $value, $field);
    }

    public function getHashidAttribute(): string
    {
        return HashId::encode($this->getKey());
    }
}
