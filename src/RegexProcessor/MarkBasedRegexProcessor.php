<?php


namespace Yosymfony\ParserUtils\RegexProcessor;


use RuntimeException;
use Yosymfony\ParserUtils\SyntaxErrorException;

class MarkBasedRegexProcessor implements RegexProcessorInterface {

    public const RECOGNIZED_REGEX_DELIMITERS = '/!#%&,:;=@_~-';
    /**
     * @var string
     */
    protected $compiledRegex;
    protected $additionalModifiers = '';


    /**
     * @inheritDoc
     */
    public function __construct(array $terminals) {
        $this->compileRegex($terminals);
    }


    /**
     * Sets additionalModifiers to add to the regular expression
     *
     * @param string $additionalModifiers
     *
     */
    public function setAdditionalModifiers(string $additionalModifiers): void {
        $this->additionalModifiers = $additionalModifiers;
    }

    protected function compileRegex($terminals): void {
        $regexes = [];

        foreach ($terminals as $regex => $token) {
            $regex = $this->cleanRegex($regex);
            $regexes[] = $regex . '(*MARK:' . $token . ')';
        }

        $this->compiledRegex =
            '~(?|'
            . $this->escapeDelimiter(implode('|', $regexes))
            . ')~A' // force anchored search
            ;
    }

    protected function cleanRegex ($regex) : string {
        // determine delimiter
        $del_candidate = $regex[0];

        // Check for recognized delimiters
        if  (strpos(self::RECOGNIZED_REGEX_DELIMITERS, $del_candidate) === false) {
            return $regex;
        }
        $new_regex = $regex;
        // look for matching trailer
        $new_regex = preg_replace(
            "~(.*){$del_candidate}[imsxeADSUXJu]*\$~",
            '\1',
            $new_regex
        );
        if ($new_regex === $regex) {
            throw new RuntimeException("Regex trailer could not be parsed $regex");
        }

        // Remove leader
        return preg_replace(
            '~^' .  preg_quote($del_candidate, '~') . '\^?(.*)~',
            '\1',
            $new_regex
        );

    }

    private function escapeDelimiter(string $regex): string {
        return str_replace('~', '\~', $regex);
    }

    /**
     * @inheritDoc
     */
    public function match(string $text, int $lineNum, int $offset): array {
        $regex = $this->compiledRegex . $this->additionalModifiers;

        if (preg_match($regex, substr($text, $offset), $matches)) {
            return $matches;
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
}
