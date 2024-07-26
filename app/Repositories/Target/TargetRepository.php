<?php
namespace App\Repositories\Target;

use App\Models\Target;
use App\Repositories\Pay\PayRepository;
use Illuminate\Http\Request;
use App\Response\ResponseArray;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Repositories\Target\TargetInterface;


class TargetRepository implements TargetInterface
{
    protected $responseArray, $payRepository;
    public function __construct(ResponseArray $responseArray, PayRepository $payRepository)
    {
        $this->responseArray = $responseArray;
        $this->payRepository = $payRepository;
    }

    public function getAllTargetByUser(Request $request, $id)
    {
        $validator = Validator::make(['status' => $request->status, 'id' => $id], [
            'id' => 'exists:users,id',
            'status' => 'required|in:berlangsung,tercapai',
        ]);

        //check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //get all target
        $target = Target::where('user_id', $id)->where('status', $request->status)->latest()->paginate(10);

        if ($target->isEmpty() && $request->status == 'berlangsung') {
            return $this->responseArray->returnArray(204, 'Belum Ada Tabungan', null);
        } elseif ($target->isEmpty() && $request->status == 'tercapai') {
            return $this->responseArray->returnArray(204, 'Belum Ada Tabungan Yang Tercapai', null);
        }

        return $this->responseArray->returnArray(200,'Data Tabungan',$target);
    }

    public function show($id)
    {

        $target = Target::find($id);

        if (is_null($target)) {
            return response()->json(['message' => 'Id Tidak Valid']);
        }

        $payData = $this->payRepository->getAllPayByTarget($id);

        return $this->responseArray->returnArray(200, "Successfuly Get Data", [
            'data-tabungan' => $target,
            'data-pembayaran' => $payData
        ]);
    }

    public function store(Request $request)
    {
        //define validation rules
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'gambar' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'judul' => 'required',
            'target_uang' => 'required|min:1',
            'jadwal_menabung' => 'required|in:hari,minggu,bulan',
            'nominal_pengisian' => 'required|min:1',
        ]);

        //check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if ($request->nominal_pengisian >= $request->target_uang) {
            return response()->json(['message' => 'Nominal Pengisian Tidak Boleh Lebih Besar Dari Target Uang'], 422);
        }

        //upload image
        $gambar = $request->file('gambar');
        $gambar->storeAs('public/target', $gambar->hashName());

        //create post
        $target = Target::create([
            'user_id' => $request->user_id,
            'judul' => $request->judul,
            'gambar' => $gambar->hashName(),
            'target_uang' => $request->target_uang,
            'nominal_pengisian' => $request->nominal_pengisian,
            'jadwal_menabung' => $request->jadwal_menabung,
        ]);

        return $this->responseArray->returnArray(201, 'Data Tabungan Berhasil Ditambahkan!', $target);
    }

    public function update(Request $request, $id){
        //define validation rules
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'gambar' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'judul' => 'required',
            'target_uang' => 'required|min:1',
            'jadwal_menabung' => 'required|in:hari,minggu,bulan',
            'nominal_pengisian' => 'required|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $target = Target::find($id);

        $extraMessage = '';

        if ($target->uang_tersimpan > $request->target_uang) {
            return response()->json([
                'message' => 'target tabungan harus lebih besar dari uang yang disimpan'
            ]);
        } elseif ($target->uang_tersimpan == $target->target_uang) {
            return response()->json([
                'message' => 'tabungan telah tercapai, tidak dapat diubah'
            ]);
        } elseif ($target->uang_tersimpan == $request->target_uang) {
            $target->update([
                'status' => 'tercapai'
            ]);
            $extraMessage = ['alert' => 'selamat tabungan telah tercapai'];
        }

        if ($request->hasFile('gambar')) {

            $gambar = $request->file('gambar');
            $gambar->storeAs('public/target', $gambar->hashName());

            Storage::delete('public/target/' . basename($target->gambar));

            $target->update([
                'judul' => $request->judul,
                'gambar' => $gambar->hashName(),
                'target_uang' => $request->target_uang,
                'nominal_pengisian' => $request->nominal_pengisian,
                'jadwal_menabung' => $request->jadwal_menabung,
            ]);
        } else {

            //update post without image
            $target->update([
                'judul' => $request->judul,
                'target_uang' => $request->target_uang,
                'nominal_pengisian' => $request->nominal_pengisian,
                'jadwal_menabung' => $request->jadwal_menabung,
            ]);
        }

        $payData = $this->payRepository->getAllPayByTarget($id);

        return $this->responseArray->returnArray(200, 'Data Tabungan Berhasil Diubah!, '. $extraMessage ?? '', null);
    }

    public function destroy($id){
        $validator = Validator::make(['id'=>$id], [
            'id'=>'required|exists:targets,id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $target = Target::find($id);

        Storage::delete('public/target/' . basename($target->image));

        $target->delete();

        return $this->responseArray->returnArray(200,'Data Tabungan Berhasil Dihapus', Null);
    }
}
