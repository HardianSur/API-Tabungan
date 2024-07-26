<?php

namespace App\Http\Controllers\Rep\Pay;

use App\Http\Controllers\Controller;
use App\Models\Pay;
use App\Repositories\Pay\PayRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PayController extends Controller
{
    protected $payRepository;

    public function __construct(PayRepository $payRepository){
        $this->payRepository = $payRepository;
    }

    public function getPayByTargetId($id)
    {
        return $this->payRepository->getAllPayByTarget($id);
    }

    public function store(Request $request)
    {
        return $this->payRepository->store($request);
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
            ],422);
        }


        if ($request->operasi == 'kurang' && $request->uang_masuk > $pay->uang_masuk) {
            return response()->json([
                "message" => "uang yang dikurangi lebih besar dari yang tersimpan"
            ],422);
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
                ],422);
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
