<?php

namespace App\Auth;

use App\Models\User;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

class EmailHashUserProvider extends EloquentUserProvider
{
    /**
     * @param  array<string, mixed>  $credentials
     */
    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        if (
            empty($credentials)
            || (count($credentials) === 1 && array_key_exists('password', $credentials))
        ) {
            return null;
        }

        if (! isset($credentials['email']) || ! is_string($credentials['email'])) {
            return parent::retrieveByCredentials($credentials);
        }

        /** @var class-string<User> $modelClass */
        $modelClass = $this->model;

        $hash = $modelClass::hashEmail($credentials['email']);

        /** @var User|null */
        return (new $modelClass)->newQuery()->where('email_hash', $hash)->first();
    }
}
