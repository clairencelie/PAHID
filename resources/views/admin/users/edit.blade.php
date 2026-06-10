@extends('layouts.app')
@section('title', 'Edit User')
@section('page-title', 'Edit User')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h6 class="mb-0 fw-semibold">Edit: {{ $user->name }}</h6></div>
            <div class="card-body">
                @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
                <form method="POST" action="{{ route('admin.users.update', $user) }}">
                    @csrf @method('PUT')
                    <div class="row g-3">
                        <div class="col-12"><label class="form-label fw-semibold">Nama</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required></div>
                        <div class="col-12"><label class="form-label fw-semibold">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required></div>
                        <div class="col-md-6"><label class="form-label fw-semibold">Role</label>
                        <select name="role" class="form-select" required>
                            @foreach(['admin','marketing','bc','underwriter','supervisor'] as $r)
                            <option value="{{ $r }}" {{ old('role', $user->role) === $r ? 'selected' : '' }}>{{ strtoupper($r) }}</option>
                            @endforeach
                        </select></div>
                        <div class="col-md-6"><label class="form-label fw-semibold">Cabang</label>
                        <select name="branch_id" class="form-select">
                            <option value="">Pilih cabang...</option>
                            @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ old('branch_id', $user->branch_id) == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                            @endforeach
                        </select></div>
                        <div class="col-md-6"><label class="form-label fw-semibold">Password Baru (kosongkan jika tidak diubah)</label>
                        <input type="password" name="password" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label fw-semibold">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" class="form-control"></div>
                        <div class="col-12">
                            <div class="form-check">
                                <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" {{ $user->is_active ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Aktif</label>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
