<?php


namespace Yosymfony\ParserUtils\RegexProcessor;


use Yosymfony\ParserUtils\SyntaxErrorException;

class SimpleRegexProcessor implements RegexProcessorInterface {
    /**
     * @var array
     */
    private $terminals;

    /**
     * @inheritDoc
     */
    public function __construct(array $terminals) {
        $this->terminals = $terminals;
    }

    /**
     * @inheritDoc
     */
    public function match(string $text, int $lineNum, int $offset): array {
        foreach ($this->terminals as $pattern => $name) {
            if (preg_match($pattern, substr($text, $offset), $matches)) {
                $matches['MARK'] = $name;
                return $matches;
            }
        }

        throw new SyntaxErrorException(
            sprintf(
                'Lexer error: unable to parse character "%s" at line %d, char %d.',
                $text,
                $lineNum,
                $offset
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function setAdditionalModifiers(string $text) {
        // Ignore the value since modifiers are added to individual regex
    }
}
