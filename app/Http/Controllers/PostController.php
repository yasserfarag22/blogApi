<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{

    public function index(Request $request)
    {
        $posts = Post::with('author')->when($request->category, function($query) use ($request) {
            return $query->where('category', $request->category);
        })->when($request->author, function($query) use ($request) {
            return $query->where('author_id', $request->author);
        })->when($request->start_date && $request->end_date, function($query) use ($request) {
            return $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        })->paginate(10);

        return response()->json($posts);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'required|in:Technology,Lifestyle,Education',
        ]);

        $post = new Post();
        $post->title = $request->title;
        $post->content = $request->content;
        $post->category = $request->category;
        $post->author_id = auth()->user()->id;
        $post->save();

        return response()->json($post, 201);
    }

    public function show($id)
    {
        $post = Post::with('author')->find($id);
        return response()->json($post);
    }

    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);
        if ($post->author_id != auth()->user()->id && auth()->user()->role != 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $post->update($request->only('title', 'content', 'category'));
        return response()->json($post);
    }

    public function destroy($id)
    {
        $post = Post::findOrFail($id);
        if ($post->author_id != auth()->user()->id && auth()->user()->role != 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $post->delete();
        return response()->json(['message' => 'Post deleted']);
    }
}
