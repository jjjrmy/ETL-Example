<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Lead\IndexRequest;
use App\Models\Lead;

class LeadController extends Controller
{
    /**
     * Display a listing of Leads.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(IndexRequest $request)
    {
        return Lead::paginate($request->query('per_page', 5));
    }
}
