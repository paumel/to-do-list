<?php

namespace App\Http\Requests;

use App\Models\Category;
use App\Rules\CategoryHasFreeSpaces;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ToDoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $toDo = request()->to_do;

        $minDate = $toDo && $toDo->due_date
            ? (Carbon::parse($toDo->due_date)->gte(Carbon::today()) ? 'today' : $toDo->due_date)
            : 'today';

        $userCategoryIds = Category::createdBy(request()->user())->pluck('id')->toArray();

        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'due_date' => ['nullable', 'date', 'after_or_equal:' . $minDate],
            'category_id' => ['nullable', Rule::in($userCategoryIds), new CategoryHasFreeSpaces()],
            'tags' => ['array', 'min:0'],
            'tags.*' => ['required', 'string', 'max:255'],
        ];
    }
}
