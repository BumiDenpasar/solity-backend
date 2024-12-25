<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Notes;
use App\Http\Resources\NotesResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class NotesController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $userId = Auth::id();
        $notes = Notes::where('user_id', $userId)->get();

        $notes->transform(function ($note) {
            $note->img = url($note->img); 
            return $note;
        });

        return $this->sendResponse(NotesResource::collection($notes), 'Notes retrieved successfully.');
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();
        $input['user_id'] = Auth::id();

        
        $validator = Validator::make($input, [
            'img' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg',
            'title' => 'required',
            'body' => 'required'
        ]);
        
        
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        
        if ($request->hasFile('img')) {
            $imagePath = $request->file('img')->store('images', 'public');
            $input['img'] = "/storage/{$imagePath}";
        }
        
        $notes = Notes::create($input);

        return $this->sendResponse(new NotesResource($notes), 'Notes created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $notes = Notes::find($id);

        
        if (is_null($notes)) {
            return $this->sendError('Notes not found.');
        }

        if ($notes->user_id !== Auth::id()) {
            return $this->sendError('Unauthorized.', [], 403);
        }
        
        $notes->transform(function ($note) {
            $note->img = url($note->img); 
            return $note;
        });

        return $this->sendResponse(new NotesResource($notes), 'Notes retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) 
    {
        $notes = Notes::find($id); 

        if (is_null($notes)) {
            return $this->sendError('Notes not found.');
        }
        if ($notes->user_id !== Auth::id()) {
            return $this->sendError('Unauthorized.', [], 403);
        }

        $input = $request->all();

        $validator = Validator::make($input, [
            'img' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg', 
            'title' => 'sometimes', 
            'body' => 'sometimes' 
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        
        if ($request->hasFile('img')) {
            $imagePath = $request->file('img')->store('images', 'public');
            $notes->img = "/storage/{$imagePath}";
        }
        
        if (isset($input['title'])) {
            $notes->title = $input['title'];
        }
        if (isset($input['body'])) { 
            $notes->body = $input['body'];
        }

        $notes->save();

        return $this->sendResponse(new NotesResource($notes), 'Notes updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $notes = Notes::find($id);

        if (is_null($notes)) {
            return $this->sendError('Notes not found.');
        }

        if ($notes->user_id !== Auth::id()) {
            return $this->sendError('Unauthorized.', [], 403);
        }

        $notes->delete();

        return $this->sendResponse([], 'Notes deleted successfully.');
    }

}