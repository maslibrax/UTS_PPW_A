<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

if (! function_exists('statusTahunBuku')) {
    function statusTahunBuku($tahun): string
    {
        return (int) $tahun >= 2023 ? 'Baru' : 'Lama';
    }
}

Route::get('/', fn () => redirect('/buku'));

Route::get('/buku', function (Request $request) {
    try {
        $pdo = DB::connection()->getPdo();
        $kata = trim(strip_tags((string) $request->query('cari', '')));
        $kategori = $request->query('kategori_buku_id', '');

        $sql = 'SELECT b.id, b.kode_buku, b.judul, b.isbn, b.tahun_terbit,
                       b.kategori_buku_id, k.nama_kategori
                FROM buku b INNER JOIN kategori_buku k ON k.id = b.kategori_buku_id';
        $where = [];
        $params = [];
        if ($kata !== '') {
            $where[] = '(b.kode_buku LIKE :kode OR b.judul LIKE :judul OR b.isbn LIKE :isbn)';
            $like = '%' . $kata . '%';
            $params[':kode'] = $like;
            $params[':judul'] = $like;
            $params[':isbn'] = $like;
        }
        if ($kategori !== '' && ctype_digit((string) $kategori)) {
            $where[] = 'b.kategori_buku_id = :kategori';
            $params[':kategori'] = (int) $kategori;
        }
        if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
        $sql .= ' ORDER BY b.id DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $buku = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmtKategori = $pdo->prepare('SELECT id, nama_kategori FROM kategori_buku ORDER BY nama_kategori');
        $stmtKategori->execute();
        $kategoriBuku = $stmtKategori->fetchAll(PDO::FETCH_ASSOC);
        return view('buku.index', compact('buku', 'kategoriBuku', 'kata', 'kategori'));
    } catch (Throwable $e) {
        report($e);
        return response()->view('buku.error', ['pesan' => 'Data belum dapat ditampilkan. Periksa koneksi basis data.'], 500);
    }
});

Route::post('/buku', function (Request $request) {
    $request->merge([
        'kode_buku' => strtoupper(trim(strip_tags((string) $request->input('kode_buku', '')))),
        'judul' => trim(strip_tags((string) $request->input('judul', ''))),
        'isbn' => trim(strip_tags((string) $request->input('isbn', ''))),
    ]);
    $data = $request->validate([
        'kode_buku' => ['required', 'regex:/^[A-Za-z0-9]{10}$/', 'unique:buku,kode_buku'],
        'judul' => ['required', 'string', 'min:3', 'max:100'],
        'isbn' => ['required', 'regex:/^[0-9]{13}$/', 'unique:buku,isbn'],
        'tahun_terbit' => ['required', 'integer', 'between:2000,2026'],
        'kategori_buku_id' => ['required', 'integer', 'exists:kategori_buku,id'],
    ], [
        'kode_buku.regex' => 'Kode buku harus tepat 10 karakter alfanumerik.',
        'isbn.regex' => 'ISBN harus tepat 13 digit angka.',
        'tahun_terbit.between' => 'Tahun terbit harus antara 2000 sampai 2026.',
    ]);
    try {
        $pdo = DB::connection()->getPdo();
        $stmt = $pdo->prepare('INSERT INTO buku (kode_buku, judul, isbn, tahun_terbit, kategori_buku_id, created_at, updated_at) VALUES (:kode, :judul, :isbn, :tahun, :kategori, NOW(), NOW())');
        $stmt->execute([':kode'=>$data['kode_buku'], ':judul'=>$data['judul'], ':isbn'=>$data['isbn'], ':tahun'=>$data['tahun_terbit'], ':kategori'=>$data['kategori_buku_id']]);
        return redirect('/buku')->with('sukses', 'Data buku berhasil ditambahkan.');
    } catch (Throwable $e) {
        report($e);
        return back()->withInput()->with('gagal', 'Data belum dapat disimpan. Periksa data dan koneksi basis data.');
    }
});

Route::get('/buku/{id}/edit', function (string $id) {
    if (!ctype_digit($id)) abort(404);
    try {
        $pdo = DB::connection()->getPdo();
        $stmt = $pdo->prepare('SELECT id, kode_buku, judul, isbn, tahun_terbit, kategori_buku_id FROM buku WHERE id = :id');
        $stmt->execute([':id'=>(int)$id]);
        $dataBuku = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$dataBuku) abort(404);
        $stmt = $pdo->prepare('SELECT id, nama_kategori FROM kategori_buku ORDER BY nama_kategori');
        $stmt->execute();
        $kategoriBuku = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return view('buku.edit', compact('dataBuku', 'kategoriBuku'));
    } catch (Throwable $e) {
        report($e);
        return response('Data edit belum dapat ditampilkan. Periksa koneksi basis data.', 500);
    }
});

Route::put('/buku/{id}', function (Request $request, string $id) {
    if (!ctype_digit($id)) abort(404);
    $idInt = (int) $id;
    $request->merge([
        'kode_buku' => strtoupper(trim(strip_tags((string) $request->input('kode_buku', '')))),
        'judul' => trim(strip_tags((string) $request->input('judul', ''))),
        'isbn' => trim(strip_tags((string) $request->input('isbn', ''))),
    ]);
    $data = $request->validate([
        'kode_buku' => ['required', 'regex:/^[A-Za-z0-9]{10}$/', Rule::unique('buku', 'kode_buku')->ignore($idInt)],
        'judul' => ['required', 'string', 'min:3', 'max:100'],
        'isbn' => ['required', 'regex:/^[0-9]{13}$/', Rule::unique('buku', 'isbn')->ignore($idInt)],
        'tahun_terbit' => ['required', 'integer', 'between:2000,2026'],
        'kategori_buku_id' => ['required', 'integer', 'exists:kategori_buku,id'],
    ], ['kode_buku.regex'=>'Kode buku harus tepat 10 karakter alfanumerik.', 'isbn.regex'=>'ISBN harus tepat 13 digit angka.']);
    try {
        $pdo = DB::connection()->getPdo();
        $stmt = $pdo->prepare('UPDATE buku SET kode_buku=:kode, judul=:judul, isbn=:isbn, tahun_terbit=:tahun, kategori_buku_id=:kategori, updated_at=NOW() WHERE id=:id');
        $stmt->execute([':kode'=>$data['kode_buku'], ':judul'=>$data['judul'], ':isbn'=>$data['isbn'], ':tahun'=>$data['tahun_terbit'], ':kategori'=>$data['kategori_buku_id'], ':id'=>$idInt]);
        return redirect('/buku')->with('sukses', 'Data buku berhasil diperbarui.');
    } catch (Throwable $e) {
        report($e);
        return back()->withInput()->with('gagal', 'Data belum dapat diperbarui. Periksa data dan koneksi basis data.');
    }
});

Route::delete('/buku/{id}', function (string $id) {
    if (!ctype_digit($id)) abort(404);
    try {
        $pdo = DB::connection()->getPdo();
        $stmt = $pdo->prepare('DELETE FROM buku WHERE id = :id');
        $stmt->execute([':id'=>(int)$id]);
        if ($stmt->rowCount() === 0) return redirect('/buku')->with('gagal', 'Data buku tidak ditemukan.');
        return redirect('/buku')->with('sukses', 'Data buku berhasil dihapus.');
    } catch (Throwable $e) {
        report($e);
        return redirect('/buku')->with('gagal', 'Data belum dapat dihapus. Periksa koneksi basis data.');
    }
});
