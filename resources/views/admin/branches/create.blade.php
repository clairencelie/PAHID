@extends('layouts.app')
@section('title', 'Tambah Cabang')
@section('page-title', 'Tambah Cabang')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h6 class="mb-0 fw-semibold">Form Cabang</h6></div>
            <div class="card-body">
                @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
                <form method="POST" action="{{ route('admin.branches.store') }}">
                    @csrf
                    <div class="mb-3"><label class="form-label fw-semibold">Nama Cabang <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required></div>
                    <div class="mb-3"><label class="form-label fw-semibold">Kode <span class="text-danger">*</span></label>
                    <input type="text" name="code" class="form-control" value="{{ old('code') }}" required placeholder="SBY, JKT, dll" style="text-transform:uppercase;"></div>
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
