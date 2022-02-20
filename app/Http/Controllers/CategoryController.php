<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
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
        $filters = $request->validate([
            'tag_id' => ['nullable', Rule::exists('tags', 'id')],
        ]);

        $categories = Category::with(['tags'])->createdBy($request->user())->filter($filters)->orderBy('title')->get();

        $tags = Tag::whereHas('categories')->createdBy($request->user())->orderBy('name')->get();

        return Inertia::render(
            'Categories/Index',
            compact('categories', 'tags', 'filters')
        );
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
     * @param \App\Http\Requests\CategoryRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(CategoryRequest $request): \Illuminate\Http\RedirectResponse
    {
        $validatedData = $request->validated();

        $category = $request->user()->categories()->create($validatedData);

        $category->attachTags($validatedData['tags'] ?? [], $request->user()->id);

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
     * @param \App\Http\Requests\CategoryRequest $request
     * @param \App\Models\Category $category
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(CategoryRequest $request, Category $category): \Illuminate\Http\RedirectResponse
    {
        $validatedData = $request->validated();

        $category->update($validatedData);

        $category->attachTags($validatedData['tags'] ?? [], $request->user()->id);

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
        $category->tags()->detach();

        $category->delete();

        return to_route('categories.index')->with('success', 'Category deleted successfully');
    }
}
