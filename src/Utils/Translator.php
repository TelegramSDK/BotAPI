<?php
/**
 * Translator class.
 * This class provides a way to translate texts by replacing specific strings with their corresponding translations.
 *
 * @author Sebastiano Racca
 * @package TelegramSDK\BotAPI\Utils
 * @see docs/02-translating.md
 */

declare(strict_types=1);
namespace TelegramSDK\BotAPI\Utils;


class Translator{
    public ?object $texts;
    public ?array $translations;

    /**
     * Translator constructor.
     *
     * @param object $texts              The object containing the original texts.
     * @param array|null $translations   Associative array of the translations to be applied.
     */
    public function __construct(object $texts, ?array $translations = null){
        $this->translations = $translations;
        $this->texts = $texts;
    }

    /**
     * Translates the given string.
     *
     * @param string $text   The text to be translated.
     * @return string        The translated text.
     */
    public function translate(string $text): string{
        return strtr($text, $this->translations);
    }

    /**
     * Magic method to dynamically access properties.
     *
     * @param string $name   The name of the property.
     * @return mixed         The value of the property, translated if applicable.
     */
    public function __get(string $name): mixed{
        if(!property_exists($this->texts, $name))
            return null;

        $propertyValue = $this->texts->$name;

        if(is_string($propertyValue))
            return $this->translate($propertyValue);

        if(is_object($propertyValue) || is_array($propertyValue)){
            $translator = new Translator($this->texts, $this->translations);
            $translator->texts = $propertyValue;
            return $translator;
        }

        return $propertyValue;
    }

}