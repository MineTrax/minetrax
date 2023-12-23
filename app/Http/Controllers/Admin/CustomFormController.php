<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomForm;
use App\Queries\Filters\FilterMultipleFields;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CustomFormController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', CustomForm::class);

        $perPage = request()->input('perPage', 10);
        if ($perPage > 100) {
            $perPage = 100;
        }

        $customForms = QueryBuilder::for(CustomForm::class)
            ->allowedFilters([
                'id',
                'title',
                'slug',
                'status',
                'is_only_auth',
                'is_only_staff',
                'is_notify_staff_on_submission',
                'created_at',
                'created_by',
                AllowedFilter::custom('q', new FilterMultipleFields(['id', 'title', 'slug', 'description'])),
            ])
            ->allowedSorts(['id', 'title', 'slug', 'status', 'is_only_auth', 'is_only_staff', 'is_notify_staff_on_submission', 'created_at'])
            ->defaultSort('-id')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Admin/CustomForm/IndexCustomForm', [
            'customForms' => $customForms,
            'filters' => request()->all(['perPage', 'sort', 'filter']),
        ]);
    }

    public function create()
    {
        $this->authorize('create', CustomForm::class);

        return Inertia::render('Admin/CustomForm/CreateCustomForm');
    }

    public function store(Request $request)
    {
        //
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(string $id)
    {
        //
    }
}
