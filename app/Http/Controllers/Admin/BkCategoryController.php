<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BkCategory;
use App\Models\BkRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BkCategoryController extends Controller
{
    public function index(): View
    {
        $categories = BkCategory::orderBy('record_type')->orderBy('sort_order')->paginate(20);

        return view('admin.bk-categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.bk-categories.form', ['category' => new BkCategory]);
    }

    public function store(Request $request): RedirectResponse
    {
        BkCategory::create($this->data($request));

        return redirect()->route('admin.bk-categories.index')->with('success', 'Kategori dibuat.');
    }

    public function edit(BkCategory $bkCategory): View
    {
        return view('admin.bk-categories.form', ['category' => $bkCategory]);
    }

    public function update(Request $request, BkCategory $bkCategory): RedirectResponse
    {
        $bkCategory->update($this->data($request, $bkCategory));

        return redirect()->route('admin.bk-categories.index')->with('success', 'Kategori diperbarui.');
    }

    public function destroy(BkCategory $bkCategory): RedirectResponse
    {
        $bkCategory->update(['is_active' => false]);

        return back()->with('success', 'Kategori dinonaktifkan.');
    }

    private function data(Request $request, ?BkCategory $category = null): array
    {
        return $request->validate(['name' => ['required', 'string', 'max:255', Rule::unique('bk_categories')->where(fn ($q) => $q->where('record_type', $request->record_type))->ignore($category?->id)], 'record_type' => ['required', Rule::in(BkRecord::TYPES)], 'default_severity' => ['nullable', Rule::in(BkRecord::SEVERITIES)], 'sort_order' => 'required|integer|min:0|max:65535', 'is_active' => 'boolean']) + ['is_active' => $request->boolean('is_active')];
    }
}
