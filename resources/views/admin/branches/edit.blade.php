@extends('layouts.app')
@section('title', 'Edit Cabang')
@section('page-title', 'Edit Cabang')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h6 class="mb-0 fw-semibold">Edit: {{ $branch->name }}</h6></div>
            <div class="card-body">
                @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
                <form method="POST" action="{{ route('admin.branches.update', $branch) }}">
                    @csrf @method('PUT')
                    <div class="mb-3"><label class="form-label fw-semibold">Nama</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $branch->name) }}" required></div>
                    <div class="mb-3"><label class="form-label fw-semibold">Kode</label>
                    <input type="text" name="code" class="form-control" value="{{ old('code', $branch->code) }}" required></div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" {{ $branch->is_active ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Aktif</label>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <a href="{{ route('admin.branches.index') }}" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
