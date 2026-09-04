<?php

namespace App\Http\Controllers\Api;

use App\Models\Jornada;
use App\Http\Requests\StoreJornadaRequest;
use App\Http\Requests\UpdateJornadaRequest;
use Illuminate\Http\Request;
use App\Models\Unity;
use Carbon\Carbon;
use App\Http\Resources\JornadaResource;
use App\Http\Controllers\Api\Controller;

class JornadaController extends Controller
{
    /**
     * Display a listing of the resource 01.
     */
    public function index()
    {
        //
        $userdata_type = auth('api')->user()->type;
        if(auth('api')->user()->can('jornada.index') ){
            $jornadas = JornadaResource::collection(Jornada::getOrPaginate());

            return $jornadas;
        }elseif($userdata_type == 3 ){
            $unityIds = auth('api')->user()->unities()
                ->wherePivot('type', 0)->get()
                ->pluck('id');

            $jornadas = JornadaResource::collection(
                Jornada::whereIn('unity_in_id', $unityIds)->getOrPaginate()
            );

            //return [2];

            return $jornadas;
        }
        else{
            $jornadas = JornadaResource::collection(
                Jornada::where('user_id', auth('api')->user()->id)
                    ->whereBetween('fechahora_ini', [now()->subMonth(), now()])
                    ->getOrPaginate()
            );
            return  $jornadas;
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function unities(Request $request)
    {
        //

        $data = $request->validate([
            'latitud' => 'required|numeric',
            'longitud' => 'required|numeric',
        ]);

        
        $data['user_id'] = auth('api')->user()->id;
        //$unities = Unity::where('unity_id',1)->get();
        $unities = Unity::all();
        

        $ultimaj =  Jornada::where('user_id', auth('api')->user()->id)
                                ->where('ent',0)   
                                ->orderBy('fechahora_ini','desc')->first();
        if(!$ultimaj){
            if($ultimaj && -now()->diffInHours(Carbon::parse($ultimaj->fechahora_ini))>15){
                $ultimaj = null;
            }
            
        }else{
            if(-now()->diffInMinutes(Carbon::parse($ultimaj->fechahora_ini)) < 10){
                $addmessage='Hay una Jornada iniciada en ['.$ultimaj->unityIn->name.'] y debe esperar '.
                            round(10 + now()->diffInMinutes(Carbon::parse($ultimaj->fechahora_ini)), 0).
                            ' minutos para cerrarla';
            
            }
        }
        

        $unids = collect([]);
            
            foreach ($unities as $unidad) {
                $dist =  sqrt(pow($request->latitud-$unidad->latitud,2)+
                pow($request->longitud-$unidad->longitud,2));
                
                $mult=(float) str_replace(',','.',$unidad->mult);
                
                if($dist<.0015*$mult){
                    
                    $unids->push(['id' => $unidad->id, 'name' => $unidad->name, 'dist' => $dist*100000, 'parent_name' => $unidad->parent?->name]);
                    //break;
                }
            }
        

                
            if(count($unids)==0) {
                return response()->json(['message' => 'No se encuentra en ninguna ubicación válida'], 400);
            }

            return response()->json(['message' => 'Se encuentra en varias ubicaciones: ',
                                        'ultimaj_uni_name' => $ultimaj ? $ultimaj->unityIn?->name : null,
                                        'addmessage' => $addmessage ?? null,
                                        'unities' => $unids], 200);


    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        //dd($request->all());
        $data = $request->validate([
            'latitud' => 'required|numeric',
            'longitud' => 'required|numeric',
            'unity_id' => 'required|integer|exists:unities,id',
            'file'=>'required|file|mimes:jpg,jpeg,png'
                    ]);


        
        $data['user_id'] = auth('api')->user()->id;
        $unity = Unity::find($request->unity_id);
            if(!$unity){
                return response()->json(['error' => 'La unidad especificada no existe'], 400);
            }
            $dist =  sqrt(pow($request->latitud-$unity->latitud,2)+
            pow($request->longitud-$unity->longitud,2));
            $mult=(float) str_replace(',','.',$unity->mult);
        
            if($dist<.0015*$mult){
                $entra = true;
            }

        if (isset($entra) && $entra ){ 

            
            $ultimaj =  Jornada::where('user_id', auth('api')->user()->id)
                                ->where('ent',0)   
                                ->orderBy('fechahora_ini','desc')->first();

            $url = Jornada::saveFile($request->file('file'), 'jornada', 240);
            
            if(!$ultimaj){
                $jornadan = Jornada::create([
                    'latitud' => $request->latitud,
                    'longitud' => $request->longitud,
                    'unity_in_id' => $unity->id,
                    'user_id' => auth('api')->user()->id,
                    'url_in' => $url,
                    'url_out' => 'no',
                    'ent' => 0,
                    'fechahora_ini' => now()->toDateTimeString()
                ]);
                $message='Se ha iniciado una nueva Jornada en ['.$unity->name.']';
                return response()->json(['message' => $message], 200);
            }else{
                //dd(-now()->diffInHours(Carbon::parse($ultimaj->fechahora_ini)));
                if ( -now()->diffInMinutes(Carbon::parse($ultimaj->fechahora_ini)) < 10){
                    $unityin = Unity::find($ultimaj->unity_in_id);
                    return response()->json(['error' => 'Hay una Jornada iniciada en ['.$unityin->name.'] y debe esperar '.
                                                        round(10 + now()->diffInMinutes(Carbon::parse($ultimaj->fechahora_ini)), 0).
                                                        ' minutos para cerrarla', 
                                                'jornada_id' => $ultimaj->id,
                                                'unity_in' => $unityin->name,
                                                'error_type' => 'time_constraint'], 400); 
                }
                if(-now()->diffInHours(Carbon::parse($ultimaj->fechahora_ini))>15){
                    $jornadan = Jornada::create([
                        'latitud' => $request->latitud,
                        'longitud' => $request->longitud,
                        'unity_in_id' => $unity->id,
                        'user_id' => auth('api')->user()->id,
                        'url_in' => $url,
                        'url_out' => 'no',
                        'ent' => 0,
                        'fechahora_ini' => now()->toDateTimeString()
                    ]);
                    $message='Se ha iniciado una nueva Jornada en ['.$unity->name.']';
                    return response()->json(['message' => $message], 200);
                }

                if($ultimaj->unity_in_id != $unity->id){
                    $jornadan = Jornada::create([
                        'latitud' => $request->latitud,
                        'longitud' => $request->longitud,
                        'unity_in_id' => $unity->id,
                        'user_id' => auth('api')->user()->id,
                        'url_in' => $url,
                        'url_out' => 'no',
                        'ent' => 0,
                        'fechahora_ini' => now()->toDateTimeString()
                    ]);
                    $message='Se tenia una Jornada Iniciada en ['.$ultimaj->unityIn->name.'], pero se ha iniciado una nueva Jornada en ['.$unity->name.']';
                    return response()->json(['message' => $message], 200); 
                }

                $ultimaj->update([
                    'url_out' => $url,
                    'ent' => 1,
                    'fechahora_fin' => now()->toDateTimeString(),
                    'unity_out_id' => $unity->id,
                    ]);
                $message='Se ha cerrado la Jornada iniciada en: ['.$unity->name.'] en fecha '.$ultimaj->fechahora_ini;
                return response()->json(['message' => $message], 200);
            }
        } else{
            $coordenadas = $request->latitud.','.$request->longitud;
            return response()->json(['error' => 'No se encuentra en una ubicación válida', 'latitud'=>$request->latitud,
                                        'longitud'=>$request->longitud,
                                        'error_type' => 'location_constraint'], 400);
        }
        
    }

    public function create(Request $request)
    {
        //
        $data = $request->validate([
            'unity_in_id' => 'required|integer|exists:unities,id',
            'unity_out_id' => 'nullable|integer|exists:unities,id',
            'user_id' => 'required|integer|exists:users,id',
            'fechahora_ini' => 'required|date',
            'fechahora_fin' => 'nullable|date|after:fechahora_ini',
            'comentario' => 'nullable|string',
            
        ]);

        $data['ent'] = 1;
        $data['url_in'] = 'no';
        $data['url_out'] = 'no';
        $data['aprobador_id'] = auth('api')->user()->id;
        return response()->json(['message' => 'Jornada creada exitosamente',
                            'jornada' => Jornada::create($data)], 200);
    }



    /**
     * Display the specified resource.
     */
    public function show(Jornada $jornada)
    {
        //


        
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Jornada $jornada)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Jornada $jornada)
    {
        //dd($request->all());
        //
        $data = $request->validate([
            'unity_in_id' => 'sometimes|required|integer|exists:unities,id',
            'unity_out_id' => 'sometimes|nullable|integer|exists:unities,id',
            'user_id' => 'sometimes|required|integer|exists:users,id',
            'fechahora_ini' => 'sometimes|required|date',
            'fechahora_fin' => 'sometimes|nullable|date|after:fechahora_ini',
            'comentario' => 'sometimes|nullable|string',
            
        ]);
        $jornada->update($data);
        //dd($data);
        $jornada->refresh();
        return response()->json(['message' => 'Jornada 3 actualizada exitosamente',
                            'jornada' => $jornada], 200);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Jornada $jornada)
    {
        //
        $jornada->delete();
        return response()->json(['message' => 'Jornada eliminada exitosamente'], 200);
    }

        public function geoloc(Request $request)
    {
       dd($request->all());
        $request->validate(['latitud'=>'required',
                            'longitud'=>'required',
                            'unity_id' => 'sometimes|required|integer|exists:unities,id',
                            'file'=>'required|file|mimes:jpg,jpeg,png|max:2048']);

        $unities = Unity::where('unity_id',1)->get();
        $entra = false;
        $unity =null;
        
        $donde ="";
        
        if($request->has('unity_id') && $request->unity_id>0){
            $unity = Unity::find($request->unity_id);
            if(!$unity){
                return response()->json(['error' => 'La unidad especificada no existe'], 400);
            }
            $dist =  sqrt(pow($request->latitud-$unity->longitud,2)+
            pow($request->longitud-$unity->latitud,2));
            $mult=(float) str_replace(',','.',$unity->mult);
        
            if($dist<.0015*$mult){
                $entra = true;
            }
            $donde = $unity->name;//.' ('.($dist*1000).') '.' '.$request->longitud.' '.$request->latitud.' '.$unidad->longitud.' '.$unidad->latitud;
            $unity = $unity->id;
        }else{
            
            $unids = collect([]);
            
            foreach ($unities as $unidad) {
                $dist =  sqrt(pow($request->latitud-$unidad->longitud,2)+
                pow($request->longitud-$unidad->latitud,2));
                
                $mult=(float) str_replace(',','.',$unidad->mult);
                
                if($dist<.0015*$mult){
                    
                    $unids->push([$unidad->id, $unidad->name, $dist*100000]);
                    //break;
                }
            }
            if(count($unids)>1) {
                //return $unids;
                return response()->json(['message' => 'Se encuentra en varias ubicaciones: '.json_encode($unids),
                                        'unities' => $unids], 200);
            }else if(count($unids)==0) {
                return response()->json(['message' => 'No se encuentra en ninguna ubicación válida'], 400);
            }
        }
        

        
        if ($entra ) { 
            $request->validate([
                'latitud' => 'required',
                'longitud' => 'required',
                'file' => 'required'
            ]);
            $ultimaj =  Jornada::where('user_id', auth()->user()->id)->where('ent',0)->orderBy('created_at','desc')->first();
            $url = Jornada::saveFile($request->file('file'), 'jornada', 240);
            //return $url;
            //$url = Storage::disk('public')->put('jornada', $request->file('file'));
            if($ultimaj){
                if( now()->diffinHours(Carbon::parse($ultimaj->created_at))>15 ){
                /* if( date("Y-m-d")<>date("Y-m-d", strtotime($ultimaj->created_at)) ){ */
                    $jornadan = Jornada::create([
                        'latitud' => $request->latitud,
                        'longitud' => $request->longitud,
                        'unity_in_id' => $unity,
                        'user_id' => auth()->user()->id,
                        'url' => $url,
                        'url2' => 'no',
                        'ent' => 0,
                        'fechahora_ini' => now()->toDateTimeString()
                    ]);
                    $message='Se ha iniciado una nueva Jornada en '.$donde;
                    return response()->json(['message' => $message], 200);
                }else{
                    $ultimaj->update(['url2' => $url,
                    'ent' => 1,
                    'fechahora_fin' => now()->toDateTimeString(),
                    'unity_out_id' => $unity,
                        ]);
                    $message='Se ha cerrado la Jornada iniciada '.$ultimaj->created_at.' '.$donde ;
                    return response()->json(['message' => $message], 200);
                }
            }else{
                $jornadan = Jornada::create([
                    'latitud' => $request->latitud,
                    'longitud' => $request->longitud,
                    'unity_in_id' => $unity,
                    'user_id' => auth()->user()->id,
                    'url' => $url,
                    'url2' => 'no',
                    'ent' => 0,
                    'fechahora_ini' => now()->toDateTimeString()
                ]);
                $message='Se ha iniciado una nueva Jornada en '.$donde;
                return response()->json(['message' => $message], 200);
            }
            
        }else{
            $coordenadas = $request->latitud.','.$request->longitud;
            return response()->json(['error' => 'No se encuentra en una ubicación válida', 'coordenadas'=>$coordenadas], 400);
        }
    }
}
