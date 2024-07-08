<?php
namespace App\Repositories\Auth;

use App\Models\User;
use App\Models\AccessToken;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Response\ResponseArray;
use App\Models\PersonalAccessToken;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Validator;

class AuthRepository implements AuthInterface
{
    protected $response;
    public function __construct(ResponseArray $response)
    {
        $this->response = $response;
    }
    public function signin( $request)
    {
        try {
            $request->validate([
                'username' => 'required|string',
                'password' => 'required|string',
            ]);
            $credentials = $request->only('username', 'password');
            // return $this->response->returnArray(401, 'tes', $credentials);

            $field = filter_var($credentials['username'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';


            $token = Auth::attempt(["$field" => $credentials['username'], 'password' => $credentials['password']]);


            if (!$token) {
                return $this->response->returnArray(401, 'Unauthorized', $token);
            }

            $user = Auth::user();
            // return $this->response->returnArray(401, 'tes', $credentials);


            $words = explode(' ', $user->name);
            $initials = '';
            foreach ($words as $word) {
                $initials .= strtoupper($word[0]);
            }

            $tokenSet = new AccessToken;
            $tokenSet->user_id = $user->id;
            $tokenSet->token = $token;
            $tokenSet->expired_at = date('Y-m-d H:i:s', strtotime('+1 day'));
            $tokenSet->save();


            session([
                'initials' => $initials,
                'user_id' => $user->id,
                'username' => $user->username,
                'name' => $user->name,
                'authorization' => [
                    'token' => $token,
                    'type' => 'bearer',
                ]
            ]);

            Cookie::queue(Cookie::make('user_id', $user->id, 125)); // 125 minutes

            return $this->response->returnArray(200, 'Successfully authorized', [
                'user' => $user,
                'for' => 'backend',
                'authorization' => [
                    'token' => $token,
                    'type' => 'Bearer',
                ]
            ]);

        } catch (\Exception $e) {
            Log::info($e->getMessage());
            return $this->response->returnArray(500, 'Failed authorized', $e->getMessage());
        }
    }
}
