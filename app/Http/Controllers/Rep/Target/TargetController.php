<?php

namespace App\Http\Controllers\Rep\Target;

use App\Models\Target;
use App\Repositories\Target\TargetRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\TargetResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;


class TargetController extends Controller
{

    protected $targetRepository;

    public function __construct(TargetRepository $targetRepository){
        $this ->targetRepository = $targetRepository;
    }

    public function getAllTargetByUser(Request $request, $id)
    {
        return $this->targetRepository->getAllTargetByUser($request,$id);
    }

    public function store(Request $request)
    {
        return $this->targetRepository->store($request);
    }

    public function show($id)
    {
        return $this->targetRepository->show($id);
    }

    public function update(Request $request, $id)
    {
        return $this->targetRepository->update($request,$id);
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
