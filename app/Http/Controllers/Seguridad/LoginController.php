<?php

namespace App\Http\Controllers\Seguridad;
use Illuminate\Notifications\Notifiable;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;

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
       
         if(Auth::attempt($request->only('usuario','password'))){

            $user = Auth::user();
            
            return Response()->json([
            'user' => $user
        ], 200);



         }else{



            return response()->json(['error'=> 'Unauthorised'], 401);

         }

        

    }


}
