<?php

namespace App\Http\Controllers\Seguridad;
use Illuminate\Notifications\Notifiable;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    //use Notifiable;
    use AuthenticatesUsers;
    
    protected $redirectTo = '/tablero';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

     
    public function index()
    {
            
           return view('seguridad.index');
    }

    
    
    protected function authenticated(Request $request, $user)
    {   
        $useractivo = $user->where([
            ['usuario', '=', $request->usuario],
            ['estado', '=', 'activo']
        
        ])->count();
      
        $roles1 = $user->roles1()->get();
       
        if ($roles1->isNotEmpty() && $useractivo >= 1) {
            $user->setSession();
        }else{
            $this->guard()->logout();
            $request->session()->invalidate();
            return redirect('seguridad/login')->withErrors(['error'=>'Este usuario no esta activo y no tiene rol ']);
        }
    }

    public function username()
    {
        return 'usuario';
    }
            public function loginMovil(Request $request)
    {
        // Validar las credenciales
        $credentials = $request->only('usuario', 'password');
        
        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            if ($user->estado == "activo") {
                // Genera un token aleatorio
                $token = Str::random(60);
                
                // ⭐ Guarda el hash del token en la BD
                $user->api_token = hash('sha256', $token);
                $user->save();

              // ⭐ Devolver como ARRAY con un solo elemento
                return response()->json([
                    [
                        'id' => $user->id,
                        'usuario' => $user->usuario,
                        'nombre' => $user->nombre,
                        'tipodeusuario' => $user->tipodeusuario,
                        'email' => $user->email,
                        'empresa' => $user->empresa,
                        'remenber_token' => $user->remember_token,
                        'estado' => $user->estado,
                        'api_token' => $token, // Token SIN hashear
                        'created_at' => $user->created_at,
                        'updated_at' => $user->updated_at,
                    ]
                ], 200);
            } else {
                return response()->json(['error' => 'Usuario no activo'], 403);
            }
        } else {
            return response()->json(['error' => 'Credenciales incorrectas'], 401);
        }
    }


}
