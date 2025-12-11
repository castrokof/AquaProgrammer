<?php

namespace App\Exports;

use App\Models\Admin\Ordenesmtl;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class OrdenesExport implements FromCollection, WithHeadings
{
    
    use Exportable;
     protected $data;

    public function __construct(Collection $data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        $validatedData = $this->data->map(function ($item) {
            // Validar y cambiar el formato de la fecha
            if (!empty($item['Fecha_de_ejecucion'])) {
               
                 $fechaOriginal = $item['Fecha_de_ejecucion'];

                // Convierte la fecha a un objeto Carbon
                $fechaCarbon = Carbon::parse($fechaOriginal);
                
                // Formatea la fecha según el formato deseado
                $item['Fecha_de_ejecucion'] = $fechaCarbon->format('d/m/Y');
                   
                   
               
            }
           
            
            //Motivos de No lectura  
        
            if ($item['Causa_des'] == '11-MEDIDOR DAÑADO') {
                $item['Causa_des'] = '1';
            }
           
            if ($item['Causa_des'] == '04-REJA CON CANDADO O SIMILAR') {
                $item['Causa_des'] = '4';
            }
            if ($item['Causa_des'] == '02-MEDIDOR TAPADO U OBSTRUIDO') {
                $item['Causa_des'] = '2';
            }
            if ($item['Causa_des'] == '06-MEDIDOR ENTERRADO O INUNDADO') {
                $item['Causa_des'] = '6';
            }
            if ($item['Causa_des'] == '03-SIN MEDIDOR Y SIN SERVICIO') {
                $item['Causa_des'] = '3';
            }
            if ($item['Causa_des'] == '01-MEDIDOR CON PRESUNTA FALLA') {
                $item['Causa_des'] = '1';
            }
            if ($item['Causa_des'] == '07-LOTE O CASA DEMOLIDA') {
                $item['Causa_des'] = '7';
            }
             if ($item['Causa_des'] == '09-OTRAS CAUSAS') {
                $item['Causa_des'] = '9';
            }
            if ($item['Causa_des'] == '08-ANIMAL CON APARENTE RIESGO') {
                $item['Causa_des'] = '8';
            }
                              
                                        
            //Observaciones                            
            
            if ($item['Observacion_des'] == 'MEDIDOR PARADO (DAÑO INTERNO)-33') {
                $item['Observacion_des'] = '14';
            }
            
            if ($item['Observacion_des'] == 'CASA DESOCUPADA-30') {
                $item['Observacion_des'] = '1';
            }
            
            if ($item['Observacion_des'] == 'RESIDENTE OCASIONAL-16') {
                $item['Observacion_des'] = '16';
            }
            
            if ($item['Observacion_des'] == 'OTRA FUENTE-15') {
                $item['Observacion_des'] = '15';
            }
            
            if ($item['Observacion_des'] == 'DESCONECTADO-32') {
                $item['Observacion_des'] = '11';
            }
            if ($item['Observacion_des'] == 'SUSPENDIDO DESOCUPADO-31') {
                $item['Observacion_des'] = '12';
            }
    
            
          
            return $item;
        });

        return $validatedData;
    }
    
    
     public function headings(): array
    {
        return [
            'Suscriptor', 'Lect_Actual', 'Causa_des', 'Observacion_des', 'Fecha_de_ejecucion' 
        ];
    }
}
