@extends('layouts.app')
@section('title', 'Tambah Entity')
@section('page-title', 'Tambah Legal Entity')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header"><h6 class="mb-0 fw-semibold">Form Legal Entity</h6></div>
            <div class="card-body">
                @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
                <form method="POST" action="{{ route('admin.entities.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12"><label class="form-label fw-semibold">Nama Legal <span class="text-danger">*</span></label>
                        <input type="text" name="legal_name" class="form-control" value="{{ old('legal_name') }}" required placeholder="PT ... / CV ..."></div>
                        <div class="col-md-6"><label class="form-label">NPWP</label>
                        <input type="text" name="npwp" class="form-control" value="{{ old('npwp') }}"></div>
                        <div class="col-md-6"><label class="form-label">NIB</label>
                        <input type="text" name="nib" class="form-control" value="{{ old('nib') }}"></div>
                        <div class="col-md-6"><label class="form-label">Kota</label>
                        <input type="text" name="city" class="form-control" value="{{ old('city') }}"></div>
                        <div class="col-md-6"><label class="form-label">Jenis Usaha</label>
                        <input type="text" name="occupation" class="form-control" value="{{ old('occupation') }}"></div>
                        <div class="col-12"><label class="form-label">Alamat</label>
                        <textarea name="address" class="form-control" rows="2">{{ old('address') }}</textarea></div>
                        <div class="col-12"><label class="form-label">Catatan</label>
                        <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea></div>
                    </div>
                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <a href="{{ route('admin.entities.index') }}" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
