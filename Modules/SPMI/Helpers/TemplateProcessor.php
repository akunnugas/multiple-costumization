<?php

namespace Modules\SPMI\Helpers;

use PhpOffice\PhpWord\TemplateProcessor as BaseTemplateProcessor;

class TemplateProcessor extends BaseTemplateProcessor
{

    public static function setMacroOpeningCharsStatic(string $macroOpeningChars): void
    {
        self::$macroOpeningChars = $macroOpeningChars;
    }
    public static function setMacroClosingCharsStatic(string $macroClosingChars): void
    {
        self::$macroClosingChars = $macroClosingChars;
    }

    public static function setMacroCharsStatic(string $macroOpeningChars, string $macroClosingChars): void
    {
        self::$macroOpeningChars = $macroOpeningChars;
        self::$macroClosingChars = $macroClosingChars;
    }
}
