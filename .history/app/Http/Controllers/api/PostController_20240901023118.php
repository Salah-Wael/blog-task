<?php

namespace App\Http\Controllers\api;

use App\Models\Tag;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\ImageController;

class PostController extends Controller
{
    public function create()
    {
        $tags = Tag::where('is_category', 0)->get();
        $categoryTags = Tag::where('is_category', 1)->get();

        // return view('news.create', ['tags' => $tags, 'categoryTags' => $categoryTags]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(
            [
                'title' => 'required|string|max:255',
                'body' => 'required|string',
                'cover_image'   => 'required|image|mimes:jpeg,jpg,png,jfif,svg|max:5048',
                'tags'    => 'required|array',
                'tags.*'  => 'exists:tags,id', // Ensure each tag exists in the tags table
            ],
            #errors
            [
                'body.required' => "Discription is required",
                'cover_image.image'      => "The image field must be an image.",
                'cover_image.mimes'      => "The image field must be an image with extention (jpeg, jpg, png, jfif, or svg).",
                'tags.required'    => 'You must select at least one tag.',
                'tags.*.exists'    => 'Selected tag does not exist.',
            ]
        );

        $post = new Post();
        $post->cover_image = ImageController::storeImage($request, 'cover_image', 'assets/img/posts');
        $post->title = $data['title'];
        $post->body = $data['body'];
        $post->user_id = Auth::user()->id;
        $post->save();

        $tags = $request->get('tags');
        if ($tags) {
            $post->tags()->attach($tags);
        }

        // return redirect()->route('news.index')->with('success', "News created successfully.");
    }

    public function index(Request $request)
    {

        if ($request->has('search')) {
            $search = $request->get('search');
            $newsQuery = Post::with('tags')
            ->whereHas('tags', function ($query) use ($search) {
                $query->where('tag', 'LIKE', "%$search%");
            })
                ->orWhereHas("user", function ($query) use ($search, $request) {
                    $query->whereRaw("name", "LIKE", "%$search%")
                    ->when($request->has('search') && $request->get('search') == 'admin', function ($query) use ($search) {
                        $query->orWhere("role", "LIKE", "%$search%");
                    });
                })
                ->orWhere("title", "LIKE", "%$search%")
                ->orWhere("body", "LIKE", "%$search%")
                ->join('users', 'news.user_id', '=', 'users.id')
                ->select('news.*', 'users.name', 'users.role')
                ->orderBy('news.created_at', 'desc')
                // ->get()
            ;
        } else {
            $newsQuery = Post::with('tags')
            ->join('users', 'news.user_id', '=', 'users.id')
            ->select('news.*', 'users.name', 'users.role')
            ->orderBy('news.created_at', 'desc')
                // ->get()
            ;
        }
        $post = $newsQuery->paginate(9);
        $tags = Tag::all();

        // return view('news.index', compact('post', 'tags'));
    }

    public function show($postId)
    {

        $post = Post::where('post.id', $postId)
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

        $post = Post::where('post.id', $postId)->with('user')->with('tags')
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
                $updateData['cover_image'] = ImageController::storeImage($request, 'cover_image', 'assets/img/news');
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

        $post = Post::where('id', $postId)
            ->firstOr(function () {
                // return ErrorController::error404();
            });


            File::delete(public_path('assets/img/posts/') . $post->cover_image);
            DB::table('posts')->where('id', $postId)->delete();

            // return redirect()->route('news.index')->with('success', "Your news deleted successfully");

    }
}
