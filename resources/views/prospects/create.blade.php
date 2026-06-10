@extends('layouts.app')
@section('title', 'Buat Prospect')
@section('page-title', 'Buat Prospect Baru')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0 fw-semibold">Form Registrasi Prospect A&H</h6>
            </div>
            <div class="card-body">
                @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
                @endif

                <form method="POST" action="{{ route('prospects.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Nama Prospect <span class="text-danger">*</span></label>
                            <input type="text" name="prospect_name" class="form-control" value="{{ old('prospect_name') }}" required
                                placeholder="Nama perusahaan / brand / group">
                            <small class="text-muted">Masukkan nama seperti yang akan disubmit ke underwriting</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Tipe Input <span class="text-danger">*</span></label>
                            <select name="input_type" class="form-select" required>
                                <option value="">Pilih...</option>
                                @foreach(['LEGAL_ENTITY' => 'Legal Entity (PT/CV)', 'BRAND' => 'Brand / Merek', 'GROUP' => 'Group / Holding', 'PROPERTY' => 'Property / Gedung', 'SUBSIDIARY' => 'Subsidiary', 'UNKNOWN' => 'Tidak Diketahui'] as $val => $label)
                                <option value="{{ $val }}" {{ old('input_type') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nama Legal Entity</label>
                            <input type="text" name="legal_entity_name" class="form-control" value="{{ old('legal_entity_name') }}"
                                placeholder="PT / CV ...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nama Brand</label>
                            <input type="text" name="brand_name" class="form-control" value="{{ old('brand_name') }}"
                                placeholder="Nama brand jika berbeda dari legal entity">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nama Group</label>
                            <input type="text" name="group_name" class="form-control" value="{{ old('group_name') }}"
                                placeholder="Nama group / holding (jika ada)">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kota</label>
                            <input type="text" name="city" class="form-control" value="{{ old('city') }}" placeholder="Jakarta, Surabaya, dll">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Alamat</label>
                            <textarea name="address" class="form-control" rows="2" placeholder="Alamat lengkap perusahaan">{{ old('address') }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Jenis Usaha / LOB</label>
                            <input type="text" name="occupation" class="form-control" value="{{ old('occupation') }}"
                                placeholder="Hotel, Manufacturing, Logistics, dll">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Estimasi Premi (Rp)</label>
                            <input type="number" name="estimated_premium" class="form-control" value="{{ old('estimated_premium') }}"
                                placeholder="500000000">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nama PIC Client</label>
                            <input type="text" name="client_pic_name" class="form-control" value="{{ old('client_pic_name') }}"
                                placeholder="Nama contact person client">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Jabatan PIC Client</label>
                            <input type="text" name="client_pic_position" class="form-control" value="{{ old('client_pic_position') }}"
                                placeholder="HR Director, Finance Manager, dll">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Catatan</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Catatan tambahan">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Simpan sebagai Draft
                        </button>
                        <a href="{{ route('prospects.index') }}" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
