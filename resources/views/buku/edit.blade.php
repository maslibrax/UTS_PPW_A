<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>Edit Buku</title></head><body>
<h1>Edit Data Buku</h1>
@if($errors->any())<div><strong>Periksa kembali input:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@if(session('gagal'))<p>{{ session('gagal') }}</p>@endif
<form method="POST" action="/buku/{{ $dataBuku['id'] }}">@csrf @method('PUT')
<p><label>Kode Buku: <input name="kode_buku" value="{{ old('kode_buku', $dataBuku['kode_buku']) }}" maxlength="10" required></label></p>
<p><label>Judul: <input name="judul" value="{{ old('judul', $dataBuku['judul']) }}" minlength="3" maxlength="100" required></label></p>
<p><label>ISBN: <input name="isbn" value="{{ old('isbn', $dataBuku['isbn']) }}" maxlength="13" required></label></p>
<p><label>Tahun Terbit: <input type="number" name="tahun_terbit" value="{{ old('tahun_terbit', $dataBuku['tahun_terbit']) }}" min="2000" max="2026" required></label></p>
<p><label>Kategori: <select name="kategori_buku_id" required>@foreach($kategoriBuku as $k)<option value="{{ $k['id'] }}" @selected(old('kategori_buku_id', $dataBuku['kategori_buku_id']) == $k['id'])>{{ $k['nama_kategori'] }}</option>@endforeach</select></label></p>
<button type="submit">Simpan Perubahan</button> <a href="/buku">Kembali</a></form></body></html>
