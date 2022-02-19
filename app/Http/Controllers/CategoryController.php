<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class CategoryController extends Controller
{
    /**
     * Create the controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizeResource(Category::class);
    }

    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Inertia\Response
     */
    public function index(Request $request): \Inertia\Response
    {
        $validatedData = $request->validate([
            'tag_id' => ['nullable', Rule::exists('tags', 'id')],
        ]);

        $categoriesQuery = Category::with(['tags'])->createdBy($request->user());
        $tags = Tag::whereHas('categories')->createdBy($request->user())->orderBy('name')->get();

        if ($request->has('tag_id')) {
            $categoriesQuery->whereHas('tags', function ($query) use ($request) {
                $query->where('tags.id', $request->get('tag_id'));
            });
        }

        return Inertia::render('Categories/Index', [
            'tags' => $tags,
            'categories' => $categoriesQuery->orderBy('title')->get(),
            'filters' => $validatedData,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Inertia\Response
     */
    public function create(): \Inertia\Response
    {
        return Inertia::render('Categories/Create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validatedData = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'max_to_dos' => ['required', 'numeric', 'min:1'],
            'tags' => ['array', 'min:0'],
            'tags.*' => ['required', 'string', 'max:255'],
        ]);

        $category = $request->user()->categories()->create($validatedData);

        foreach ($validatedData['tags'] ?? [] as $tag) {
            $tag = Tag::firstOrCreate([
                'name' => $tag,
                'user_id' => $request->user()->id
            ]);
            $category->tags()->attach($tag);
        }

        return to_route('categories.index')->with('success', 'Category created successfully');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Category $category
     * @return \Inertia\Response
     */
    public function edit(Category $category): \Inertia\Response
    {
        $category->load(['tags']);

        return Inertia::render('Categories/Edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Category $category
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Category $category): \Illuminate\Http\RedirectResponse
    {
        $validatedData = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'max_to_dos' => ['required', 'numeric', 'min:1'],
            'tags' => ['array', 'min:0'],
            'tags.*' => ['required', 'string', 'max:255'],
        ]);

        $category->update($validatedData);

        $newTags = [];
        foreach ($validatedData['tags'] ?? [] as $tag) {
            $tag = Tag::firstOrCreate([
                'name' => $tag,
                'user_id' => $request->user()->id
            ]);
            $newTags[] = $tag->id;
        }
        $category->tags()->sync($newTags);


        return to_route('categories.index')->with('success', 'Category updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Category $category
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Category $category): \Illuminate\Http\RedirectResponse
    {
        $category->delete();

        return to_route('categories.index')->with('success', 'Category deleted successfully');
    }
}
