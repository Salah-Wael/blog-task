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
                'content' => 'required|string',
                'cover_image'   => 'required|image|mimes:jpeg,jpg,png,jfif,svg|max:5048',
                'tags'    => 'required|array',
                'tags.*'  => 'exists:tags,id', // Ensure each tag exists in the tags table
            ],
            #errors
            [
                'content.required' => "Discription is required",
                'cover_image.image'      => "The image field must be an image.",
                'cover_image.mimes'      => "The image field must be an image with extention (jpeg, jpg, png, jfif, or svg).",
                'tags.required'    => 'You must select at least one tag.',
                'tags.*.exists'    => 'Selected tag does not exist.',
            ]
        );

        $news = new Post();
        $news->cover_image = ImageController::storeImage($request, 'cover_image', 'assets/img/posts');
        $news->title = $data['title'];
        $news->content = $data['content'];
        $news->user_id = Auth::user()->id;
        $news->save();

        $tags = $request->get('tags');
        if ($tags) {
            $news->tags()->attach($tags);
        }

        return redirect()->route('news.index')->with('success', "News created successfully.");
    }

    public function index(Request $request)
    {

        if ($request->has('search')) {
            $search = $request->get('search');
            $newsQuery = News::with('tags')
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
                ->orWhere("content", "LIKE", "%$search%")
                ->join('users', 'news.user_id', '=', 'users.id')
                ->select('news.*', 'users.name', 'users.role')
                ->orderBy('news.created_at', 'desc')
                // ->get()
            ;
        } else {
            $newsQuery = News::with('tags')
            ->join('users', 'news.user_id', '=', 'users.id')
            ->select('news.*', 'users.name', 'users.role')
            ->orderBy('news.created_at', 'desc')
                // ->get()
            ;
        }
        $news = $newsQuery->paginate(9);
        $tags = Tag::all();
        return view('news.index', compact('news', 'tags'));
    }

    public function show($postId)
    {

        $news = Post::where('news.id', $postId)
            ->join('users', 'news.user_id', '=', 'users.id')
            ->select('news.*', 'users.name', 'users.role')
            ->firstOr(function () {
                // return ErrorController::error404();
            });

        $tags = $news->tags;
        $relatedNews = [];
        $addedNewsIds = [];

        foreach ($tags as $tag) {
            foreach ($tag->news->where("id", "!=", $news->id) as $related) {
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

        $news = Post::where('post.id', $postId)->with('user')->with('tags')
        ->firstOr(function () {
            // return ErrorController::error404();
        });
        $tags = Tag::where('is_category', 0)->get();
        $categoryTags = Tag::where('is_category', 1)->get();
        $news_tags = $news->tags->pluck('id')->toArray();

        return  view("news.edit", compact('news', 'tags', 'categoryTags', 'news_tags'));
    }

    public function update(Request $request, $newsId)
    {
        $news = News::where('news.id', $newsId)
            ->join('users', 'news.user_id', '=', 'users.id')
            ->select('news.*', 'users.role')
            ->firstOr($newsId, function () {
                return ErrorController::error404();
            });

        if ((((auth()->user()->role == 'admin') && $news->role != 'hero') || (auth()->user()->role == 'hero' && Auth::user()->id == $news->user_id))) {
            $data = $request->validate(
                [
                    'title' => 'required|string|max:255',
                    'content' => 'required|string',
                    'cover_image' => 'image|mimes:jpeg,jpg,png,jfif,svg|max:2048',
                ],
                #errors messages
                [
                    'cover_image.image' => "The image field must be an image.",
                    'cover_image.mimes' => "The image field must be an image with extension jpeg, jpg, png, jfif, or svg.",
                ]
            );

            // if ($news->title != $request->title || $news->content != $request->content) {
            if ($news->isDirty()) {
                $news->updated_at = now();
            }

            $updateData = [
                'title' => $data['title'],
                'content' => $data['content'],
            ];

            if ($request->hasfile('cover_image')) {
                File::delete(public_path('assets/img/posts/') . $news->cover_image);
                $updateData['cover_image'] = ImageController::storeImage($request, 'cover_image', 'assets/img/news');
                $updateData['updated_at'] = now();
            }
            DB::table('news')->where('id', $newsId)->update($updateData);

            $news->tags()->detach();
            $newTags = $request->get('tags', []);
            if ($newTags) {
                // Attach the unique tags to the $news model
                $news->tags()->sync($newTags);
            }

            return redirect()->route('news.show', $newsId)->with('success', 'News updated successfully.');
        } else {
            // return ErrorController::error401();
        }
    }

    public  function delete($newsId)
    {

        $news = P::where('id', $newsId)
            ->firstOr(function () {
                // return ErrorController::error404();
            });

        if (((auth()->user()->role == 'admin') || (auth()->user()->role == 'salesman' && Auth::user()->id == $news->user_id))) {

            File::delete(public_path('assets/img/news/') . $news->cover_image);
            DB::table('news')->where('id', $newsId)->delete();

            return redirect()->route('news.index')->with('success', "Your news deleted successfully");
        } else {
            // return ErrorController::error401();
        }
    }
}
