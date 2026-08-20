<?php

namespace App\Support\Hashing;

use Illuminate\Hashing\BcryptHasher as BaseBcryptHasher;
use RuntimeException;

class CustomBcryptHasher extends BaseBcryptHasher
{
    /**
     * Check the given plain value against a hash.
     *
     * @param  string  $value
     * @param  string  $hashedValue
     * @param  array  $options
     * @return bool
     */
    public function check($value, $hashedValue, array $options = [])
    {
        if (is_null($hashedValue) || strlen($hashedValue) === 0) {
            return false;
        }

        if (is_string($hashedValue) && str_starts_with($hashedValue, '$2b$')) {
            $hashedValue = '$2y$' . substr($hashedValue, 4);
        }

        if ($this->info($hashedValue)['algoName'] !== 'bcrypt') {
            return false;
        }

        return password_verify($value, $hashedValue);
    }

    /**
     * Get information about the given hashed value.
     *
     * @param  string  $hashedValue
     * @return array
     */
    public function info($hashedValue)
    {
        if (is_string($hashedValue) && str_starts_with($hashedValue, '$2b$')) {
            $hashedValue = '$2y$' . substr($hashedValue, 4);
        }

        return parent::info($hashedValue);
    }

    /**
     * Check if the given hash has been hashed using the given options.
     *
     * @param  string  $hashedValue
     * @param  array  $options
     * @return bool
     */
    public function needsRehash($hashedValue, array $options = [])
    {
        if (is_string($hashedValue) && str_starts_with($hashedValue, '$2b$')) {
            return true;
        }

        return parent::needsRehash($hashedValue, $options);
    }
}
