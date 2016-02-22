<?php

namespace spec\Madkom\Docker\Creator;

use Madkom\Docker\Creator\Assistant;
use Madkom\Docker\Creator\Builder\BuildException;
use Madkom\Docker\Creator\Builder\Parser;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\visitor\vfsStreamPrintVisitor;
use org\bovigo\vfs\visitor\vfsStreamStructureVisitor;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class AssistantSpec
 * @package spec\Madkom\Docker\Creator
 * @author  Dariusz Gafka <d.gafka@madkom.pl>
 * @mixin Assistant
 */
class AssistantSpec extends ObjectBehavior
{

    /** @var vfsStreamDirectory  */
    private $root;

    function let()
    {
        $this->root = $this->setUpStructure();
        $this->beConstructedWith(vfsStream::url('system/docker/finished'), vfsStream::url('system/docker/resources'), vfsStream::url('system/partials'), []);
        file_put_contents(vfsStream::url('system/docker/dockertemplates/DockerfileTemplate'), file_get_contents(__DIR__ . '/../../stubs/DockerfileWithAdd'));
    }

    function it_should_register_parser(Parser $parser)
    {
        $this->registerExternalParser($parser);
    }

    function it_should_create_prepared_image()
    {
        $this->createImage(vfsStream::url('system/docker/build/buildscript.sh'),'test', vfsStream::url('system/docker/dockertemplates/DockerfileTemplate'), 'registry.com/php:7.1');

        $buildScript = $this->root->getChild('docker/build/buildscript.sh');
        \PHPUnit_Framework_Assert::assertEquals("#!/usr/bin/env bash
docker build --pull=true --tag=registry.com/php:7.1 vfs://system/docker/finished/test
docker push registry.com/php:7.1", $buildScript->getContent());

        $dockerfile = $this->root->getChild('docker/finished/test/Dockerfile');
        \PHPUnit_Framework_Assert::assertEquals(file_get_contents(__DIR__ . '/../../stubs/DockerfileWithAddReplaced'), $dockerfile->getContent());
    }

    function it_should_throw_build_exception_if_not_script_found()
    {
        $this->shouldThrow(BuildException::class)->during('createImage', [vfsStream::url('system/docker/build/buildscript.sh'),'test', vfsStream::url('system/docker/dockertemplates/DockerfileTemplateNotExisting'), 'registry.com/php:7.1']);
    }

    private function setUpStructure()
    {
        return vfsStream::setup('system', 0775, [
             'docker' => [
                 'build'        => [],
                 'finished'     => [],
                 'resources'    => [
                     'nginx' => [
                         'nginx.conf' => 'Some nginx configuration'
                     ]
                 ],
                 'partials'     => [],
                 'dockertemplates' => []
             ]
        ]);
    }

}
