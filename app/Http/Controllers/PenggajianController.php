<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\PegawaiModel;
use App\JabatanModel;
use App\GolonganModel;
use App\TunjanganModel;
use App\PenggajianModel;
use App\LemburPegawaiModel;
use App\KategoriLemburModel;
use App\TunjanganPegawaiModel;
use auth;
use Input;
use Validator;

class PenggajianController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $penggajians = PenggajianModel::paginate(3);
        return view('penggajian.index', compact('penggajians'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        $tunjangan_pegawais = TunjanganPegawaiModel::paginate(10);
        return view('penggajian.create', compact('tunjangans'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($penggajian);
        $penggajians = Input::all();
         // dd($where);
        $where = TunjanganPegawaiModel::where('id', $penggajians['tunjangan_pegawai_id'])->first();
        // dd($wherepenggajian);
        $wherepenggajian = PenggajianModel::where('tunjangan_pegawai_id', $where->id)->first();
        // dd($wheretunjangan);
        $wheretunjangan = TunjanganModel::where('id', $where->tunjangan_id)->first();
        // dd($pegawai);
        $wherepegawai = PegawaiModel::where('id', $where->pegawai_id)->first();
        // dd($kategotilembur);
        $wherekategorilembur = KategoriLemburModel::where('jabatan_id', $wherepegawai->jabatan_id)->where('golongan_id', $wherepegawai->golongan_id)->first();
        // dd($lemburpegawai);
        $wherelemburpegawai = LemburPegawaiModel::where('pegawai_id', $wherepegawai->id)->first();
        // dd($jabatan);
        $wherejabatan = JabatanModel::where('id', $wherepegawai->jabatan_id)->first();
        // dd($golongan);
        $wheregolongan = GolonganModel::where('id', $wherepegawai->golongan_id)->first();

        $penggajians = new PenggajianModel;
            if (isset($wherepenggajian)) {
                $error = true;
                $tunjangan_pegawais = TunjanganPegawaiModel::paginate(10);
                return view('penggajian.create', compact('tunjangan_pegawais', 'error'));
            }
            elseif (!isset($wherelemburpegawai)) {
                $nol = 0;
                $penggajians->jumlah_jam_lembur = $nol;
                $penggajians->jumlah_uang_lembur = $nol;
                $penggajians->gaji_pokok = ($wherejabatan->besaran_uang) + ($wheregolongan->besaran_uang);
                $penggajians->gaji_total = ($wheretunjangan->besaran_uang) + ($wherejabatan->besaran_uang) + ($wheregolongan->besaran_uang);
                $penggajians->tunjangan_pegawai_id = Input::get('tunjangan_pegawai_id');
                $penggajians->petugas_penerima = auth::user()->name;
                $penggajians->save();
            }
            elseif (!isset($wherelemburpegawai) || !isset($wherekategorilembur)) {
                $nol = 0;
                $penggajians->jumlah_jam_lembur = $nol;
                $penggajians->jumlah_uang_lembur = $nol;
                $penggajians->gaji_pokok = ($wherejabatan->besaran_uang) + ($wheregolongan->besaran_uang);
                $penggajians->gaji_total = ($wheretunjangan->besaran_uang) + ($wherejabatan->besaran_uang) + ($wheregolongan->besaran_uang);
                $penggajians->tunjangan_pegawai_id = Input::get('tunjangan_pegawai_id');
                $penggajians->petugas_penerima = auth::user()->name;
                $penggajians->save();
            }
            else {
                $penggajians->jumlah_jam_lembur = $wherelemburpegawai->jumlah_jam;
                $penggajians->jumlah_uang_lembur = ($wherelemburpegawai->jumlah_jam) * ($wherekategorilembur->besaran_uang);
                $penggajians->gaji_pokok = ($wherejabatan->besaran_uang) + ($wheregolongan->besaran_uang);
                $penggajians->gaji_total = ($wherelemburpegawai->jumlah_jam * $wherekategorilembur->besaran_uang) + ($wheretunjangan->besaran_uang) + ($wherejabatan->besaran_uang + $wheregolongan->besaran_uang);
                $penggajians->tanggal_pengambilan = date('d-m-y');
                $penggajians->tunjangan_pegawai_id = Input::get('tunjangan_pegawai_id');
                $penggajians->petugas_penerima = auth::user()->name;
                $penggajians->save();
            }
            return redirect('Penggajian');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        $penggajians = PenggajianModel::find($id);
        return view('penggajian.show', compact('penggajians'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
        $gaji = PenggajianModel::find($id);
        $penggajian = new PenggajianModel;
        $penggajian = array('status_pengambilan'=>1, 'tanggal_pengambilan'=>date('y-m-d'));
        PenggajianModel::where('id', $id)->update($penggajian);
        return redirect('Penggajian');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        PenggajianModel::find($id)->delete();
        return redirect('Penggajian');
    }
}
