<?php

namespace spec\Madkom\Docker\Creator\Builder\Parser;

use Madkom\Docker\Creator\Builder\Parser\ExpressionParser;
use Madkom\Docker\Creator\Builder\Parser\PreExpressionParser;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * Class PreExpressionParserSpec
 * @package spec\Madkom\Docker\Creator\Builder\Parser
 * @author  Dariusz Gafka <d.gafka@madkom.pl>
 * @mixin PreExpressionParser
 */
class PreExpressionParserSpec extends ObjectBehavior
{

    function let(ExpressionLanguage $expressionLanguage)
    {
        $this->beConstructedWith($expressionLanguage, []);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ExpressionParser::class);
    }

    function it_should_return_0_priority()
    {
        $this->priority()->shouldReturn(0);
    }

}
