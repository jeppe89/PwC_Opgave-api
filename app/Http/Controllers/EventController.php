<?php

namespace App\Http\Controllers;

use App\Event;
use App\Http\Resources\EventSerializer;
use App\Http\Resources\UserSerializer;
use Illuminate\Http\Request;
use Tobscure\JsonApi\Collection;
use Tobscure\JsonApi\Document;
use Tobscure\JsonApi\Resource;
use Validator;

class EventController extends Controller
{
    /**
     * Display a listing of the Event resource.
     *
     * @return \Tobscure\JsonApi\Document
     */
    public function index()
    {
        // Collect all the Events
        $events = Event::all();

        // Set up the response object
        $collection = (new Collection($events, new EventSerializer))
            ->with(['users'])->fields(['users' => ['name']]);
        $document = (new Document($collection))
            ->addLink('self', route('events.index'));

        return $document;
    }

    /**
     * Store a newly created Event resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Tobscure\JsonApi\Document
     */
    public function store(Request $request)
    {
        // Validate the request
        $request->validate($this->storeRules());

        // Create a new Event with the data from the request
        $event = new Event;
        $event->name = $request->input('data.attributes.name');
        $event->description = $request->input('data.attributes.description');
        $event->date = $request->input('data.attributes.date');

        $relationships = $request->input('data.relationships.users.data.*.id');
        $user_ids = null;

        // If any relationship were provided, store the id's in the $user_ids variable
        if ($relationships) {
            $user_ids = array_map('intval',
                array_unique($relationships)
            );
        }

        // Store the Event in the database
        $event->save();

        // If any relationships were provided attach them to the stored Event
        $event->users()->attach($user_ids);

        // Set up the response object
        $resource = (new Resource($event, new EventSerializer))
            ->with(['users']);

        return new Document($resource);
    }

    /**
     * Display the specified Event resource.
     *
     * @param  \App\Event  $event
     * @return \Tobscure\JsonApi\Document
     */
    public function show(Event $event)
    {
        // Set up the response object
        $resource = (new Resource($event, new EventSerializer))
            ->with(['users']);

        return new Document($resource);
    }

    /**
     * Update the specified Event resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Event  $event
     * @return \Tobscure\JsonApi\Document
     */
    public function update(Request $request, Event $event)
    {
        // Validate the request
        $request->validate($this->updateRules());

        // Update the Event with the requested data
        $event->update($request->input('data.attributes'));

        $relationships = $request->input('data.relationships.users.data.*.id');
        $user_ids = null;

        // If any relationship were provided, store the id's in the $user_ids variable
        if ($relationships) {
            $user_ids = array_map('intval',
                array_unique($relationships)
            );
        }

        // If any relationships were provided, update all the Event's relationships
        if ($user_ids) {
            $event->users()->sync($user_ids);
        }

        // Set up the response object
        $resource = (new Resource($event, new EventSerializer))
            ->with(['users']);

        return new Document($resource);
    }

    /**
     * Remove the specified Event resource from storage.
     *
     * @param  \App\Event $event
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(Event $event)
    {
        // Delete the requested Event
        $event->delete();

        // If successful return 204 No Content
        return response()->json([], 204);
    }

    /**
     * Display a listing of the subscribed Users for the specified Event resource.
     *
     * @param  \App\Event  $event
     * @return \Tobscure\JsonApi\Document
     */
    public function subscribers(Event $event)
    {
        // Get the subscribed Users for the specified Event
        $users = $event->users;

        // Set up the response object
        $collection = (new Collection($users, new UserSerializer));

        return new Document($collection);
    }

    /**
     * Subscribe User(s) for the specified Event resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Event  $event
     * @return \Tobscure\JsonApi\Document
     */
    public function subscribe(Request $request, Event $event)
    {
        // Validate the request
        $request->validate($this->subscribeRules());

        // Store the requested id's in the $user_ids variable
        $user_ids = array_map('intval',
            array_unique($request->input('data.*.id'))
        );

        // Check if the authenticated User have permission to subscribe multiple Users to an Event
        $permissionMultiple = auth()->user()->can('event-subscribe-multiple');

        /* If the authenticated User do not have permission to subscribe multiple Users to an Event,
         * and the id of the first object in the $user_ids array is not equal to the authenticated User's,
         * then respond with an 403 Forbidden error.
         */
        if ((auth()->user()->id !== $user_ids[0]) && !$permissionMultiple) {
            abort(403);
        }

        // Get the current subscribed User ids for the specified Event
        $attachedIds = $event->users()->whereIn('user_id', $user_ids)->pluck('id')->toArray();

        /*
         * To prevent having duplicates, collect the difference of the two arrays
         * (requested ids and the already attached ids) and attach the difference ids to the Event
         */
        $newIds = array_diff($user_ids, $attachedIds);
        $event->users()->attach($newIds);

        /*
         * Set up the response object:
         *
         * If the authenticated user does not have permission to subscribe multiple Users to an Event,
         * return a 200 OK response, instead of showing the subscribed users (this is only for users with the permission).
         */
        if (!$permissionMultiple) {
            return response()->json();
        }

        // Response object for users with permission to see the subscribed users.
        $users = $event->users;
        $collection = (new Collection($users, new UserSerializer));

        return new Document($collection);
    }

    /**
     * Unsubscribe User(s) for the specified Event resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Event  $event
     * @return \Tobscure\JsonApi\Document
     */
    public function unsubscribe(Request $request, Event $event)
    {
        // Validate the request
        $request->validate($this->subscribeRules());

        // Store the requested id's in the $user_ids variable
        $user_ids = array_map('intval',
            $request->input('data.*.id')
        );

        // Check if the authenticated User have permission to unsubscribe multiple Users from an Event
        $permissionMultiple = auth()->user()->can('event-subscribe-multiple');

        /* If the authenticated User do not have permission to unsubscribe multiple Users from an Event,
         * and the id of the first object in the $user_ids array is not equal to the authenticated User's,
         * then respond with an 403 Forbidden error.
         */
        if ((auth()->user()->id !== $user_ids[0]) && !$permissionMultiple) {
            abort(403);
        }

        // If the requested ids are indeed attached to the Event relationship, detach them.
        if ($event->users()->whereIn('user_id', $user_ids))
        {
            $event->users()->detach($user_ids);
        }

        /*
         * Set up the response object:
         *
         * If the authenticated user does not have permission to unsubscribe multiple Users from an Event,
         * return a 200 OK response, instead of showing the subscribed users (this is for only users with the permission).
         */
        if (!$permissionMultiple) {
            return response()->json();
        }

        // Response object for users with permission to see the subscribed users.
        $users = $event->users;
        $collection = (new Collection($users, new UserSerializer));

        return new Document($collection);
    }

    /**
     * Validation rules for storing an Event
     *
     * @return array
     */
    private function storeRules()
    {
        return [
            'data.type' => 'required|in:events',
            'data.attributes.name' => 'required|max:50|min:5',
            'data.attributes.description' => 'required|max:255',
            'data.attributes.date' => 'required|date',
            'data.relationships.users' => 'required_with:data.relationships',
            'data.relationships.users.data.*.type' => 'required_with:data.relationships|in:users',
            'data.relationships.users.data.*.id' => 'required_with:data.relationships|numeric'
        ];
    }

    /**
     * Validation rules for updating an Event
     *
     * @return array
     */
    private function updateRules()
    {
        return [
            'data.type' => 'required|in:events',
            'data.attributes.name' => 'max:30|min:5',
            'data.attributes.description' => 'min:1|max:255',
            'data.attributes.date' => 'date',
            'data.relationships.users' => 'required_with:data.relationships',
            'data.relationships.users.data.*.type' => 'required_with:data.relationships|in:users',
            'data.relationships.users.data.*.id' => 'required_with:data.relationships|numeric'
        ];
    }

    /**
     * Validation rules for subscribing and unsubscribing Users from an Event
     *
     * @return array
     */
    private function subscribeRules()
    {
        return [
            'data' => 'required',
            'data.*.type' => 'required_with:data|in:users',
            'data.*.id' => 'required_with:data|numeric'
        ];
    }
}
