<?php


namespace App\Http\Resources;

use Tobscure\JsonApi\AbstractSerializer;
use Tobscure\JsonApi\Collection;
use Tobscure\JsonApi\Relationship;

class EventSerializer extends AbstractSerializer
{
    protected $type = 'events';

    public function getAttributes($event, array $fields = null)
    {
        return [
            'name' => $event->name,
            'description' => $event->description,
            'date' => $event->date
        ];
    }

    public function users($event)
    {
        $element = new Collection($event->users, new UserSerializer);

        $relationship = (new Relationship($element))
            ->addLink('self', route('events.relationships.users', ['event' => $event->id]))
            ->addLink('related', route('events.users', ['event' => $event->id]));

        return $relationship;
    }

    public function getLinks($event)
    {
        return [ 'self' => route('events.show', ['event' => $event->id]) ];
    }

}