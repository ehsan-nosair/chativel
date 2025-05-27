<?php

namespace EhsanNosair\Chativel\Http\Controllers\Api;

use Carbon\Carbon;
use EhsanNosair\Chativel\Events\ChativelMessageCreated;
use EhsanNosair\Chativel\Events\ChativelMessageRead;
use EhsanNosair\Chativel\Events\ChativelReadAllMessages;
use EhsanNosair\Chativel\Facades\Chativel;
use EhsanNosair\Chativel\Http\Resources\ChatableResource;
use EhsanNosair\Chativel\Http\Resources\ConversationResource;
use EhsanNosair\Chativel\Http\Resources\MessageResource;
use EhsanNosair\Chativel\Models\Chativel\Conversation;
use EhsanNosair\Chativel\Models\Chativel\Message;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;

class ConversationController extends Controller
{
    public function broadcastMyStatus()
    {
        Chativel::sayIamConnected();

        return response()->json([
            'message' => "You say i'm connected",
        ], 200);
    }

    public function myConversations(Request $request)
    {
        Chativel::sayIamConnected();

        return ConversationResource::collection(Chativel::myConversationsPaginator($request->page ?? 1));
    }

    public function getConversation($conversationId)
    {
        $selectedConversation = Conversation::findOrFail($conversationId);
        if (! Chativel::isParticipant($selectedConversation)) {
            return response()->json(['message' => 'You are not a participant in this conversation.'], 403);
        }
        $otherParticipant = Chativel::getOtherParticipant($selectedConversation);

        Chativel::sayIamConnected();
        Chativel::markAllMessagesAsRead($selectedConversation);
        broadcast(new ChativelReadAllMessages($selectedConversation->id))->toOthers();

        return ConversationResource::make(
            $selectedConversation,
            class_basename($otherParticipant->participant_type),
            $otherParticipant->participant_id
        );
    }

    public function conversationMessages($conversationId)
    {
        $selectedConversation = Conversation::with(['participants', 'participants.chatable'])->findOrFail($conversationId);
        if (! Chativel::isParticipant($selectedConversation)) {
            return response()->json(['message' => 'You are not a participant in this conversation.'], 403);
        }

        $messages = $selectedConversation->messages()->with(['statuses', 'media'])->latest()->paginate(10, ['*'], 'page', $request->page ?? 1);

        return MessageResource::collection($messages);
    }

    public function getOtherActivityStatus($conversationId)
    {
        $selectedConversation = Conversation::with(['participants', 'participants.chatable'])->findOrFail($conversationId);
        if (! Chativel::isParticipant($selectedConversation)) {
            return response()->json(['message' => 'You are not a participant in this conversation.'], 403);
        }
        $otherParticipant = Chativel::getOtherParticipant($selectedConversation);
        $lastSeen = $otherParticipant->chatable->lastSeen;
        $status = null;
        if ($lastSeen) {
            if (Carbon::parse($lastSeen->last_seen)->setTimeZone(config('chativel.timezone', 'app.timezone'))->diffInSeconds(Carbon::now(config('chativel.timezone', 'app.timezone'))) <= 61) {
                $status = __('Online');
            } else {
                $status = Carbon::parse($lastSeen->last_seen)->setTimeZone(config('chativel.timezone', 'app.timezone'))?->diffForHumans();
            }
        }

        return response()->json(['status' => $status]);
    }

    public function getMessage($conversationId, $messageId)
    {
        $selectedConversation = Conversation::with(['participants', 'participants.chatable'])->findOrFail($conversationId);
        if (! Chativel::isParticipant($selectedConversation)) {
            return response()->json(['message' => 'You are not a participant in this conversation.'], 403);
        }
        $message = Message::with(['statuses', 'media'])->find($messageId);
        Chativel::markMessageAsRead($message);

        broadcast(new ChativelMessageRead($selectedConversation->id, $message->id))->toOthers();

        return MessageResource::make($message);
    }

    public function sendMessage(Request $request, $conversationId)
    {
        $selectedConversation = Conversation::with(['participants', 'participants.chatable'])->findOrFail($conversationId);
        if (! Chativel::isParticipant($selectedConversation)) {
            return response()->json(['message' => 'You are not a participant in this conversation.'], 403);
        }
        $otherParticipant = Chativel::getOtherParticipant($selectedConversation);

        $validator = Validator::make($request->all(), [
            'message' => ['nullable', 'string', 'max:2000'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['required', 'file', 'mimes:jpeg,png,jpg,pdf,docx,xlsx', 'max:5000'],
        ]);
        $validator->after(function ($validator) use ($request) {
            if (! $request->message && count($request->attachments ?? []) == 0) {
                $validator->errors()->add('message', __('Empty message cannot be sent.'));
            }
        });
        $validator->validate();

        $newMessage = Chativel::sendMessage($validator->validated(), $selectedConversation, $otherParticipant);

        broadcast(new ChativelMessageCreated($selectedConversation->id, $newMessage->id))->toOthers();

        return MessageResource::make($newMessage);
    }

    public function chatablesSearch(Request $request)
    {
        $chatables = Chativel::chatablesSearch($request->searchTerm ?? '');

        return ChatableResource::collection($chatables);
    }

    public function getConversationWith(Request $request)
    {
        $chatableType = str_replace('\\\\', '\\', $request->type);
        $chatableId = $request->id;

        $validator = Validator::make([], []);

        $validator->after(function ($validator) use ($chatableType, $chatableId) {
            if (! in_array($chatableType, config('chativel.chatables', []))) {
                $validator->errors()->add('type', __('Invalid type'));
            } else {
                $model = $chatableType::find($chatableId);
                if (! $model) {
                    $validator->errors()->add('type', __('Chatable not found'));
                }
            }
        });

        $validator->validate();

        $conversation = Chativel::getConversationWith($chatableType, $chatableId);

        return ConversationResource::make($conversation);
    }
}
