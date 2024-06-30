<?php

namespace App\Http\Controllers\Api;

use App\Models\Target;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\TargetResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TargetController extends Controller
{
    public function index()
    {
        //get all target
        $target = Target::latest()->paginate(5);

        if ($target->isEmpty()) {
            return response()->json(['message' => 'Belum Ada Tabungan']);
        }

        //return collection of posts as a resource
        return new TargetResource(true, 'List Data Tabunngan', $target);
    }

    public function getAllTargetByUser(Request $request, $id)
    {
        // dd($request->status);

        //define validation rules
        $validator = Validator::make(['status'=>$request->status, 'id' => $id], [
            'id' => 'exists:users,id',
            'status' => 'required|in:berlangsung,tercapai',
        ]);
        // dd($validator);


        //check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //get all target
        $target = Target::where('user_id', $id)->where('status', $request->status)->latest()->paginate(10);

        if ($target->isEmpty() && $request->status == 'berlangsung') {
            return response()->json(['message' => 'Belum Ada Tabungan'],204);
        } elseif ($target->isEmpty() && $request->status == 'tercapai') {
            return response()->json(['message' => 'Belum Ada Tabungan Yang Tercapai'],204);
        }

        //return collection of posts as a resource
        return new TargetResource(true, 'List Data Tabungan', $target);
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

        if($request->nominal_pengisian >= $request->target_uang){
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

        //return response
        return new TargetResource(true, 'Data Tabungan Berhasil Ditambahkan!', $target);
    }

    public function show($id)
    {
        //find post by ID
        $target = Target::find($id);

        if (is_null($target)) {
            return response()->json(['message' => 'Id Tidak Valid']);
        }

        // Call the show function from PayController
        $payController = new PayController();
        $payData = $payController->getPayByTargetId($id);

        // Combine the results
        return response()->json([
            'detail-tabungan' => new TargetResource(true, 'Detail Data Tabungan!', $target),
            'detail-pembayaran' => $payData
        ]);

        //return single post as a resource
        // return new TargetResource(true, 'Detail Data Tabungan!', $target);
    }

    public function update(Request $request, $id)
    {
        //define validation rules
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'gambar' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'judul' => 'required',
            'target_uang' => 'required|min:1',
            'jadwal_menabung' => 'required|in:hari,minggu,bulan',
            // 'uang_tersimpan' => 'min:0',
            'nominal_pengisian' => 'required|min:1',
        ]);

        //check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //find post by ID
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

        //check if image is not empty
        if ($request->hasFile('gambar')) {

            //upload image
            $gambar = $request->file('gambar');
            $gambar->storeAs('public/target', $gambar->hashName());

            //delete old image
            Storage::delete('public/target/' . basename($target->gambar));

            //update post with new image
            $target->update([
                'judul' => $request->judul,
                'gambar' => $gambar->hashName(),
                'target_uang' => $request->target_uang,
                // 'uang_tersimpan' => $request->uang_tersimpan,
                'nominal_pengisian' => $request->nominal_pengisian,
                'jadwal_menabung' => $request->jadwal_menabung,
            ]);
        } else {

            //update post without image
            $target->update([
                'judul' => $request->judul,
                'target_uang' => $request->target_uang,
                // 'uang_tersimpan' => $request->uang_tersimpan,
                'nominal_pengisian' => $request->nominal_pengisian,
                'jadwal_menabung' => $request->jadwal_menabung,
            ]);
        }

        //return response
        return new TargetResource(true, 'Data Target Berhasil Diubah!', [$this->show($id), $extraMessage]);
    }

    public function destroy($id)
    {

        //define validation rules
        $validator = Validator::make(['id'=>$id], [
            'id'=>'required|exists:targets,id'
        ]);

        //check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //find post by ID
        $target = Target::find($id);

        //delete image
        Storage::delete('public/target/' . basename($target->image));

        //delete post
        $target->delete();

        //return response
        return new TargetResource(true, 'Data Tabungan Berhasil Dihapus!', null);
    }
}
