<?php

namespace App\telegram;

use DefStudio\Telegraph\Handlers\WebhookHandler;

class Handler extends WebhookHandler
{
    private string $bot_token;

    public function __construct()
    {
        parent::__construct();
        $this->bot_token = env('BOT_TOKEN');
    }

    public function start()
    {
        $this->reply("Начнем Работу");
    }
}
