<?php

namespace App\Http\Controllers\Api;

use App\Models\Service;
use App\Models\Unity;
use App\Models\User;
use App\Models\Configuration;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\Controller;
use App\Http\Resources\ServiceResource;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $services = ServiceResource::collection(Service::getOrPaginate());
        return $services;
    }

    public function exportall()
    {
        $user = auth('api')->user();
        $services = $user->can('servicio.exportar')
            ? Service::all()
            : Service::where('user_id', $user->id)->get();

        return Excel::download(new \App\Exports\ServiceExport( $services ), 'services.xlsx');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $data = $request->validate([
            'description' => 'required|string|max:2000',
            'tipo' => 'required|string|max:255',
            //'estado' => 'required|string|max:255',
            'tipo_pago' => 'required|string|max:255',
            'n_fichas' => 'required|integer',
            //'n_personas' => 'required|integer',
            //'costo_ficha' => 'required|numeric',
            //'tipo_ambiente' => 'required|string|max:255',
            //'costo_ambiente' => 'required|numeric',
            'costo_asignado' => 'required|numeric',
            'costo_hora' => 'required|numeric',
            'fecha_inicio' => 'required|date',
            'tiempo_horas' => 'required|numeric',
            //'costo_total' => 'required|numeric',
            'unity_id' => 'required|integer|exists:unities,id',
            //'user_id' => 'required|integer|exists:users,id',
            'items' => 'nullable|array',
            'items.*.cantidad' => 'sometimes|required|numeric|gt:0',
            'items.*.costo' => 'sometimes|required|numeric|gt:0',
            'items.*.name' => 'sometimes|required|string|max:255',
            'items.*.id' => 'sometimes|required|numeric',
        ]);
        $data['user_id'] = auth('api')->id(); // Set the user_id to the authenticated user's ID
        $data['estado'] = 0; // Set estado to 0 by default

        $configuration = Configuration::first();

        $matrizColum= [ 'costo_monoambiente',
                        'costo_dos_ambientes',
                        'costo_tres_ambientes',
                        'costo_cuatro_ambientes',];
        
        $data['tipo_ambiente'] = Unity::find($data['unity_id'])->type;
        $data['costo_ambiente'] = $configuration->{$matrizColum[$data['tipo_ambiente']]};
        $data['users'] = json_encode([], JSON_FORCE_OBJECT);
        if (isset($data['items'])) {
            $tems2=[];
            foreach ($data['items'] as $item) {
                $tems2[$item['id']] = $item;
            }
            
            $data['items'] = json_encode($tems2, JSON_FORCE_OBJECT);
        } else {
            $data['items'] = json_encode([], JSON_FORCE_OBJECT);
        }
        $totalitems = 0;
        if (isset($data['items'])) {
            $items = json_decode($data['items'], true);
            foreach ($items as $item) {
                $totalitems += $item['cantidad'] * $item['costo'];
            }
        }

        // $data['items'] = json_encode([], JSON_FORCE_OBJECT);
        $data['fecha_inicio'] = Carbon::parse($data['fecha_inicio'])->format('Y-m-d H:i:s');


        $unity = Unity::find($data['unity_id']);
        if ($unity) {
            $costo_ficha = $unity->costo_ficha;
            if ($costo_ficha ) {
                $data['costo_ficha'] = $costo_ficha;
            } else {
                $data['costo_ficha'] = $unity->parent->costo_ficha;
            }
        } else {
            return response()->json(['error' => 'Unity not found'], 404);
        }

        $data['costo_total'] = $data['costo_asignado'] 
                                + $totalitems
                                + $data['costo_ambiente'] 
                                +$data['costo_hora']*$data['tiempo_horas']
                                + ($data['n_fichas'] * $data['costo_ficha']);

        
    $service = Service::create($data);
    return new ServiceResource($service);



    }

    /**
     * Display the specified resource.
     */
    public function show(Service $service)
    {
        //
        return new ServiceResource($service);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Service $service)
    {
        //
        $data = $request->validate([
            'description' => 'sometimes|required|string|max:2000',
            'tipo' => 'sometimes|required|string|max:255',
            'estado' => 'sometimes|required|string|max:255',
            'tipo_pago' => 'sometimes|required|string|max:255',
            'n_fichas' => 'sometimes|required|integer',
            'n_personas' => 'sometimes|required|integer',
            'costo_ficha' => 'sometimes|required|numeric',
            'tipo_ambiente' => 'sometimes|required|string|max:255',
            'costo_ambiente' => 'sometimes|required|numeric',
            'costo_asignado' => 'sometimes|required|numeric',
            'costo_hora' => 'sometimes|required|numeric',
            'fecha_inicio' => 'sometimes|required|date',
            'tiempo_horas' => 'sometimes|required|numeric|gt:0',
            //'costo_total' => 'sometimes|required|numeric',
            'unity_id' => 'sometimes|required|integer|exists:unities,id',
            'user_id' => 'sometimes|required|integer|exists:users,id',
            //'users' => 'nullable|array',
            'items' => 'sometimes|nullable|array',
            'items.*.cantidad' => 'sometimes|required|numeric|gt:0',
            'items.*.costo' => 'sometimes|required|numeric|gt:0',
            'items.*.name' => 'sometimes|required|string|max:255',
            'items.*.cantidad' => 'sometimes|required|numeric|gt:0',
            'items.*.id' => 'sometimes|required|numeric|gt:0',

        ]);
        if (isset($data['items'])) {
            $tems2=[];
            foreach ($data['items'] as $item) {
                $tems2[$item['id']] = $item;
            }

            $data['items'] = json_encode($tems2, JSON_FORCE_OBJECT);
        } 
        $totalitems = 0;
        if (isset($data['items'])) {
            $items = json_decode($data['items'], true);
            foreach ($items as $item) {
                $totalitems += $item['cantidad'] * $item['costo'];
            }
        }

        // permisos asociados a poder cambiar el estado de los servicios [1: "servicio.asignar", 2: "servicio.ejecutar",
        // 3: "servicio.supervisar", 4: "servicio.procesar", 5: "servicio.pagar"]
        
        if (array_key_exists('estado', $data)) {
            $permisosEstado = [
                1 => 'servicio.asignar',
                2 => 'servicio.ejecutar',
                3 => 'servicio.supervisar',
                4 => 'servicio.procesar',
                5 => 'servicio.pagar',
            ];

            $user = auth('api')->user();
            // return response()->json(['can' => auth('api')->user()->hasPermissionTo($permisosEstado[$data['estado']]),
            // 'permiso' => $permisosEstado[$data['estado']]]);
            
            if (isset($data['estado'])) {
                $permisoEstado = $permisosEstado[$data['estado']];

                if (!$user || !$user->can($permisoEstado) || (!$user->can('servicio.back') && $data['estado'] < $service->estado)) {
                    unset($data['estado']);
                }
            }
        }

        

        $data['costo_total'] = $data['costo_asignado'] 
                                + $totalitems
                                + $data['costo_ambiente'] 
                                +$data['costo_hora']*$data['tiempo_horas']
                                + ($data['n_fichas'] * $data['costo_ficha']);
        $service->update($data);
        $service->refresh();
        return new ServiceResource($service);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Service $service)
    {
        //
        $service->delete();
        return response()->json(['message' => 'Service deleted successfully']);
    }

    public function addUserToService(Request $request, Service $service)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);

        $user = User::find($request->input('user_id'));
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $service->addUser($user);
        $service->refresh();
        return response()->json(['message' => 'User added to service successfully',
            'service' => new ServiceResource($service)]);
    }

    public function removeUserFromService(Request $request, Service $service)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);

        $user = User::find($request->input('user_id'));
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $service->removeUser($user->id);
        $service->refresh();
        return response()->json(['message' => 'User removed from service successfully',
            'service' => new ServiceResource($service)]);
    }

    public function addchat(Service $service, Request $request)
    {

    $data = $request->validate([
        'message' => 'nullable|string',
        'file' => 'nullable|file',
    ]);
        //$request->validate([
        //    'message' => 'required|string',
        //]);

        $chat = $service->chat()->create([
            //'messages' => $request->input('message'),
        ]);

        //dd('hola');

        $chat->addMessage($request->input('message'), $request->file('file'));  


        return response()->json(['message' => 'Chat added to service successfully',
            'chat' => $chat]);
    }

    public function addMessageToChat(Service $service, Request $request)
    {
        $request->validate([
            'message' => 'nullable|string',
             'file' => 'nullable|file',
        ]);

        $chat = $service->chat;
        if (!$chat) {
            return response()->json(['error' => 'Chat not found for this service'], 404);
        }

        $message_id  = random_int(100000, 999999);

        $messages = json_decode($chat->messages ?? '[]', true);
        $newMessage = [
            'id' => $message_id,
            'message' => $request->input('message'),
            'created_at' => now()->toDateTimeString(),
            'user_id' => auth('api')->id(),
            'user_name' => auth('api')->user()->name,
        ];
        $messages[$message_id] = $newMessage;

        $chat->messages = json_encode($messages, JSON_FORCE_OBJECT);
        
        $chat->save();
        $chat->refresh();

        return response()->json(['message' => 'Message added to chat successfully',
            'chat' => $chat]);
    }

    public function removeMessageFromChat(Service $service, Request $request)
    {
        $request->validate([
            'message_id' => 'required|integer',
        ]);

        $chat = $service->chat;
        if (!$chat) {
            return response()->json(['error' => 'Chat not found for this service'], 404);
        }

        $messages = json_decode($chat->messages ?? '[]', true);
        $messageId = $request->input('message_id');

        if (!isset($messages[$messageId])) {
            return response()->json(['error' => 'Message not found in chat'], 404);
        }

        unset($messages[$messageId]);
        $chat->messages = json_encode($messages, JSON_FORCE_OBJECT);
        
        $chat->save();
        $chat->refresh();

        return response()->json(['message' => 'Message removed from chat successfully',
            'chat' => $chat]);
    }

    public function getChat(Service $service)
    {
        $chat = $service->chat;
        if (!$chat) {
            return response()->json(['error' => 'Chat not found for this service'], 404);
        }

        return response()->json(['chat' => $chat]);
    }

    function getMessages(Service $service)
    {
        $chat = $service->chat;
        if (!$chat) {
            return response()->json(['error' => 'Chat not found for this service'], 404);
        }

        $messages = json_decode($chat->messages ?? '[]', true);

        return response()->json(['messages' => $messages]);
    }

    function changestate(Service $service, Request $request)
    {
        $data = $request->validate([
            'estado' => 'required|integer|in:1,2,3,4,5',
        ]);

        $permisosEstado = [
            1 => 'servicio.asignar',
            2 => 'servicio.ejecutar',
            3 => 'servicio.supervisar',
            4 => 'servicio.procesar',
            5 => 'servicio.pagar',
        ];
        $user = auth('api')->user();

        if (!$user || !$user->can($permisosEstado[$data['estado']])) {
            return response()->json(['error' => 'Unauthorized to change service state',
                'service' => new ServiceResource($service)], 403);
        }

        $service->estado = $data['estado'];
        $service->save();

        return response()->json(['message' => 'Service state changed successfully',
            'service' => new ServiceResource($service)]);
    }


}
