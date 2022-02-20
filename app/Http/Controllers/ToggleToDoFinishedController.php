<?php

namespace App\Http\Controllers;

use App\Models\ToDo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ToggleToDoFinishedController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\ToDo $toDo
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request, ToDo $toDo): \Illuminate\Http\RedirectResponse
    {
        abort_if($request->user()->cannot('update', $toDo), 403);

        $validatedData = $request->validate([
            'category_id' => ['nullable', Rule::exists('categories', 'id')],
            'tag_id' => ['nullable', Rule::exists('tags', 'id')],
            'finished' => ['nullable', 'boolean'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ]);

        $toDo->update(['finished' => !$toDo->finished]);

        return back();
    }
}
