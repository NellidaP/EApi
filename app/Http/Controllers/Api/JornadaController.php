<?php

namespace App\Http\Controllers\Api;

use App\Models\Jornada;
use App\Http\Requests\StoreJornadaRequest;
use App\Http\Requests\UpdateJornadaRequest;

class JornadaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreJornadaRequest $request)
    {
        //
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
    public function update(UpdateJornadaRequest $request, Jornada $jornada)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Jornada $jornada)
    {
        //
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
