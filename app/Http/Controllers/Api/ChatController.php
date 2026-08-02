<?php

namespace App\Http\Controllers\Api;

use App\Models\Chat;
use App\Http\Requests\StoreChatRequest;
use App\Http\Requests\UpdateChatRequest;
use App\Http\Controllers\Api\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use App\Http\Resources\ChatResource;

class ChatController extends Controller
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
    public function store(Request $request)
    {
        //
        $data = $request->validate([
            'chatable_id' => 'required|integer',
            'chatable_type' => 'required|string',
            'file' => 'nullable|file', // 
            'message' => 'nullable|string', // Optional file upload, max size 10MB
        ]);

        $chateable = $data['chatable_type']::find($data['chatable_id']);

        $chat = $chateable->chat()->create([
            'messages' => json_encode([]),
        ]);

        unset($data['chatable_id'], $data['chatable_type']);

        $data['user_id'] = auth()->id() ?? null;
        $data['user_name'] = auth()->user()->name ?? null;

        $chat->addMessage($data['message'], $request->file('file'));

        return new ChatResource($chat->refresh());


    }

    public function addMessage(Request $request, Chat $chat)
    {
        $data=$request->validate([
            'message' => 'nullable|string',
            'file' => 'nullable|file', // Optional file upload, max size 10MB
        ]);

        $data['user_id'] = auth()->id() ?? null;
        $data['user_name'] = auth()->user()->name ?? null;

        $chat->addMessage($data['message'], $request->file('file'));

        return new ChatResource($chat->refresh()); // Refresh the model instance to get the latest data
    }

    public function deleteMessage(Request $request, Chat $chat)
    {
        $request->validate([
            'index' => 'required|string',
        ]);

        $chat->deleteMessage($request->input('index'));
        
        return new ChatResource($chat->refresh()); // Refresh the model instance to get the latest data
    }

    /**
     * Display the specified resource.
     */
    public function show(Chat $chat)
    {
        //
        return response()->json($chat);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Chat $chat)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Chat $chat)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Chat $chat)
    {
        //
        $chat->delete();
        return response()->json(null, 204);
    }
}
