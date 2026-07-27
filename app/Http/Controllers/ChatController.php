<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use Illuminate\Http\Request;
class ChatController extends Controller
{
    public function index(Request $request)
{
    return view('chats.index');
}
}
