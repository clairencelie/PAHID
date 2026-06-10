<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Entity;
use App\Models\EntityGroup;
use Illuminate\Http\Request;

class EntityGroupController extends Controller
{
    public function index()
    {
        $groups = EntityGroup::withCount('members')->orderBy('group_name')->paginate(20);
        return view('admin.entity-groups.index', compact('groups'));
    }

    public function create()
    {
        $entities = Entity::orderBy('legal_name')->get();
        return view('admin.entity-groups.create', compact('entities'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'group_name' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'members' => 'nullable|array',
            'members.*' => 'exists:entities,id',
            'relationship_types' => 'nullable|array',
        ]);

        $group = EntityGroup::create([
            'group_name' => $validated['group_name'],
            'normalized_group_name' => strtolower(trim($validated['group_name'])),
            'notes' => $validated['notes'] ?? null,
        ]);

        if (!empty($validated['members'])) {
            foreach ($validated['members'] as $entityId) {
                $relType = $validated['relationship_types'][$entityId] ?? 'SUBSIDIARY';
                $group->members()->attach($entityId, ['relationship_type' => $relType]);
            }
        }

        return redirect()->route('admin.entity-groups.show', $group)->with('success', 'Group berhasil dibuat.');
    }

    public function show(EntityGroup $entityGroup)
    {
        $entityGroup->load('members');
        return view('admin.entity-groups.show', compact('entityGroup'));
    }

    public function edit(EntityGroup $entityGroup)
    {
        $entityGroup->load('members');
        $entities = Entity::orderBy('legal_name')->get();
        return view('admin.entity-groups.edit', compact('entityGroup', 'entities'));
    }

    public function update(Request $request, EntityGroup $entityGroup)
    {
        $validated = $request->validate([
            'group_name' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'members' => 'nullable|array',
            'members.*' => 'exists:entities,id',
        ]);

        $entityGroup->update([
            'group_name' => $validated['group_name'],
            'normalized_group_name' => strtolower(trim($validated['group_name'])),
            'notes' => $validated['notes'] ?? null,
        ]);

        $syncData = [];
        foreach ($validated['members'] ?? [] as $entityId) {
            $syncData[$entityId] = ['relationship_type' => 'SUBSIDIARY'];
        }
        $entityGroup->members()->sync($syncData);

        return redirect()->route('admin.entity-groups.show', $entityGroup)->with('success', 'Group berhasil diperbarui.');
    }

    public function destroy(EntityGroup $entityGroup)
    {
        $entityGroup->delete();
        return redirect()->route('admin.entity-groups.index')->with('success', 'Group berhasil dihapus.');
    }
}
