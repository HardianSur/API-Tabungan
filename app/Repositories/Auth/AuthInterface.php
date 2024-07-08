<?php
namespace App\Repositories\Auth;
use Illuminate\Http\Request;


interface AuthInterface {
    public function signin(Request $request);
}
