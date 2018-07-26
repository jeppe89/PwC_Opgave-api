<?php

namespace App\Http\Controllers;

use App\Http\Resources\EventSerializer;
use App\Http\Resources\UserSerializer;
use App\User;
use Illuminate\Http\Request;
use Tobscure\JsonApi\Collection;
use Tobscure\JsonApi\Document;
use Tobscure\JsonApi\Resource;
use Validator;
use JWTAuth;
use Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the User resource.
     *
     * @return \Tobscure\JsonApi\Document
     */
    public function index()
    {
        // Collect all the Users
        $users = User::all();

        // Set up the response object
        $collection = (new Collection($users, new UserSerializer))
            ->with(['events']);
        $document = (new Document($collection))
            ->addLink('self', route('users.index'));

        return $document;
    }

    /**
     * Store a newly created User resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Not implemented
    }

    /**
     * Display the specified User resource.
     *
     * @param  \App\User $user
     * @return \Tobscure\JsonApi\Document
     */
    public function show(User $user)
    {
        // Set up the response object
        $resource = (new Resource($user, new UserSerializer))
            ->with(['events']);

        return new Document($resource);
    }

    /**
     * Update the specified User resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\User $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        // Not implemented
    }

    /**
     * Remove the specified User resource from storage.
     *
     * @param  \App\User $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        // Not implemented
    }

    /**
     * Store a newly registered User resource in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        // Validate the request
        $request->validate($this->register_rules());

        // Create a new User with the data from the request
        $user = new User;
        $user->name = $request->input('data.attributes.name');
        $user->email = $request->input('data.attributes.email');
        $user->password = Hash::make($request->input('data.attributes.password'));
        $user->phone = $request->input('data.attributes.phone');

        // Store the User in the database
        $user->save();

        // Assign the default role
        $user->assignRole('Customer');

        return response()->json();
    }

    /**
     * Display a listing of the Events the specified User resource is subscribed to.
     *
     * @param  \App\User  $user
     * @return \Tobscure\JsonApi\Document
     */
    public function subscribed_to(User $user)
    {
        // Get the events that the specified User are subscribed to
        $events = $user->events;

        // Set up the response object
        $collection = (new Collection($events, new EventSerializer));

        return new Document($collection);
    }

    /**
     * Validation rules for registering a new User
     *
     * @return array
     */
    private function register_rules()
    {
        return [
            'data.type' => 'required|in:users',
            '*.*.email' => 'unique:users|email',
            'data.attributes.name' => 'required',
            'data.attributes.password' => 'required',
            'data.attributes.phone' => 'required'
        ];
    }
}
