<?php

namespace App\Exports;

use App\Models\Service;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;


class ServiceExport implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct($services)
    {
        $this->services = $services;
    }

    public function view(): View
    {   $tipo_matr= [
        'Nomal',
        'Mucama',
        'Final de Obra'
        ];
        $estado_matr= [
        'Creado',
        'Asignado',
        'Ejecutado',
        'Supervisado',
        'Procesado',
        'Pagado',
        'Cancelado'
        ];
        $tipo_pago_matr= [
        'Efectivo',
        'Tarjeta',
        'Transferencia'
        ];
        $tipo_ambiente_matr= [
        'Normal',
        'Monoambiente',
        'DosAmbientes',
        'TripleAmbiente',
        'CuatroAmbientes'
        ];

        $services_exp = [];

        foreach ($this->services as $indexed => $service) {
            $services_exp[$indexed] = [
                'description' => $service->description,
                'tipo' => $tipo_matr[$service->tipo], // 0: Nomal, 1: Mucama, 2: Final de Obra
                'estado' => $estado_matr[$service->estado], // 0: Creado, 1: Asignado, 2: Ejecutado, 3: Supervisado, 4: Procesado, 5: Pagado , 6: Cancelado
                'tipo_pago' => $tipo_pago_matr[$service->tipo_pago], // 0: Efectivo, 1: Tarjeta, 2: Transferencia
                'n_fichas'  => $service->n_fichas,
                'n_personas' => $service->n_personas,
                'costo_ficha' => $service->costo_ficha,
                'tipo_ambiente' => $tipo_ambiente_matr[$service->tipo_ambiente], // 0: Normal, 1: Monoambiente, 2: DosAmbientes , 3: TripleAmbiente, 4: CuatroAmbientes
                'costo_ambiente' => $service->costo_ambiente,
                'costo_asignado' => $service->costo_asignado,
                'costo_hora' => $service->costo_hora,
                'fecha_inicio' => $service->fecha_inicio,
                'tiempo_horas' => $service->tiempo_horas,
                'costo_total' => $service->costo_total,
                'unity_id' => $service->unity_id,
                'unity' => $service->unity->name,    // The unity associated with the service
                'unity_parent' => $service->unity->parent->name, // The parent unity associated with the service
                'user_id' => $service->user_id,
                'user' => $service->user->name, // The user associated with the service
                'users' => $service->users, // Array of users associated ( that does the service) with the service
                'items' => $service->items, // Array of items associated with the service
            ];
        }

        

        return view('exports.servicesexport', [
            'services' => $services_exp
        ]);
    }
}
