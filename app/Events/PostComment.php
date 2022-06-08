<?php

namespace App\Events;

use App\Models\Comment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostComment implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;


    public Comment $comment;
    public int $groupId;
    public int $channelId;
    /**
     * Create a new event instance.
     *
     */
    public function __construct(Comment $comment, int $groupId, int $channelId)
    {
        $this->comment = $comment;
        $this->channelId = $channelId;
        $this->groupId = $groupId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel
     */
    public function broadcastOn(): Channel
    {
        return new PrivateChannel('group-'.$this->groupId.'channel-'.$this->channelId);
    }

    public function broadcastAs(): string
    {
        return "post.comment";
    }
}
