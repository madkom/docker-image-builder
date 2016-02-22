<?php

namespace spec\Madkom\Docker\Creator\Builder;

use Madkom\Docker\Creator\Builder\BuildException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class BuildExceptionSpec
 * @package spec\Madkom\Docker\Creator\Builder
 * @author  Dariusz Gafka <d.gafka@madkom.pl>
 * @mixin BuildException
 */
class BuildExceptionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(\Exception::class);
    }
}
