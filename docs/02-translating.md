# Translating
This library provides a class to translate texts from a given object.

## How to use
Assuming you have a file called `locales/en.json`:
```json
{
    "start":{
        "text": "<b>Hi {{MENTION}}!</b>",
        "keyboard": {
            "inline_keyboard": [
                [{
                    "text": "Awesome Library",
                    "url": "https://github.com/TelegramSDK/BotAPI"
                }]
            ]
        }
    }
}
```


```php
<?php
use TelegramSDK\BotAPI\Translator;

$user = $updates->message->from;

$user_language = $user->language_code ?? "en";

if(file_exists("locales/$user_language.json")){
    $file_texts = json_decode(file_get_contents("locales/$user_language.json"));
} else{
    $file_texts = json_decode(file_get_contents("locales/en.json"));
}

$translator = new Translator($file_texts, [
    "{{MENTION}}" => "<a href='tg://user?id=$user->id'>$user->first_name</a>"
]);

$bot->sendMessage([
    "chat_id" => $user->id,
    "text" => $translator->start->text,
    "reply_markup" => json_encode($translator->texts->start->keyboard), // Calling $translator->texts doesn't translate the string and doesn't return a new instance of Translator
    "parse_mode" => "HTML"
]);

```

