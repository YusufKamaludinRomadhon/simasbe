<?php

namespace App\Http\Controllers;

use App\Imports\SuratmasukImport;
use App\Models\Suratmasuk;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class SuratmasukController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = Suratmasuk::all();
        return view('suratmasuk.index', compact('query'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('suratmasuk.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate(
            [
                'tglsurat' => 'required',
                'tglmasuk' => 'required',
                'nosurat' => 'required',
                'pengirim' => 'required',
                'isi' => 'required',
                'file' => 'required|mimes:pdf|max:10000',
            ],
            [
                'tglsurat.required' => 'Kolom Tanggal Surat tidak boleh kosong',
                'tglmasuk.required' => 'Kolom Tanggal Masuk tidak boleh kosong',
                'nosurat.required' => 'Kolom No. Surat tidak boleh kosong',
                'pengirim.required' => 'Kolom Pengirim tidak boleh kosong',
                'isi.required' => 'Kolom Isi Ringkas tidak boleh kosong',
                'file.required' => 'Silahkan pilih file surat',
                'file.mimes' => 'Tipe File harus PDF',
                'file.max' => 'Ukuran file tidak boleh dari 10 MB',
            ],
        );
        $filesurat = $request->file('file');
        $new_name = 'SRM' . date('Ymd') . '.' . $filesurat->extension();
        $filesurat->move('./suratmasukfile/' . $new_name);
        $simpan = Suratmasuk::create([
            'tgl_surat' => $request->tglsurat,
            'tgl_masuk' => $request->tglmasuk,
            'no_surat' => $request->nosurat,
            'pengirim' => $request->pengirim,
            'ringkasan' => $request->isi,
            'file_surat' => $new_name,
        ]);
        $simpan->save();
        return redirect()
            ->route('suratmasuks.index')
            ->with('success', 'Data Surat Masuk sudah berhasil disimpan');
    }

    /**
     * Display the specified resource.
     */
    public function show(Suratmasuk $suratmasuk)
    {
        return view('suratmasuk.detail', compact('suratmasuk'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Suratmasuk $suratmasuk)
    {
        return view('suratmasuk.edit', compact('suratmasuk'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Suratmasuk $suratmasuk)
    {
        $request->validate(
            [
                'tglsurat' => 'required',
                'tglmasuk' => 'required',
                'nosurat' => 'required',
                'pengirim' => 'required',
                'isi' => 'required',
                'file' => 'mimes:pdf|max:10000',
            ],
            [
                'tglsurat.required' => 'Kolom Tanggal Surat tidak boleh kosong',
                'tglmasuk.required' => 'Kolom Tanggal Masuk tidak boleh kosong',
                'nosurat.required' => 'Kolom No. Surat tidak boleh kosong',
                'pengirim.required' => 'Kolom Pengirim tidak boleh kosong',
                'isi.required' => 'Kolom Isi Ringkas tidak boleh kosong',
                'file.required' => 'Silahkan pilih file surat',
                'file.mimes' => 'Tipe File harus PDF',
                'file.max' => 'Ukuran file tidak boleh dari 10 MB',
            ],
        );
        $filesurat = $request->file('file');
        $suratmasuk->no_surat = $request->nosurat;
        $suratmasuk->tgl_surat = $request->tglsurat;
        $suratmasuk->tgl_masuk = $request->tglmasuk;
        $suratmasuk->pengirim = $request->pengirim;
        $suratmasuk->ringkasan = $request->isi;
        if ($filesurat != '') {
            $new_name = rand() . '.' . $filesurat->extension();
            $filesurat->move('./suratmasukfile', $new_name);
            $suratmasuk->file_surat = $new_name;
        }
        $suratmasuk->save();
        return redirect()
            ->route('suratmasuks.index')
            ->with('success', 'Ubah Surat Masuk sudah berhasil disimpan');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Suratmasuk $suratmasuk)
    {
        $suratmasuk->delete();
        return redirect()
            ->route('suratmasuks.index')
            ->with('success', 'Data Surat Masuk sudah berhasil dihapus');
    }

    public function import()
    {
        return view('suratmasuk.import');
    }

    public function importproses(Request $request)
    {
        $this->validate($request, [
            'file' => 'required|mimes:csv,xls,xlsx',
        ]);
        Excel::import(new SuratmasukImport(), $request->file('file'));
        return redirect()
            ->route('suratmasuks.index')
            ->with('success', 'Import data berhasil');
    }
    public function exportpdf()
    {
        $query = Suratmasuk::all();
        $pdf = app()->make('dompdf.wrapper');
        $pdf->loadView('suratmasuk.suratmasuk_pdf', ['query' => $query]);
        return $pdf->download('laporan-suratmasuk.pdf');
    }
}
