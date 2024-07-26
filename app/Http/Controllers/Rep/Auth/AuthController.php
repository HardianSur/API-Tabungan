<?php

namespace App\Http\Controllers\Rep\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Auth\AuthRepository;

class AuthController extends Controller
{
    protected $authRepository;
    public function __construct(AuthRepository $authRepository) {
        $this->authRepository = $authRepository;
    }

    public function view(){
        return view('pages.auth.login');
    }
    public function signin(Request $request){
        return $this->authRepository->signin($request);
    }
}
