<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
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
            ->sendResponse(new ListingCollection($listings), 
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
        
        if (is_null($listing))
        {
            return $this->sendError('Listing not found.');
        }

        return $this->sendResponse(new ListingResource($listing), 'Listing retrieved successfully.');
    }
}
