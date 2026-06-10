@extends('layouts.app')
@section('title', 'Tambah User')
@section('page-title', 'Tambah User')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h6 class="mb-0 fw-semibold">Form User</h6></div>
            <div class="card-body">
                @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
                <form method="POST" action="{{ route('admin.users.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12"><label class="form-label fw-semibold">Nama <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required></div>
                        <div class="col-12"><label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required></div>
                        <div class="col-md-6"><label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                        <select name="role" class="form-select" required>
                            @foreach(['admin','marketing','bc','underwriter','supervisor'] as $r)
                            <option value="{{ $r }}" {{ old('role') === $r ? 'selected' : '' }}>{{ strtoupper($r) }}</option>
                            @endforeach
                        </select></div>
                        <div class="col-md-6"><label class="form-label fw-semibold">Cabang</label>
                        <select name="branch_id" class="form-select">
                            <option value="">Pilih cabang...</option>
                            @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ old('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                            @endforeach
                        </select></div>
                        <div class="col-md-6"><label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label fw-semibold">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" class="form-control" required></div>
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
