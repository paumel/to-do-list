<?php

namespace App\Http\Controllers;

use App\Models\ToDo;
use Illuminate\Http\Request;

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

        $toDo->update(['completed' => !$toDo->completed]);

        return back();
    }
}
