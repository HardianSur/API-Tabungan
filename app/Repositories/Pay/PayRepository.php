<?php
namespace App\Repositories\Pay;

use App\Models\Pay;
use App\Models\Target;
use App\Response\ResponseArray;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class PayRepository implements PayInterface
{
    protected $responseArray, $payRepository;

    public function __construct(ResponseArray $responseArray)
    {
        $this->responseArray = $responseArray;
    }

    public function getAllPayByTarget($id)
    {
        $data = Pay::targets()->where('target_id', $id)->get();

        if (!$data) {
            return response()->json(["Message" => "Belum Ada Uang Yang Ditabung"]);
        }

        return response()->json(["Data" => $data]);
    }

    public function show($id)
    {
        $data = Pay::find($id);

        if (!$data) {
            return $this->responseArray->returnArray(401, "Data Tidak Ditemukan", Null);
        }

        return $this->responseArray->returnArray(200, "Pembayaran Ditemukan", $data);
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
                    ], 422);
                }

                //increase value 'uang_tersimpan'
                $target->increment('uang_tersimpan', $request->uang_masuk);
                break;
            case 'kurang':
                if ($request->uang_masuk > $target->uang_tersimpan) {
                    return response()->json([
                        "message" => "uang yang dikurang melebihi uang yang ditabung"
                    ], 422);
                }
                $target->decrement('uang_tersimpan', $request->uang_masuk);
                break;
            default:
                return response()->json(['error' => 'Invalid'], 400);
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
            return $this->responseArray->returnArray(200, 'Tabungan telah Tercapai', Null);
        }


        return $this->responseArray->returnArray(200, 'Data Pembayaran Berhasil Di' . $request->operasi, Null);
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
            ], 422);
        }


        switch ($request->operasi) {
            case 'tambah':
                if (($target->uang_tersimpan + $request->uang_masuk) > $target->target_uang) {
                    return response()->json([
                        "message" => "uang yang ditabung melebihi batas"
                    ], 422);
                }

                if($request->uang_masuk < $pay->uang_masuk){
                    $operasi = $pay->uang_masuk - $request->uang_masuk;
                    if($operasi <= 0){
                        return response()->json([
                            "message" => "Invalid"
                        ], 422);
                    }
                $target->decrement('uang_tersimpan', $operasi);
                }

                $target->increment('uang_tersimpan', $request->uang_masuk - $pay->uang_masuk);
                $pay->uang_masuk = $request->uang_masuk;
                break;
            case 'kurang':
                if ($request->uang_masuk > $target->uang_tersimpan) {
                    return response()->json([
                        "message" => "uang yang dikurang melebihi uang yang ditabung"
                    ], 422);
                }

                if($request->uang_masuk < $pay->uang_masuk){
                    $operasi = $pay->uang_masuk - $request->uang_masuk;
                    if($operasi <= 0){
                        return response()->json([
                            "message" => "Invalid"
                        ], 422);
                    }
                $target->increment('uang_tersimpan', $operasi);
                }

                $target->decrement('uang_tersimpan', $request->uang_masuk - $pay->uang_masuk);
                $pay->uang_masuk = $request->uang_masuk;
                break;
            default:
                return response()->json(['error' => 'Invalid'], 400);
        }

        $pay->operasi = $request->operasi;
        $pay->save();

        if ($target->uang_tersimpan == $target->target_uang) {
            $target::update([
                'status' => 'tercapai'
            ]);
            return $this->responseArray->returnArray(200, 'Tabungan Telah Tercapai', null);
        }


        return $this->responseArray->returnArray(200, 'Data Pembayaran Berhasil Di' . $request->operasi,null);

    }
}
