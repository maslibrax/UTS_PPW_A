<!DOCTYPE html>
<html lang="id"><head><meta charset="UTF-8"><title>Data Buku Perpustakaan</title></head><body>
<h1>Data Buku Perpustakaan</h1>
@if(session('sukses'))<p>{{ session('sukses') }}</p>@endif
@if(session('gagal'))<p>{{ session('gagal') }}</p>@endif
@if($errors->any())<div><strong>Periksa kembali input:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<h2>Tambah Buku</h2>
<form method="POST" action="/buku">@csrf
<p><label>Kode Buku (10 karakter): <input name="kode_buku" value="{{ old('kode_buku') }}" maxlength="10" required></label></p>
<p><label>Judul: <input name="judul" value="{{ old('judul') }}" minlength="3" maxlength="100" required></label></p>
<p><label>ISBN (13 digit): <input name="isbn" value="{{ old('isbn') }}" maxlength="13" required></label></p>
<p><label>Tahun Terbit: <input type="number" name="tahun_terbit" value="{{ old('tahun_terbit') }}" min="2000" max="2026" required></label></p>
<p><label>Kategori: <select name="kategori_buku_id" required><option value="">-- Pilih kategori --</option>@foreach($kategoriBuku as $k)<option value="{{ $k['id'] }}" @selected(old('kategori_buku_id') == $k['id'])>{{ $k['nama_kategori'] }}</option>@endforeach</select></label></p>
<button type="submit">Simpan Buku</button></form>
<h2>Pencarian dan Filter</h2>
<form method="GET" action="/buku"><label>Kata kunci (kode, judul, ISBN): <input name="cari" value="{{ $kata }}"></label>
<label>Kategori: <select name="kategori_buku_id"><option value="">Semua kategori</option>@foreach($kategoriBuku as $k)<option value="{{ $k['id'] }}" @selected((string)$kategori === (string)$k['id'])>{{ $k['nama_kategori'] }}</option>@endforeach</select></label>
<button type="submit">Cari / Filter</button> <a href="/buku">Reset</a></form>
<h2>Daftar Buku</h2><p>Tabel master yang digunakan: <strong>kategori_buku</strong>.</p>
<table border="1" cellpadding="5"><thead><tr><th>No.</th><th>Kode</th><th>Judul</th><th>ISBN</th><th>Tahun</th><th>Kategori</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
@forelse($buku as $i => $b)<tr><td>{{ $i + 1 }}</td><td>{{ $b['kode_buku'] }}</td><td>{{ $b['judul'] }}</td><td>{{ $b['isbn'] }}</td><td>{{ $b['tahun_terbit'] }}</td><td>{{ $b['nama_kategori'] }}</td><td>@if((int)$b['tahun_terbit'] >= 2023) Baru @else Lama @endif ({{ statusTahunBuku($b['tahun_terbit']) }})</td><td><a href="/buku/{{ $b['id'] }}/edit">Edit</a><form method="POST" action="/buku/{{ $b['id'] }}" onsubmit="return confirm('Hapus buku ini?')">@csrf @method('DELETE')<button type="submit">Hapus</button></form></td></tr>@empty<tr><td colspan="8">Belum ada data yang cocok.</td></tr>@endforelse
</tbody></table>
<p>Jumlah data tampil: {{ count($buku) }}</p>
</body></html>
