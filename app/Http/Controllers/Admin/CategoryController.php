<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Display all categories grouped by parent.
     *
     * Intentionally does NOT filter by is_active — admin must see all
     * categories including inactive ones in order to manage them.
     * Children are eager-loaded with listing counts to avoid N+1.
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
}