<?php

namespace App\Http\Controllers\Api;

use App\Models\Pay;
use App\Models\Target;
use Illuminate\Http\Request;
use App\Http\Resources\PayResource;
use App\Http\Controllers\Controller;
use App\Http\Resources\TargetResource;
use Illuminate\Support\Facades\Validator;

class PayController extends Controller
{
    public function index()
    {
        // Get all payments for a specific target
        $pay = Pay::latest()->paginate(10);

        //return collection of posts as a resource
        return new PayResource(true, 'List Data Pembayaran', $pay);
    }

    public function show($id)
    {
        $pays = Pay::find($id);

        //return single post as a resource
        return new TargetResource(true, 'Detail Data Tabungan!', $pays);
    }

    public function getPayByTargetId($id)
    {
        //define validation rules
        $validator = Validator::make(["target_id" => $id], [
            'target_id' => 'required|exists:targets,id'
        ]);

        //check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        //find post by ID
        $pays = Pay::where('target_id', $id)->latest()->paginate(10);

        if ($pays->isEmpty()) {
            $pays = 'Belum Ada Transaksi';
        }

        //return single post as a resource
        return response()->json($pays);
    }

    public function store(Request $request)
    {

        //define validation rules
        $validator = Validator::make($request->all(), [
            'target_id' => 'required|exists:targets,id',
            'operasi' => 'required|in:tambah,kurang',
            'uang_masuk' => 'required|min:1',
        ]);


        //check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Additional validation for 'status' column in 'targets' table
        $target = Target::find($request->target_id);
        if ($target && $target->status !== 'berlangsung') {
            return response()->json(['error' => 'Tabungan Telah Tercapai'], 422);
        }

        switch ($request->operasi) {
            case 'tambah':
                if (($target->uang_tersimpan + $request->uang_masuk) > $target->target_uang) {
                    return response()->json([
                        "message" => "uang yang ditabung melebihi batas"
                    ]);
                }

                //increase value 'uang_tersimpan'
                $target->increment('uang_tersimpan', $request->uang_masuk);
                break;
            case 'kurang':
                if($request->uang_masuk > $target->uang_tersimpan ){
                    return response()->json([
                        "message" => "uang yang dikurang melebihi uang yang ditabung"
                    ]);
                }
                $target->decrement('uang_tersimpan', $request->uang_masuk);
                break;
            default:
                return response()->json(array('error' =>'Invalid'));
        }

        //create pay
        $pay = Pay::create([
            'target_id' => $request->target_id,
            'uang_masuk' => $request->uang_masuk,
            'operasi' => $request->operasi,
        ]);

        if ($target->uang_tersimpan == $target->target_uang) {
            $target->update([
                'status' => 'tercapai'
            ]);
            return new PayResource(true, 'Tabungan telah Tercapai', app('App\Http\Controllers\Api\TargetController')->show($target->id));
        }


        return new PayResource(true, 'Data Pembayaran Berhasil Di' . $request->operasi . '!', app('App\Http\Controllers\Api\TargetController')->show($target->id));
    }

    public function update(Request $request, $id)
    {

        //define validation rules
        $validator = Validator::make($request->all(), [
            'operasi' => 'required|in:tambah,kurang',
            'uang_masuk' => 'required|min:1'
        ]);

        //check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //find pay by id
        $pay = Pay::find($id);

        //find target by target_id 
        $target = Target::find($pay->target_id);

        //check value status
        if ($target->status == 'tercapai') {
            return response()->json([
                "message" => "tabungan telah tercapai"
            ]);
        }


        if ($request->operasi == 'kurang' && $request->uang_masuk > $pay->uang_masuk) {
            return response()->json([
                "message" => "uang yang dikurangi lebih besar dari yang tersimpan"
            ]);
        } elseif ($request->operasi == 'kurang') {
            $target->decrement('uang_tersimpan', $request->uang_masuk);
            $pay->decrement('uang_masuk', $request->uang_masuk);
        } else {
            $target->increment('uang_tersimpan', $request->uang_masuk);
            $pay->increment('uang_masuk', $request->uang_masuk);
            if ($target->uang_tersimpan > $target->target_uang) {
                $target->decrement('uang_tersimpan', $request->uang_masuk);
                $pay->decrement('uang_tersimpan', $request->uang_masuk);
                return response()->json([
                    "message" => "uang yang ditabung melebihi batas"
                ]);
            } elseif ($target->uang_tersimpan == $target->target_uang) {
                $target::update([
                    'status' => 'tercapai'
                ]);
                return new PayResource(true, 'Tabungan telah Tercapai', app('App\Http\Controllers\Api\TargetController')->show($target->id));
            }
        }

        return new PayResource(true, 'Data Pembayaran Berhasil Di' . $request->operasi, app('App\Http\Controllers\Api\TargetController')->show($target->id));

    }

    public function destroy($id)
    {
        //define validation rules
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|exists:pays,id'
        ]);

        //check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //find pay by id
        $pay = Pay::find($id);

        //find target by target_id 
        $target = Target::find($pay->target_id);

        $target->decrement('uang_tersimpan', $pay->uang_masuk);

        $pay->delete();

        return new PayResource(true, 'Data Pembayaran Berhasil Hapus', app('App\Http\Controllers\Api\TargetController')->show($target->id));

    }
}