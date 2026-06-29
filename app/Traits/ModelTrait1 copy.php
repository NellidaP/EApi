<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use ReflectionClass;

trait ModelTrait1
{
    

    //Sección de Borrar un Item de la tabla stocktaking_items
    public function deleteTagJson( $tag, $column='data' ){
        $query = "UPDATE ".$this->table." SET ".$column." = JSON_REMOVE(".$column.", '$.".$tag."') WHERE id = ".$this->id;
        $json = DB::update($query);
        return $query;
    }


    //Sección Para agragar un item a la tabla 
    public function setTagJson($tag, $object, $column='data'){
        $query = "UPDATE ".$this->table." SET ".$column." = JSON_SET(".$column.", '$.".$tag."' , JSON_EXTRACT('".$object."', '$')) WHERE id = ".$this->id;
        $json = DB::update($query);
        return $query;
    }

    //Setear una matriz en formato JSON de la tabla 
    public function setDataJson($matriz, $path='data', $column='data'){

        $object = json_encode($matriz, JSON_FORCE_OBJECT);
        $query = "UPDATE ".$this->table." SET ".$column." = JSON_SET(".$column.", '$.".$path."' , JSON_EXTRACT('".$object."', '$')) WHERE id = ".$this->id;
        //return $query;
        $json = DB::update($query);
        return $query;

    }

    //Agregar un item a la tabla  por un formulario
    public function additem( Request $request , $path='data', $column='data' ){
        
        $data = $request->all();
        $data['id'] = (Str::random(6)) . (Str::subStr(time(), -6));
        $data['user_id'] = auth()->user()->id;
        $model = strtolower((new ReflectionClass($this::class))->getShortName());

        $data['archivos'] = [];

        if ($request->hasFile('file')) {
            $data['archivos'][] = [
                'url' => self::saveFile($request->file('file'), $model),
                'origname' => $request->file('file')->getClientOriginalName(),
            ];
            unset($data['file']);
        } elseif ($request->hasFile('files')) {
            foreach ($request->file('files') ?? [] as $file) {
                $sid = (Str::random(6)) . (Str::subStr(time(), -6));
                $data['archivos'][$sid] = [
                    'url' => self::saveFile($file, $model),
                    'origname' => $file->getClientOriginalName(),
                    'id' => $sid,
                ];
            }
            unset($data['files']);
        }

        $data['act'] = true;
        unset($data['_token'], $data['SUBMIT']);

        $this->setDataJson($data, $path . "." . $data['id'], $column);

        return $data;
    }

    //Agregar un item a la tabla 
    public function addmatriz(  $data , $path, $column='data' ){
        
        //$data = $request->all();
        $data['id'] = (Str::random(6)) . (Str::subStr(time(), -6));
        $data['user_id'] = auth()->user()->id;

        $this->setDataJson($data, $path . "." . $data['id'], $column);

        return true;
    }

    //Sección de Borrar un Item de la tabla stocktaking_items junto con sus archivos
    public function deleteitem( $tag, $path='data', $column='data' ){

        $this->refresh();
        $model = strtolower((new ReflectionClass($this::class))->getShortName());
        $data = $this->getDataJson($path);
            if(isset($data[$tag]['archivos'])){
                foreach ($data[$tag]['archivos'] as $key => $value) {
                    if(isset($value['url'])){
                        Storage::disk('public')->delete($model, $value['url']);
                    }
                }
            }
        
        $query = "UPDATE ".$this->table." SET ".$column." = JSON_REMOVE(".$column.", '$.".$path.".".$tag."') WHERE id = ".$this->id;
        $json = DB::update($query);
        return $query;
    }

    /**
     * Agrega archivos a un registro JSON en la base de datos.
     *
     * Esta función procesa archivos enviados en la petición HTTP y los guarda
     * en el disco público asociado al modelo. Luego actualiza el bloque JSON
     * especificado con los metadatos del archivo, incluyendo URL y nombre
     * original.
     *
     * @param Request $request Solicitud HTTP que contiene el archivo o archivos.
     * @param string $path   Ruta JSON donde se insertará el nuevo elemento.
     * @param string $column Nombre de la columna que contiene el JSON.
     * @return array Datos del elemento insertado, incluyendo el ID generado y los archivos.
     */
    public function addfiles( Request $request , $variable, $path='data', $column='data'){
        
        $data = $request->all();

        if($this->$column == null){
            $this->$column = json_encode([$path => []]);
            $this->save();
            $this->refresh();
        }

        //dd($data);
        //$data['id'] = (Str::random(6)) . (Str::subStr(time(), -6));
        $model = strtolower((new ReflectionClass($this::class))->getShortName());
        $data[$variable] = $this->getItemDataJson($path.".".$variable, $column, $this->table)?
                            $this->getItemDataJson($path.".".$variable, $column, $this->table):[ ];
        //dd($data);
        //dd($this->getItemDataJson($path, $column, $this->table));
        if ($request->hasFile('file')) {
            $sid = (Str::random(6)) . (Str::subStr(time(), -6));
            $data[$variable][$sid] = [
                'url' => self::saveFile($request->file('file'), $model),
                'origname' => $request->file('file')->getClientOriginalName(),
                'id' => $sid,
            ];
            
            
            unset($data['file']);
        } elseif ($request->hasFile('files')) {
            foreach ($request->file('files') ?? [] as $index => $file) {
                $sid = (Str::random(6)) . (Str::subStr(time(), -6));
                $data[$variable][$sid] = [
                    'url' => self::saveFile($file, $model),
                    'origname' => $file->getClientOriginalName(),
                    'id' => $sid,
                    
                ];
                
            }
            
            unset($data['files']);
        }
        $data['name'] = $request->input('name', null);
        $data['description'] = $request->input('description', null);

        //$data['act'] = true;
        unset($data['_token'], $data['SUBMIT']);

         $query = "UPDATE ".$this->table." SET ".$column." = JSON_SET(".$column.", '$.".$path."' , JSON_EXTRACT('".json_encode($data)."', '$')) WHERE id = ".$this->id;
        $json = DB::update($query);
        $this->refresh();
        //$this->setDataJson($data,  $path , $column);

        return $data;

    }

    //Borra un archivo de un path
    public function deletefile(   $fileid , $variable ,$path='data', $column='data'){
        $file = $this->getItemDataJson($path.".".$variable, $column, $this->table);
        if($file == null){
            return null;
        }
        $this->refresh();
        $model = strtolower((new ReflectionClass($this::class))->getShortName());

        if($path == null){
            $data = $this->getDataJson(null, $column);
        }else{
            $data = $this->getDataJson($path, $column);
        }
        dd($data);
        //$data = $this->getDataJson($path);
            if(isset($data[$variable]['archivos'])){
                    if(isset($data[$variable]['archivos'][$fileid]['url'])){
                        Storage::disk('public')->delete($model, $file['url']);
                    }
            }


        if($path == null){
            $query = "UPDATE ".$this->table." SET ".$column." = JSON_REMOVE(".$column.", '$.".$variable.".archivos.".$fileid."') WHERE id = ".$this->id;
        }else{
            $query = "UPDATE ".$this->table." SET ".$column." = JSON_REMOVE(".$column.", '$.".$path.'.'.$variable.".".$fileid."') WHERE id = ".$this->id;
        }
        
        //$query = "UPDATE ".$this->table." SET ".$column." = JSON_REMOVE(".$column.", '$.".$path.'.'.$variable.".".$fileid."') WHERE id = ".$this->id;
        $json = DB::update($query);
        return true;
    }

    //Obtener un bloque en formato JSON de la tabla stocktaking_items
    public function getDataJson($block='data', $column='data'){
        $this->refresh();

        if($block == null){
            return json_decode(json_encode(json_decode($this->$column)), true);
        }else{
             return json_decode(json_encode(json_decode($this->$column)->$block), true);
        }

       

    }

    //Obtiene items en formato JSON de la tabla stocktaking_items
    public function getDataJson2($block='items', $column='data'){
        $this->refresh();
        $data= json_decode(json_encode(json_decode($this->$column)), true);

        if(isset($data[$block]) == false){
            return null;
        }
        return $data[$block];

    }

    //Toma los items desde el JSON y los actualiza con la matriz dada
    public function getItemsDataJson(){
        $this->refresh();
        return json_decode(json_encode(json_decode($this->data)->items), true);

    }

    //Toma un item especifico desde el JSON y lo actualiza con la matriz dada
    public function getItemDataJson($path, $column='data', $table=null){
        if($table == null){
            $table = $this->table;
        }
            $query = "SELECT JSON_EXTRACT(".$column." , '$.".$path."') AS dato FROM ".($table)." WHERE id = ".$this->id;
            /* SELECT  JSON_EXTRACT(DATA , '$.items.uZWbEz433995') AS uZWbEz433995 FROM stocktakings */
            //$query = "SELECT JSON_EXTRACT('data', '$.".$block.".".$item."') AS dato FROM ".$this->table." WHERE id = ".$this->id;
            $json = DB::select($query);
            return $json[0]->dato?json_decode(json_encode(json_decode($json[0]->dato)), true):null;
            

    }

    // Accesorios
    //
    public function getMaxJsonValue($stag,$block='items'){
        $this->refresh();
        $data = $this->getDataJson($block);
        if(count($data) == 0){
            return null;
        }
        $ftag = array_key_first($data);
        $comp = $data[array_key_first($data)][$stag];
        foreach ($data as $key => $value) {
            if ($value[$stag] > $comp) {
                $comp = $value[$stag];
                $ftag = $key;
            }
        }
        return $data[$ftag];
    }

    public static function OrdenarMatrizColumna(array $MatrizRegistros, $Columna = false, $Orden = false) {
        if (is_array($MatrizRegistros) == true and $Columna == true and $Orden == true) {
            $Orden = ($Orden == "ASC") ? SORT_ASC : SORT_DESC;
            foreach ($MatrizRegistros as $Arreglo) {
                $Lista[] = $Arreglo[$Columna];
            }
            array_multisort($Lista, $Orden, $MatrizRegistros);
            return $MatrizRegistros;
        }
    }

    public static function OrdenarJsonColumna( $MatrizRegistros, $tag = false, $Orden = false) {
        $matriz = [];
        foreach ($MatrizRegistros as $key => $row) {
            $matriz[$key] = [ $row , $row[$tag] ];
        }
        //return $matriz;
        return array_column( self::OrdenarMatrizColumna($matriz, 1 ,'DES') ,0);
    }

    public static function saveFile($file, $carpeta, $size = 240) {
        $imageName = time() . '-' . $file->getClientOriginalName();

        if ($file->isValid() && in_array($file->extension(), ['jpg', 'jpeg', 'png'])) {
            list($width, $height) = getimagesize($file);
            $new_width = $size;
            $new_height = floor($height * $new_width / $width);

            $image = ($file->extension() === 'png') ? imagecreatefrompng($file) : imagecreatefromjpeg($file);
            $new_image = imagecreatetruecolor($new_width, $new_height);
            imagecopyresampled($new_image, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

            $savePath = public_path('storage/') . $carpeta . '/' . $imageName;
            if ($file->extension() === 'png') {
                imagepng($new_image, $savePath);
            } else {
                imagejpeg($new_image, $savePath);
            }

            imagedestroy($image);
            imagedestroy($new_image);

            return $carpeta . '/' . $imageName;
        } else {
            $url = Storage::disk('public')->put($carpeta, $file);
            return $url;
        }
    }
}