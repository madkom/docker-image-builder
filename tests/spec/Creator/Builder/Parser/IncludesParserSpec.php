<?php

namespace spec\Madkom\Docker\Creator\Builder\Parser;

use Madkom\Docker\Creator\Builder\BuildException;
use Madkom\Docker\Creator\Builder\DockerTemplate;
use Madkom\Docker\Creator\Builder\Parser;
use Madkom\Docker\Creator\Builder\Parser\IncludesParser;
use org\bovigo\vfs\vfsStream;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class IncludesParserSpec
 * @package spec\Madkom\Docker\Creator\Builder\Parser
 * @author  Dariusz Gafka <d.gafka@madkom.pl>
 * @mixin IncludesParser
 */
class IncludesParserSpec extends ObjectBehavior
{

    function let()
    {
        $this->beConstructedWith(vfsStream::url('system/home/partials'));
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Parser::class);
    }

    function it_should_return_priority()
    {
        $this->priority()->shouldReturn(1);
    }

    function it_should_parse_include_statement()
    {
        $dockerTemplate = new DockerTemplate('super-image', file_get_contents(__DIR__ . '/../../../../stubs/DockerfileWithInclude'));
        $this->setUpFolderStructure();

        $template = $this->parse($dockerTemplate);
        $template->shouldNotBe($this);
        $template->content()->shouldReturn(file_get_contents(__DIR__ . '/../../../../stubs/DockerfileWithIncludeReplaced'));
    }

    function it_should_parse_multiple_include_statements()
    {
        $dockerTemplate = new DockerTemplate('super-image', file_get_contents(__DIR__ . '/../../../../stubs/DockerfileWithMultipleInclude'));
        $this->setUpFolderStructure();

        $template = $this->parse($dockerTemplate);
        $template->shouldNotBe($this);
        $template->content()->shouldReturn(file_get_contents(__DIR__ . '/../../../../stubs/DockerfileWithMultipleIncludeReplaced'));
    }

    function it_should_throw_exception_if_file_not_found()
    {
        vfsStream::setup('system', null, [
            "home" => [
                "dockerfiles" => [],
                "partials" => []
            ]
        ]);

        $dockerTemplate = new DockerTemplate('super-image', '{include:php/5/composer}');

        $this->shouldThrow(BuildException::class)->during('parse', [$dockerTemplate]);
    }

    function it_should_throw_exception_if_not_read_permissions_for_file()
    {
        $root = vfsStream::setup('system', null, [
            "home" => [
                "dockerfiles" => [],
                "partials" => [
                    'composer' => 'composer contents'
                ]
            ]
        ]);

        $partials = $root->getChild('home/partials/composer');
        $partials->chmod(0000);

        $dockerTemplate = new DockerTemplate('super-image', '{include:composer}');

        $this->shouldThrow(BuildException::class)->during('parse', [$dockerTemplate]);
    }

    /**
     * @return \org\bovigo\vfs\vfsStreamDirectory
     */
    private function setUpFolderStructure()
    {
        $structure = [
            "home" => [
                "dockerfiles" => [],
                "partials" => [
                    "php-5.6" => [
                        "curl" => 'RUN apt-get update -qq \
    && apt-get install -yqq curl --no-install-recommends \
    && apt-get autoclean \
    && rm -rf /var/lib/apt/lists/*'
                    ],
                    "nginx" => 'RUN apt-get install nginx -yqq',
                    "php" => [
                        "5" => [
                            "composer" => 'RUN curl -sSL -m 30 https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && chmod +x /usr/local/bin/composer'
                        ]
                    ]
                ]
            ]
        ];

        $root = vfsStream::setup('system', null, $structure);
        return $root;
    }

}
