<?php

namespace spec\Madkom\Docker\Creator\Builder\Parser;

use Madkom\Docker\Creator\Builder\BuildException;
use Madkom\Docker\Creator\Builder\DockerTemplate;
use Madkom\Docker\Creator\Builder\Parser;
use Madkom\Docker\Creator\Builder\Parser\ExpressionParser;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * Class DockerExpressionParserSpec
 * @package spec\Madkom\Docker\Creator\Builder\Parser
 * @author  Dariusz Gafka <d.gafka@madkom.pl>
 * @mixin ExpressionParser
 */
class ExpressionParserSpec extends ObjectBehavior
{

    function let()
    {
        $expressionLanguage = new ExpressionLanguage();
        $data = [
            'imageName'  => 'registry.com.pl',
            'phpVersion' => '5.6',
            'tag'        => 'cli'
        ];

        $this->beConstructedWith($expressionLanguage, $data);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Parser::class);
    }

    function it_should_return_priority()
    {
        $this->priority()->shouldReturn(2);
    }

    function it_should_parse_expression()
    {
        $dockerTemplate = new DockerTemplate('super-image', file_get_contents(__DIR__ . '/../../../../stubs/DockerfileWithExpression'));

        $template = $this->parse($dockerTemplate);
        $template->shouldNotBe($dockerTemplate);

        $template->content()->shouldReturn(file_get_contents(__DIR__ . '/../../../../stubs/DockerfileWithExpressionReplaced'));
    }

    function it_should_parse_if_statement()
    {
        $dockerTemplate = new DockerTemplate('super-image', file_get_contents(__DIR__ . '/../../../../stubs/DockerfileWithIfExpression'));

        $template = $this->parse($dockerTemplate);
        $template->shouldNotBe($dockerTemplate);

        $template->content()->shouldReturn(file_get_contents(__DIR__ . '/../../../../stubs/DockerfileWithIfExpressionReplaced'));
    }

    function it_should_throw_exception_if_wrong_expression_passed()
    {
        $dockerTemplate1 = new DockerTemplate('super-image', "[*phpVersion = dsa*]");
        $dockerTemplate2 = new DockerTemplate('super-image', "[*notExisting = dsa*]");
        $dockerTemplate3 = new DockerTemplate('super-image', "[*if phpVersion = dar*][*/if*]");

        $this->shouldThrow(BuildException::class)->during('parse', [$dockerTemplate1]);
        $this->shouldThrow(BuildException::class)->during('parse', [$dockerTemplate2]);
        $this->shouldThrow(BuildException::class)->during('parse', [$dockerTemplate3]);
    }

}
