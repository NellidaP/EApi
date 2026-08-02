<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Models\Api;
use App\Traits\ModelTrait1;

class Chat extends Api
{
    /** @use HasFactory<\Database\Factories\ChatFactory> */
    use HasFactory, ModelTrait1;

    protected $fillable = [
        'chatable_id',
        'chatable_type',
        'messages',
        'name',
    ];

    public function chatable()
    {
        return $this->morphTo();
    }

    public function addMessage($message, $file = null)
    {
        $messages = json_decode($this->messages, true) ?? [];
        if($file) {
            $url = $this->saveFile($file, 'chat');
            
        } else {
            $url = null;
            
        }
        $id = now()->toDateTimeString();

        if ($url) {

            $messages[$id] = [
                'id' => $id,
                'user_id' => auth()->id() ?? null,
                'user_name' => auth()->user()->name ?? null,
                'message' => $message,
                'url' => $url,
            ];
        
            
        }else {
            
            $messages[$id] = [
                'id' => $id,
                'user_id' => auth()->id() ?? null,
                'user_name' => auth()->user()->name ?? null,
                'message' => $message,
                'url' => null,
            ];
            
        }
        $this->messages = json_encode($messages);
        $this->save();
    }

    public function deleteMessage($index)
    {
        $messages = json_decode($this->messages, true)?? [];
        //dd($messages);
        if (isset($messages[$index])) {
            $url = $messages[$index]['url'] ?? null;

            if( $url) {
                Storage::disk('public')->delete('chats', $url);
            }
            unset($messages[$index]);
            $this->messages = json_encode($messages);
            $this->save();
        }
    }
}
