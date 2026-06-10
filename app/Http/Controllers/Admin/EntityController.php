<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Entity;
use App\Models\EntityAlias;
use Illuminate\Http\Request;

class EntityController extends Controller
{
    public function index()
    {
        $entities = Entity::withCount('aliases')->orderBy('legal_name')->paginate(20);
        return view('admin.entities.index', compact('entities'));
    }

    public function create()
    {
        return view('admin.entities.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'legal_name' => 'required|string|max:255',
            'npwp' => 'nullable|string|max:30',
            'nib' => 'nullable|string|max:30',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'occupation' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'notes' => 'nullable|string',
        ]);

        $validated['normalized_name'] = strtolower(preg_replace('/\s+/', ' ', trim($validated['legal_name'])));

        $entity = Entity::create($validated);

        return redirect()->route('admin.entities.show', $entity)->with('success', 'Entity berhasil dibuat.');
    }

    public function show(Entity $entity)
    {
        $entity->load('aliases', 'groups');
        return view('admin.entities.show', compact('entity'));
    }

    public function edit(Entity $entity)
    {
        return view('admin.entities.edit', compact('entity'));
    }

    public function update(Request $request, Entity $entity)
    {
        $validated = $request->validate([
            'legal_name' => 'required|string|max:255',
            'npwp' => 'nullable|string|max:30',
            'nib' => 'nullable|string|max:30',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'occupation' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'notes' => 'nullable|string',
        ]);

        $validated['normalized_name'] = strtolower(preg_replace('/\s+/', ' ', trim($validated['legal_name'])));

        $entity->update($validated);
        return redirect()->route('admin.entities.show', $entity)->with('success', 'Entity berhasil diperbarui.');
    }

    public function destroy(Entity $entity)
    {
        $entity->delete();
        return redirect()->route('admin.entities.index')->with('success', 'Entity berhasil dihapus.');
    }
}
