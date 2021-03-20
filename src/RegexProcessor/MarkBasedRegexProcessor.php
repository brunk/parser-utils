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
    protected $tokenIndex = [];

    /**
     * @inheritDoc
     */
    public function __construct(array $terminals) {
        $this->compileRegex($terminals);
    }


    /**
     * @inheritDoc
     *
     */
    public function setAdditionalModifiers(string $additionalModifiers): void {
        $this->additionalModifiers = $additionalModifiers;
    }

    protected function compileRegex($terminals): void {
        $regexes = [];
        $counter = 0;
        // map MARKs to integers to reduce memory used
        // latter by preg_match
        foreach ($terminals as $regex => $token) {
            $regex = $this->cleanRegex($regex);
            $this->tokenIndex[$counter] = $token;
            $regexes[] = $regex . '(*MARK:' . $counter . ')';
            $counter++;
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
            // map tokenIndex to full TOKEN Name
            $matches['MARK'] = $this->tokenIndex[$matches['MARK']];
            return $matches;
        }

        throw new SyntaxErrorException(
            sprintf(
                'Lexer error: unable to parse character "%s" at line %d, char %d.',
                substr($text, $offset),
                $lineNum,
                $offset
            )
        );
    }
}
