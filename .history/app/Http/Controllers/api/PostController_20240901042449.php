<?php

namespace App\Http\Controllers\api;

use App\Models\Tag;
use App\Models\Post;
use Illuminate\Http\Request;
use App\Http\Helpers\ApiResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\ImageController;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    public function create()
    {
        // $tags = Tag::where('is_category', 0)->get();
        // $categoryTags = Tag::where('is_category', 1)->get();
        // return view('news.create', ['tags' => $tags, 'categoryTags' => $categoryTags]);
    }

    public function store(Request $request)
    {
        $data = Validator::make(
            $request->all(),
            [
                'title' => 'required|string|max:255',
                'body' => 'required|string',
                'cover_image'   => 'required|image',
                'pinned'    => 'required|boolean',
                'tags'    => 'required|array',
                'tags.*'  => 'exists:tags,id', // Ensure each tag exists in the tags table
            ], #errors
            [
                'body.required' => "Discription is required",
                'cover_image.image'      => "The image field must be an image.",
                'cover_image.mimes'      => "The image field must be an image with extention (jpeg, jpg, png, jfif, or svg).",
                'tags.required'    => 'You must select at least one tag.',
                'tags.*.exists'    => 'Selected tag does not exist.',
            ]
        );

        if ($data->fails()) {
            return ApiResponse::sendResponse(422, null, $data->messages()->all());
        }

        $post = Post::create([
            'cover_image' => ImageController::storeImage($request, 'cover_image', 'assets/img/posts'),
            'title' => $request['title'],
            'body' => $request['body'],
            'pinned' => $request['pinned'] ?? 0,
            'user_id' => Auth::user()->id,
        ]);

        $tags = $request->get('tags');
        if ($tags) {
            $post->tags()->attach($tags);
        }

        // Load the tags relationship to include them in the response
        $post->load('tags');

        // Return the response without the pivot attribute
        $post->tags->makeHidden('pivot');

        return ApiResponse::sendResponse(201, "Post created successfully.", $post);
    }

    public function index(Request $request)
    {
        $myPosts = Post::with('tags')->with('user')->where('user_id', Auth::user()->id)
            ->select('posts.*', 'users.name')
            ->orderBy('pinned', 'desc')
            ->get();

        return ApiResponse::sendResponse(200, null, $myPosts);
    }

    public function show($postId)
    {

        $post = Post::where('posts.id', $postId)
            ->join('users', 'news.user_id', '=', 'users.id')
            ->select('news.*', 'users.name')
            ->firstOr(function () {
                // return ErrorController::error404();
            });

        $tags = $post->tags;
        $relatedNews = [];
        $addedNewsIds = [];

        foreach ($tags as $tag) {
            foreach ($tag->post->where("id", "!=", $post->id) as $related) {
                if (!in_array($related->id, $addedNewsIds)) {
                    $relatedNews[] = $related;
                    $addedNewsIds[] = $related->id;
                }
            }
        }

        // return view('news.show', compact('news', 'relatedNews'));
    }

    public  function edit($postId)
    {

        $post = Post::where('posts.id', $postId)->with('user')->with('tags')
            ->firstOr(function () {
                // return ErrorController::error404();
            });
        $tags = Tag::where('is_category', 0)->get();
        $categoryTags = Tag::where('is_category', 1)->get();
        $post_tags = $post->tags->pluck('id')->toArray();

        // return  view("news.edit", compact('post', 'tags', 'categoryTags', 'post_tags'));
    }

    public function update(Request $request, $postId)
    {
        $post = Post::where('posts.id', $postId)
            ->join('users', 'posts.user_id', '=', 'users.id')

            ->firstOr($postId, function () {
                // return ErrorController::error404();
            });

        // if ((((auth()->user()->role == 'admin') && $post->role != 'hero') || (auth()->user()->role == 'hero' && Auth::user()->id == $news->user_id))) {
        $data = $request->validate(
            [
                'title' => 'required|string|max:255',
                'body' => 'required|string',
                'cover_image' => 'image|mimes:jpeg,jpg,png,jfif,svg|max:2048',
            ],
            #errors messages
            [
                'cover_image.image' => "The image field must be an image.",
                'cover_image.mimes' => "The image field must be an image with extension jpeg, jpg, png, jfif, or svg.",
            ]
        );

        if ($post->isDirty()) {
            $post->updated_at = now();
        }

        $updateData = [
            'title' => $data['title'],
            'body' => $data['body'],
        ];

        if ($request->hasfile('cover_image')) {
            File::delete(public_path('assets/img/posts/') . $post->cover_image);
            $updateData['cover_image'] = ImageController::storeImage($request, 'cover_image', 'assets/img/posts');
            $updateData['updated_at'] = now();
        }
        DB::table('news')->where('id', $postId)->update($updateData);

        $post->tags()->detach();
        $newTags = $request->get('tags', []);
        if ($newTags) {
            // Attach the unique tags to the $news model
            $post->tags()->sync($newTags);
        }

        return redirect()->route('news.show', $postId)->with('success', 'News updated successfully.');
        // } else {
        //     // return ErrorController::error401();
        // }
    }

    public  function delete($postId)
    {

        $post = Post::->get();
        $post = $post->where('id', $postId)->andWhere->first();

        if ($post) {
            File::delete(public_path('assets/img/posts/') . $post->cover_image);
            DB::table('posts')->where('id', $postId)->delete();
            return ApiResponse::sendResponse(200, "Your news deleted successfully", []);
        }

        return ApiResponse::sendResponse(403, "Unauthorized", []);
    }
}
