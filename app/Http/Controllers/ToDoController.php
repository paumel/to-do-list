<?php

namespace App\Http\Controllers;

use App\Http\Requests\ToDoFilterRequest;
use App\Http\Requests\ToDoRequest;
use App\Models\Category;
use App\Models\Tag;
use App\Models\ToDo;
use Illuminate\Http\Request;
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
     * @param \App\Http\Requests\ToDoFilterRequest $request
     * @return \Inertia\Response
     */
    public function index(ToDoFilterRequest $request): \Inertia\Response
    {
        $filters = $request->validated();

        $categories = Category::whereHas('toDos')->createdBy($request->user())->orderBy('title')->get();
        $tags = Tag::whereHas('toDos')->createdBy($request->user())->orderBy('name')->get();

        $toDos = ToDo::with(['category', 'tags'])
            ->createdBy($request->user())
            ->filter($filters)
            ->orderBy('due_date')
            ->get();

        return Inertia::render('ToDos/Index', [
            'to_dos' => $toDos,
            'categories' => $categories,
            'tags' => $tags,
            'filters' => $filters,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param \Illuminate\Http\Request $request
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
     * @param \App\Http\Requests\ToDoRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(ToDoRequest $request): \Illuminate\Http\RedirectResponse
    {
        $validatedData = $request->validated();

        $toDo = $request->user()->toDos()->create($validatedData);

        $toDo->attachTags($validatedData['tags'] ?? [], $request->user()->id);

        return to_route('to-dos.index')->with('success', 'To Do created successfully');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\ToDo $toDo
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
     * @param \App\Http\Requests\ToDoRequest $request
     * @param \App\Models\ToDo $toDo
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(ToDoRequest $request, ToDo $toDo): \Illuminate\Http\RedirectResponse
    {
        $validatedData = $request->validated();

        $toDo->update($validatedData);

        $toDo->attachTags($validatedData['tags'] ?? [], $request->user()->id);

        return to_route('to-dos.index')->with('success', 'To Do updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\ToDo $toDo
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(ToDo $toDo): \Illuminate\Http\RedirectResponse
    {
        $toDo->tags()->detach();

        $toDo->delete();

        return to_route('to-dos.index')->with('success', 'To Do deleted successfully');
    }
}
