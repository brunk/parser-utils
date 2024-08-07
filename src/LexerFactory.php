<?php


namespace Brunk\ParserUtils;


use Brunk\ParserUtils\RegexProcessor\MarkBasedRegexProcessor;
use Brunk\ParserUtils\RegexProcessor\RegexProcessorInterface;
use Brunk\ParserUtils\RegexProcessor\SimpleRegexProcessor;

class LexerFactory {

    public static function create(RegexProcessorInterface $regexProcessor) : AbstractLexer {
        return new Lexer($regexProcessor);
    }

    public static function createFile(RegexProcessorInterface $regexProcessor) : AbstractLexer {
        return new FileLexer($regexProcessor);
    }

    public static function createMarkBased(array $terminals) : AbstractLexer {
        return self::create(new MarkBasedRegexProcessor($terminals));
    }

    public static function createMarkBasedFile(array $terminals) : AbstractLexer {
        return self::createFile(new MarkBasedRegexProcessor($terminals));
    }

    public static function createSimple(array $terminals) : AbstractLexer {
        return self::create(new SimpleRegexProcessor($terminals));
    }

    public static function createSimpleFile(array $terminals) : AbstractLexer {
        return self::createFile(new SimpleRegexProcessor($terminals));
    }
}
