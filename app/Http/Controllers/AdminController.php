<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use Yajra\DataTables\DataTables;



class AdminController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {   

        $fechaAi=now()->toDateString()." 00:00:01";
        $fechaAf=now()->toDateString()." 23:59:59";

        if(request()->ajax())
        {

            if (!empty($request->periodo1) & !empty($request->zona1)) {
               
                $data = DB::table('ordenescu')
               ->select('Ciclo','Periodo','Usuario','nombreu', 'idDivision',
                DB::raw('SUM(CASE WHEN Estado > 0 THEN 1 ELSE 0 END) AS Asignados'),
                DB::raw('SUM(CASE WHEN Estado = 2 THEN 1 ELSE 0 END) AS Pendientes'),
                DB::raw('SUM(CASE WHEN Estado = 4 THEN 1 ELSE 0 END) AS Ejecutadas'),
                DB::raw('SUM(CASE WHEN Critica = "ALTO CONSUMO" THEN 1 ELSE 0 END) AS Altos'),
                DB::raw('SUM(CASE WHEN Critica = "BAJO CONSUMO" THEN 1 ELSE 0 END) AS Bajos'),
                DB::raw('SUM(CASE WHEN Critica = "NEGATIVO" THEN 1 ELSE 0 END) AS Negativo'),
                DB::raw('SUM(CASE WHEN Critica = "CONSUMO CERO" THEN 1 ELSE 0 END) AS Consumo_cero'),
                DB::raw('SUM(CASE WHEN Critica = "NORMAL" THEN 1 ELSE 0 END) AS Normales'),
                DB::raw('SUM(CASE WHEN Critica = "CAUSADO" THEN 1 ELSE 0 END) AS Causados'),
                DB::raw('MIN(fecha_de_ejecucion) as inicio'), DB::raw('MAX(fecha_de_ejecucion) as Final'))
                ->where([
                    ['periodo', $request->periodo1],
                    ['Ciclo', $request->zona1],
                    ['Estado_des', '!=', 'CARGADO'],
                    ])
                ->groupBy('Ciclo', 'Periodo', 'Usuario','nombreu','idDivision')
                ->get();
                
            }else{

                $data = DB::table('ordenescu')
                ->select('Ciclo','Periodo','Usuario', 'nombreu', 'idDivision',
                DB::raw('SUM(CASE WHEN Estado > 0 THEN 1 ELSE 0 END) AS Asignados'),
                DB::raw('SUM(CASE WHEN Estado = 2 THEN 1 ELSE 0 END) AS Pendientes'),
                DB::raw('SUM(CASE WHEN Estado = 4 THEN 1 ELSE 0 END) AS Ejecutadas'),
                DB::raw('SUM(CASE WHEN Critica = "ALTO CONSUMO" THEN 1 ELSE 0 END) AS Altos'),
                DB::raw('SUM(CASE WHEN Critica = "BAJO CONSUMO" THEN 1 ELSE 0 END) AS Bajos'),
                DB::raw('SUM(CASE WHEN Critica = "NEGATIVO" THEN 1 ELSE 0 END) AS Negativo'),
                DB::raw('SUM(CASE WHEN Critica = "CONSUMO CERO" THEN 1 ELSE 0 END) AS Consumo_cero'),
                DB::raw('SUM(CASE WHEN Critica = "NORMAL" THEN 1 ELSE 0 END) AS Normales'),
                DB::raw('SUM(CASE WHEN Critica = "CAUSADO" THEN 1 ELSE 0 END) AS Causados'),
                DB::raw('MIN(fecha_de_ejecucion) as inicio'), DB::raw('MAX(fecha_de_ejecucion) as Final'))
                ->whereBetween('fecha_de_ejecucion', [$fechaAi,$fechaAf])
                ->groupBy('Ciclo', 'Periodo', 'Usuario', 'nombreu', 'idDivision')
                ->get();

            }

           return  DataTables()->of($data)->make(true);

        }
        
        
        
        return view('admin.admin.index'); 


       
      
      
    
   
    }
   
}
