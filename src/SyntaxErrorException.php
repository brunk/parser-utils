<?php
/*
 * This file is part of the Brunk\ParserUtils package.
 *
 * (c) YoSymfony <http://github.com/yosymfony>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Brunk\ParserUtils;

use Exception;
use RuntimeException;

/**
 * Exception thrown when an error occurs during parsing or tokenizing
 */
class SyntaxErrorException extends RuntimeException
{

    /**
     * Constructor
     *
     * @param string $message The error messsage
     * @param Token|null $token The token
     * @param \Exception|null $previous The previous exceptio
     */
    public function __construct(string $message, Token $token = null, Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }

}
