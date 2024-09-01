<?php

namespace App\Http\Controllers\api;

use App\Models\Tag;
use Illuminate\Http\Request;
use App\Http\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class TagController extends Controller
{
    public function create()
    {
        // return view('tag.create');
    }

    public function store(Request $request)
    {
        $data = Validator::make($request->all(),
            [
                'name' => 'required|string|unique:tags',
            ], 
            #errors messages
            [
                'name.unique' => "This tag has been Created already.",
            ]
        );

        if ($data->fails()) {
            return ApiResponse::sendResponse(422, null, $data->messages()->all());
        }

        $tag = Tag::firstOrCreate([
            'name' => ucwords(strtolower($request['name'])),
        ]);

        return ApiResponse::sendResponse(201, 'Tag created Successfully', $tag);
    }

    public function index()
    {
        $tags = Tag::all()->sortBy('name');

        return ApiResponse::sendResponse(200, null, $tags);
    }

    public function edit($tagId)
    {
        $tag = Tag::find($tagId);

        if($tag){
            return ApiResponse::sendResponse(200, null, $tag);
        }
        
        return ApiResponse::sendResponse(404, 'Tag not found', []);
    }

    public function update(Request $request, $tagId)
    {
        $tag = Tag::find($tagId, function(){
            );
        });

        

        $data = Validator::make($request->all(),
            [
                'name' => 'required|string|unique:tags',
            ],

            [
                'name.unique' => "$tag->name has been created already."
            ]);

        if ($data->fails()) {
            return ApiResponse::sendResponse(422, null, $data->messages()->all());
        }

        $tag->name = ucwords(strtolower($request['name']));
        $tag->update();

        return ApiResponse::sendResponse(200, 'Tag updated successfully', $tag);
    }

    public function delete(int $tagId)
    {
        $tag = Tag::find($tagId);

        if (!$tag) {
            return ApiResponse::sendResponse(404, 'Tag not found', []);
        }

        $tag->delete();

        return ApiResponse::sendResponse(200, 'Tag deleted successfully', $tag);
    }
}
