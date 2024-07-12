<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\V1\ListingResource;
use App\Http\Resources\V1\ListingCollection;
use App\Http\Controllers\Api\V1\BaseController;

class ListingController extends BaseController
{
    /**
     * Display a listing of the resource.
     * 
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $listings = Listing::all();
        return $this
            ->sendResponse(
                new ListingCollection($listings),
                'Listings retrieved successfully.'
            );
    }

    /**
     * Display the specified resource.
     * 
     * @return \Illuminate\Http\Response
     */
    public function show(Listing $listing)
    {
        $listing = Listing::find($listing->id);

        if (is_null($listing)) {
            return $this->sendError('Listing not found.');
        }

        return $this->sendResponse(new ListingResource($listing), 'Listing retrieved successfully.');
    }

    public function getUserIdByToken($token)
    {
        $personalAccessToken = PersonalAccessToken::findToken($token);

        if (!$personalAccessToken) {
            return $this->sendError('Token not found.', ['error' => 'Unauthorized'], 401);
        }

        return $personalAccessToken->tokenable->id;
    }

    /**
     * Store a newly created resource in storage.
     * 
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $userId = $this->getUserIdByToken($request->bearerToken());

        $formFields = Validator::make($request->all(), [
            'title' => 'required',
            'tags' => 'required',
            'company' => ['required', Rule::unique('listings', 'company')],
            'location' => 'required',
            'email' => ['required', 'email'],
            'website' => 'required',
            'description' => 'required',
            'logo' => 'file:jpeg,png,jpg,gif,svg|max:3072',
        ]);

        if ($formFields->fails()) {
            return $this->sendError('Validation Error.', $formFields->errors());
        }

        $validatedListingDataToStore = $formFields->validated();

        if ($request->hasFile('logo')) {
            $validatedListingDataToStore['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $validatedListingDataToStore['user_id'] = $userId;

        $createdListing = Listing::create($validatedListingDataToStore);

        return $this->sendResponse([
            'listing' => new ListingResource($createdListing),
        ], 'Listing created successfully.');
    }

    public function update(Request $request, string $id)
    {
        $userId = $this->getUserIdByToken($request->bearerToken());

        $listing = Listing::find($id);

        if (is_null($listing)) {
            return $this->sendError('Listing not found.', ['error' => 'Listing not found.'], 404);
        }
        
        $formFields = Validator::make($request->all(), [
            'title' => 'required',
            'tags' => 'required',
            'company' => ['required'],
            'location' => 'required',
            'email' => ['required', 'email'],
            'website' => 'required',
            'description' => 'required',
            'logo' => 'file:jpeg,png,jpg,gif,svg|max:3072',
        ]);

        if ($formFields->fails()) {
            return $this->sendError('Validation Error.', $formFields->errors());
        }

        $validatedListingDataToUpdate = $formFields->validated();

        
        if ($request->hasFile('logo')) {
            $validatedListingDataToUpdate['logo'] = $request->file('logo')->store('logos', 'public');
        }
        
        $listing->update($validatedListingDataToUpdate);
        
        return $this->sendResponse([
            'listing' => new ListingResource($listing),
        ], 'Listing updated successfully.');
    }
}
