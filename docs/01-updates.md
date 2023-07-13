# Getting Updates
As mentioned in the introduction, the `Bot` class provides a method to get updates.

There are 3 updates methods:
* `Bot::NO_UPDATES`: No updates are expected from Telegram, is set to default.
* `Bot::UPDATES_FROM_GET_UPDATES`: Get updates from the `getUpdates()` method.
* `Bot::UPDATES_FROM_WEBHOOK`: Get updates from [`php://input`](https://www.php.net/manual/en/wrappers.php.php#wrappers.php.input).

## How to use them
```php
<?php

use TelegramSDK\BotAPI\Telegram\Bot;

// With getUpdates()
$bot1 = new Bot("YOUR_BOT_TOKEN", Bot::UPDATES_FROM_GET_UPDATES);

$updates = $bot1->updates();
foreach($updates->result as $update){
    echo json_encode($update) . "\n";
}


// With a webhook
$bot2 = new Bot("YOUR_BOT_TOKEN", Bot::UPDATES_FROM_WEBHOOK);

$update = $bot2->updates();
echo (json_encode($update) ?? "No updates found.") . "\n";
```

## Default Updates
The library provides general default updates to use

```php
$updates = $bot->updates(true);
```

Here's a list of the currently available general updates:
* [`user`](https://core.telegram.org/bots/api#user): The user that performed the action.
* [`chat`](https://core.telegram.org/bots/api#chat): The chat where the action was performed.