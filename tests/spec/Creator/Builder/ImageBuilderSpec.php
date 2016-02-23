<?php

namespace spec\Madkom\Docker\Creator\Builder;

use Madkom\Docker\Creator\Builder\ImageBuilder;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class ImageCreatorSpec
 * @package spec\Madkom\Docker
 * @author  Dariusz Gafka <d.gafka@madkom.pl>
 * @mixin ImageBuilder
 */
class ImageBuilderSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Madkom\Docker\Creator\Builder\ImageBuilder');
    }

    function it_should_build_image()
    {
        $tag = 'nginx:1.9.1';
        $targetDirectory = '/system/home';

        $this->buildImage($tag, $targetDirectory)->shouldReturn("docker build --tag=nginx:1.9.1 /system/home");
    }

    function it_should_build_image_with_pull()
    {
        $tag = 'nginx:1.9.1';
        $targetDirectory = '/system/home';

        $this->buildImage($tag, $targetDirectory, true)->shouldReturn("docker build --pull=true --tag=nginx:1.9.1 /system/home");
    }

    function it_should_push_image()
    {
        $imageName = 'nginx:1.9.1';

        $this->pushImage($imageName)->shouldReturn('docker push nginx:1.9.1');
    }

}
