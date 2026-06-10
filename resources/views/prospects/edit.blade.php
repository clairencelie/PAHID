@extends('layouts.app')
@section('title', 'Edit Prospect')
@section('page-title', 'Edit Prospect')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0 fw-semibold">Edit Prospect: {{ $prospect->prospect_name }}</h6>
            </div>
            <div class="card-body">
                @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
                @endif

                <form method="POST" action="{{ route('prospects.update', $prospect) }}">
                    @csrf @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Nama Prospect <span class="text-danger">*</span></label>
                            <input type="text" name="prospect_name" class="form-control" value="{{ old('prospect_name', $prospect->prospect_name) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Tipe Input <span class="text-danger">*</span></label>
                            <select name="input_type" class="form-select" required>
                                @foreach(['LEGAL_ENTITY' => 'Legal Entity (PT/CV)', 'BRAND' => 'Brand / Merek', 'GROUP' => 'Group / Holding', 'PROPERTY' => 'Property / Gedung', 'SUBSIDIARY' => 'Subsidiary', 'UNKNOWN' => 'Tidak Diketahui'] as $val => $label)
                                <option value="{{ $val }}" {{ old('input_type', $prospect->input_type) === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nama Legal Entity</label>
                            <input type="text" name="legal_entity_name" class="form-control" value="{{ old('legal_entity_name', $prospect->legal_entity_name) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nama Brand</label>
                            <input type="text" name="brand_name" class="form-control" value="{{ old('brand_name', $prospect->brand_name) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nama Group</label>
                            <input type="text" name="group_name" class="form-control" value="{{ old('group_name', $prospect->group_name) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kota</label>
                            <input type="text" name="city" class="form-control" value="{{ old('city', $prospect->city) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Alamat</label>
                            <textarea name="address" class="form-control" rows="2">{{ old('address', $prospect->address) }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Jenis Usaha / LOB</label>
                            <input type="text" name="occupation" class="form-control" value="{{ old('occupation', $prospect->occupation) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Estimasi Premi (Rp)</label>
                            <input type="number" name="estimated_premium" class="form-control" value="{{ old('estimated_premium', $prospect->estimated_premium) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nama PIC Client</label>
                            <input type="text" name="client_pic_name" class="form-control" value="{{ old('client_pic_name', $prospect->client_pic_name) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Jabatan PIC Client</label>
                            <input type="text" name="client_pic_position" class="form-control" value="{{ old('client_pic_position', $prospect->client_pic_position) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Catatan</label>
                            <textarea name="notes" class="form-control" rows="2">{{ old('notes', $prospect->notes) }}</textarea>
                        </div>
                    </div>
                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        <a href="{{ route('prospects.show', $prospect) }}" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
