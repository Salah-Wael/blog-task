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
            [
                'name.unique' => "This tag has been Created already.",
            ]);

        if ($data->fails()) {
            return ApiResponse::sendResponse(422, '', $data->errors()->all());
        }

        $tag = Tag::create([
            'name' => ucwords(strtolower($data['name'])),
        ]);

        return ApiResponse::sendResponse(201, 'Tag created Successfully', $data);
    }

    public function index()
    {
        $tags = Tag::all()->sortBy('name');
        return ApiResponse::sendResponse(200, null, $tags);
    }

    public function edit($tagId)
    {
        $tag = Tag::findOr($tagId, function(){
            return ApiResponse::sendResponse(404, 'Tag not found', []);
        });

        return ApiResponse::sendResponse(200, null, $tag);
    }

    public function update(Request $request, $tagId)
    {
        $tag = Tag::findOrFail($tagId);

        $data = Validator::make($request->all(),
            [
                'name' => 'required|string|unique:tags',
            ],
            [
                'name.unique' => "$tag->name has already been created."
            ]);

        $tag->name = ucwords(strtolower($data['name']));
        $tag->update();

        return ApiResponse::sendResponse(200, 'Tag updated successfully', $tag);
    }

    public function delete(int $tagId)
    {
        $tag = Tag::findOrFail($tagId);
        $tag->delete();

        // return redirect()->route('tag.index');
    }
}
