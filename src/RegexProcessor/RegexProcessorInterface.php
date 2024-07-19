<?php


namespace Brunk\ParserUtils\RegexProcessor;

interface RegexProcessorInterface {
    /**
     * RegexProcessorInterface constructor.
     * @param array $terminals
     */
    public function __construct (array $terminals) ;

    /**
     * Required setter but may be ignored in some processors
     *
     * @param string $additionalModifiers
     * @return void
     */
    public function setAdditionalModifiers(string $additionalModifiers): void;

    /**
     * @param string $text full line of text to evaluate
     * @param int $lineNum
     * @param int $offset
     * @return array contains regex matches AND array['MARK'] containing
     *      the token for the found match
     */
    public function match (string $text, int $lineNum, int $offset) : array;
}
