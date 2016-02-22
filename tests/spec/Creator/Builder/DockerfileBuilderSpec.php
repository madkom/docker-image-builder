<?php

namespace spec\Madkom\Docker\Creator\Builder;

use Madkom\Docker\Creator\Builder\DockerTemplate;
use Madkom\Docker\Creator\Builder\Parser;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class ImageBuilderSpec
 * @package spec\Madkom\Docker\Creator\Builder
 * @author  Dariusz Gafka <d.gafka@madkom.pl>
 * @mixin \ImageBuilder
 */
class ImageBuilderSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Madkom\Docker\Creator\Builder\ImageBuilder');
    }

    function it_should_call_registered_parsers_in_order(
        Parser $parser1, Parser $parser2, Parser $parser3,
        DockerTemplate $template, DockerTemplate $template1, DockerTemplate $template2, DockerTemplate $template3
    )
    {
        $parser1->priority()->willReturn(1);
        $parser2->priority()->willReturn(2);
        $parser3->priority()->willReturn(3);

        $parser1->parse($template)->willReturn($template1);
        $parser2->parse($template1)->willReturn($template2);
        $parser3->parse($template2)->willReturn($template3);

        $this->register($parser2);
        $this->register($parser1);
        $this->register($parser3);

        $this->buildFor($template)->shouldReturn($template3);
    }

}
