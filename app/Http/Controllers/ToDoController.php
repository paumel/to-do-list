<?php

namespace App\Http\Controllers;

use App\Models\ToDo;
use Carbon\Carbon;
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Inertia\Response
     */
    public function index(Request $request): \Inertia\Response
    {
        return Inertia::render('ToDos/Index', [
            'to_dos' => ToDo::createdBy($request->user())->latest()->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Inertia\Response
     */
    public function create(): \Inertia\Response
    {
        return Inertia::render('ToDos/Create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validatedData = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'due_date' => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        $request->user()->toDos()->create($validatedData);

        return to_route('to-dos.index')->with('success', 'To Do created successfully');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ToDo  $toDo
     * @return \Inertia\Response
     */
    public function edit(ToDo $toDo): \Inertia\Response
    {
        return Inertia::render('ToDos/Edit', compact('toDo'));
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

        $validatedData = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'due_date' => ['nullable', 'date', 'after_or_equal:' . $minDate],
        ]);

        $toDo->update($validatedData);

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
