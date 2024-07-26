<?php
namespace App\Repositories\Target;
use Illuminate\Http\Request;


interface TargetInterface {
    public function getAllTargetByUser(Request $request, $id);

    public function show($id);
    public function store(Request $request);
    public function update(Request $request,$id);
}
