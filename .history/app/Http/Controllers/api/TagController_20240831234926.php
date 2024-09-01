<?php

namespace App\Http\Controllers\api;

use App\Models\Tag;
use Illuminate\Http\Request;
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
        $data = Validator::make($request->all(),[
                'name' => 'required|string|unique:tags',
        ],
        )
        $data = $request->validate(
            
            #error message
            [
                'name.unique' => "The tag has been Created already."
            ]
        );

        if ($data->fails()) {
            // return response(['errors' => $validator->errors()->all()], 422);
            return ApiResponse::sendResponse(422, 'Register Validation Error', $validator->messages()->all());
        }

        $tag = new Tag();
        $tag->name = ucwords(strtolower($data['name']));
        $tag->save();

        // return redirect()->route('tag.index');
    }

    public function index()
    {
        $tags = Tag::all()->sortByDesc('id');
        // return view('tag.index', compact('tags'));
    }

    public function edit(int $tagId)
    {
        $tag = Tag::find($tagId);

        if (!is_null($tag)) {
            // return view('tag.edit', compact('tag'));
        }

        // return ErrorController::error404();
    }

    public function update(Request $request, int $tagId)
    {
        $tag = Tag::findOrFail($tagId);

        $data = $request->validate(
            [
                'name' => 'required|string|unique:tags',
            ],
            [
                'name.unique' => "$tag->name has already been created."
            ]
        );

        $tag->name = ucwords(strtolower($data['name']));
        $tag->update();

        // return redirect()->route('tag.index');
    }

    public function delete(int $tagId)
    {
        $tag = Tag::findOrFail($tagId);
        $tag->delete();

        // return redirect()->route('tag.index');
    }
}
