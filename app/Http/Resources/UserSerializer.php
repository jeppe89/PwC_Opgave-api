<?php
/**
 * Created by PhpStorm.
 * User: Jeppe
 * Date: 20-07-2018
 * Time: 20:33
 */

namespace App\Http\Resources;


use Tobscure\JsonApi\AbstractSerializer;
use Tobscure\JsonApi\Collection;
use Tobscure\JsonApi\Relationship;

class UserSerializer extends AbstractSerializer
{
    protected $type = 'users';

    public function getAttributes($user, array $fields = null)
    {
        return [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'roles' => $user->getRoleNames()
        ];
    }

    public function events($user)
    {
        $element = new Collection($user->events, new EventSerializer);

        $relationship = (new Relationship($element))
            ->addLink('self', route('users.relationships.events', ['user' => $user->id]))
            ->addLink('related', route('users.events', ['user' => $user->id]));

        return $relationship;
    }

    public function getLinks($user)
    {
        return [ 'self' => route('users.show', ['user' => $user->id]) ];
    }
}