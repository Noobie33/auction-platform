<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
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
     * Parent dropdown: active roots only — inactive categories should not
     * accept new children; subcategories cannot themselves be parents.
     */
    public function create(): View
    {
        $parents = Category::active()->roots()->orderBy('name')->get();

        return view('admin.categories.create', compact('parents'));
    }

    /**
     * Validate and persist a new category.
     *
     * Slug is always auto-generated. is_active defaults to true when the
     * checkbox is omitted (direct API calls, test POSTs without hidden field).
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

    /**
     * Show the edit form for an existing category.
     *
     * The current category is excluded from the parent dropdown:
     *  - scopeRoots() already excludes non-root categories, but a root
     *    category editing itself could still appear — the explicit where()
     *    removes it in all cases.
     */
    public function edit(Category $category): View
    {
        $parents = Category::active()
            ->roots()
            ->where('id', '!=', $category->id)
            ->orderBy('name')
            ->get();

        return view('admin.categories.edit', compact('category', 'parents'));
    }

    /**
     * Validate and persist changes to an existing category.
     *
     * Slug is always regenerated from the name. Passing $category->id to
     * generateSlug() excludes the current record from the uniqueness check
     * so updating with an unchanged name preserves the existing slug.
     *
     * is_active falls back to the current model value if the key is absent
     * (e.g. a test PATCH that omits the field) rather than hardcoding true,
     * since this is an update, not a create.
     */
    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $data = $request->validated();

        $category->update([
            'name'        => $data['name'],
            'slug'        => Category::generateSlug($data['name'], $category->id),
            'description' => $data['description'] ?? null,
            'parent_id'   => $data['parent_id'] ?? null,
            'is_active'   => (bool) ($data['is_active'] ?? $category->is_active),
        ]);

        return redirect()->route('admin.categories.index');
    }

    /**
     * Toggle the active status of a category.
     *
     * Active   → archived (is_active = false).
     * Inactive → restored (is_active = true).
     *
     * $wasActive is captured before update() mutates the model so the
     * flash message reflects the transition, not the resulting state.
     */
    public function updateStatus(Category $category): RedirectResponse
    {
        $wasActive = $category->is_active;

        $category->update([
            'is_active' => ! $wasActive,
        ]);

        $message = $wasActive
            ? "'{$category->name}' has been archived."
            : "'{$category->name}' has been restored.";

        return redirect()
            ->route('admin.categories.index')
            ->with('success', $message);
    }

     /**
     * Permanently delete a category — application-level guards enforced first.
     *
     * Guard 1 — children: listings.category_id uses restrictOnDelete(). A
     * parent category being deleted would orphan or throw a FK violation on
     * children rows. Block before any DB call.
     *
     * Guard 2 — listings: hasListings() calls withTrashed() because soft-deleted
     * listings still hold the category_id FK. restrictOnDelete() means the DB
     * would throw even for trashed listings. Block before any DB call.
     *
     * Both guards produce a user-facing error flash and redirect rather than
     * letting a raw FK exception surface.
     */
    public function destroy(Category $category): RedirectResponse
    {
        if ($category->children()->exists()) {
            return redirect()
                ->route('admin.categories.index')
                ->with('error', "'{$category->name}' cannot be deleted because it has subcategories. Remove or reassign them first.");
        }

        if ($category->hasListings()) {
            return redirect()
                ->route('admin.categories.index')
                ->with('error', "'{$category->name}' cannot be deleted because it has associated listings. Archive it instead.");
        }

        $name = $category->name;
        $category->delete();

        return redirect()
            ->route('admin.categories.index')
            ->with('success', "'{$name}' has been permanently deleted.");
    }
}