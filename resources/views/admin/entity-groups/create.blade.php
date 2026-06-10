@extends('layouts.app')
@section('title', 'Tambah Group')
@section('page-title', 'Tambah Entity Group')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header"><h6 class="mb-0 fw-semibold">Form Entity Group</h6></div>
            <div class="card-body">
                @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
                <form method="POST" action="{{ route('admin.entity-groups.store') }}">
                    @csrf
                    <div class="mb-3"><label class="form-label fw-semibold">Nama Group <span class="text-danger">*</span></label>
                    <input type="text" name="group_name" class="form-control" value="{{ old('group_name') }}" required></div>
                    <div class="mb-3"><label class="form-label">Catatan</label>
                    <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea></div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Member Entities</label>
                        <div class="border rounded p-2" style="max-height:200px;overflow-y:auto;">
                            @foreach($entities as $entity)
                            <div class="form-check">
                                <input type="checkbox" name="members[]" value="{{ $entity->id }}"
                                    id="e{{ $entity->id }}" class="form-check-input"
                                    {{ in_array($entity->id, old('members', [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="e{{ $entity->id }}">{{ $entity->legal_name }}</label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <a href="{{ route('admin.entity-groups.index') }}" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
