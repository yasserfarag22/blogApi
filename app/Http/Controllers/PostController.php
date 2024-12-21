<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostRequest;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{

    public function index(Request $request)
    {
        $posts = Post::with('author')
            ->when($request->category, fn($query) => $query->where('category', $request->category))
            ->when($request->author, fn($query) => $query->where('author_id', $request->author))
            ->when($request->start_date && $request->end_date, fn($query) =>
            $query->whereBetween('created_at', [$request->start_date, $request->end_date])
            )
            ->paginate(10);

        return response()->json($posts);
    }


    public function store(PostRequest $request)
    {
        $post = Post::create([
            'title' => $request->title,
            'content' => $request->content,
            'category' => $request->category,
            'author_id' => auth()->id(),
        ]);

        return response()->json($post, 201);
    }


    public function show($id)
    {
        $post = Post::with('author')->findOrFail($id);

        $this->authorizePostAccess($post);

        return response()->json($post);
    }

    public function update(PostRequest $request, $id)
    {
        $post = Post::findOrFail($id);

        $this->authorizePostAccess($post);

        $post->update($request->validated());

        return response()->json($post);
    }

    public function destroy($id)
    {
        $post = Post::findOrFail($id);

        $this->authorizePostAccess($post);

        $post->delete();

        return response()->json(['message' => 'Post deleted']);
    }

    private function authorizePostAccess(Post $post)
    {
        if (!Auth::user()->isAdmin() && Auth::id() !== $post->author_id) {
            abort(403, 'Unauthorized');
        }
    }
}
