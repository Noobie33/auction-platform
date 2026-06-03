<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Display all categories grouped by parent.
     *
     * No is_active filter — admin must see all categories including inactive.
     */
    public function index(): View
    {
        $categories = Category::roots()
            ->with([
                'children' => fn ($q) => $q->withCount('listings')->orderBy('name'),
            ])
            ->withCount('listings')
            ->orderBy('name')
            ->get();

        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Show the create category form.
     *
     * Parent dropdown is active roots only — a child category cannot itself
     * be a parent, and inactive categories should not accept new children.
     */
    public function create(): View
    {
        $parents = Category::active()->roots()->orderBy('name')->get();

        return view('admin.categories.create', compact('parents'));
    }

    /**
     * Validate and persist a new category.
     *
     * Slug is always auto-generated — no user input for slug.
     * is_active defaults to true when the checkbox is not submitted.
     */
    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();

        Category::create([
            'name'        => $data['name'],
            'slug'        => Category::generateSlug($data['name']),
            'description' => $data['description'] ?? null,
            'parent_id'   => $data['parent_id'] ?? null,
            'is_active'   => (bool) ($data['is_active'] ?? true),
        ]);

        return redirect()->route('admin.categories.index');
    }
}