<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Tag;
use App\Models\ToDo;
use App\Rules\CategoryHasFreeSpaces;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ToDoController extends Controller
{
    /**
     * Create the controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizeResource(ToDo::class);
    }

    /**
     * Display a listing of to dos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Inertia\Response
     */
    public function index(Request $request): \Inertia\Response
    {
        $validatedData = $request->validate([
            'category_id' => ['nullable', Rule::exists('categories', 'id')],
            'tag_id' => ['nullable', Rule::exists('tags', 'id')],
            'finished' => ['nullable', 'boolean'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ]);

        $categories = Category::whereHas('toDos')->createdBy($request->user())->orderBy('title')->get();
        $tags = Tag::whereHas('toDos')->createdBy($request->user())->orderBy('name')->get();

        $toDosQuery = ToDo::with(['category', 'tags'])->createdBy($request->user());

        if ($request->has('tag_id')) {
            $toDosQuery->whereHas('tags', function ($query) use ($request) {
                $query->where('tags.id', $request->get('tag_id'));
            });
        }

        if ($request->has('category_id')) {
            $toDosQuery->whereHas('category', function ($query) use ($request) {
                $query->where('categories.id', $request->get('category_id'));
            });
        }

        if ($request->has('finished')) {
            $toDosQuery->where('finished', $request->get('finished'));
        }

        if ($request->has('start_date')) {
            $toDosQuery->whereDate('due_date', '>=' ,$request->get('start_date'))->whereNotNull('due_date');
        }

        if ($request->has('end_date')) {
            $toDosQuery->whereDate('due_date', '<=' ,$request->get('end_date'))->whereNotNull('due_date');
        }

        return Inertia::render('ToDos/Index', [
            'to_dos' => $toDosQuery->latest()->get(),
            'categories' => $categories,
            'tags' => $tags,
            'filters' => $validatedData,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Inertia\Response
     */
    public function create(Request $request): \Inertia\Response
    {
        $categories = Category::createdBy($request->user())->orderBy('title')->get();

        return Inertia::render('ToDos/Create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $userCategoryIds = Category::createdBy($request->user())->pluck('id')->toArray();

        $validatedData = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'due_date' => ['nullable', 'date', 'after_or_equal:today'],
            'category_id' => ['nullable', Rule::in($userCategoryIds), new CategoryHasFreeSpaces()],
            'tags' => ['array', 'min:0'],
            'tags.*' => ['required', 'string', 'max:255'],
        ]);

        $toDo = $request->user()->toDos()->create($validatedData);

        foreach ($validatedData['tags'] ?? [] as $tag) {
            $tag = Tag::firstOrCreate([
                'name' => $tag,
                'user_id' => $request->user()->id
            ]);
            $toDo->tags()->attach($tag);
        }

        return to_route('to-dos.index')->with('success', 'To Do created successfully');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ToDo  $toDo
     * @return \Inertia\Response
     */
    public function edit(Request $request, ToDo $toDo): \Inertia\Response
    {
        $toDo->load(['tags']);

        $categories = Category::createdBy($request->user())->orderBy('title')->get();

        return Inertia::render('ToDos/Edit', compact('toDo', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ToDo  $toDo
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, ToDo $toDo): \Illuminate\Http\RedirectResponse
    {
        $minDate = $toDo->due_date ? (Carbon::parse($toDo->due_date)->gte(Carbon::today()) ? 'today' : $toDo->due_date) : 'today';
        $userCategoryIds = Category::createdBy($request->user())->pluck('id')->toArray();

        $validatedData = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'due_date' => ['nullable', 'date', 'after_or_equal:' . $minDate],
            'category_id' => ['nullable', Rule::in($userCategoryIds), new CategoryHasFreeSpaces()],
            'tags' => ['array', 'min:0'],
            'tags.*' => ['required', 'string', 'max:255'],
        ]);

        $toDo->update($validatedData);

        $newTags = [];
        foreach ($validatedData['tags'] ?? [] as $tag) {
            $tag = Tag::firstOrCreate([
                'name' => $tag,
                'user_id' => $request->user()->id
            ]);
            $newTags[] = $tag->id;
        }
        $toDo->tags()->sync($newTags);

        return to_route('to-dos.index')->with('success', 'To Do updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ToDo  $toDo
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(ToDo $toDo): \Illuminate\Http\RedirectResponse
    {
        $toDo->delete();

        return to_route('to-dos.index')->with('success', 'To Do deleted successfully');
    }
}
