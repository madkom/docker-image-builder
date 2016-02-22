<?php

namespace spec\Madkom\Docker\Creator\Builder\Parser;

use Madkom\Docker\Creator\Builder\BuildException;
use Madkom\Docker\Creator\Builder\DockerTemplate;
use Madkom\Docker\Creator\Builder\Parser;
use Madkom\Docker\Creator\Builder\Parser\DockerAddParser;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\visitor\vfsStreamPrintVisitor;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class DockerIncludeParserSpec
 * @package spec\Madkom\Docker\Creator\Builder\Parser
 * @author  Dariusz Gafka <d.gafka@madkom.pl>
 * @mixin DockerAddParser
 */
class DockerAddParserSpec extends ObjectBehavior
{

    function let()
    {
        $this->beConstructedWith(vfsStream::url('system/home/resources'), vfsStream::url('system/home/prepared'));
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Parser::class);
    }

    function it_should_return_priority()
    {
        $this->priority()->shouldReturn(3);
    }

    function it_should_parse_for_include_tags()
    {
        $dockerTemplate = new DockerTemplate('super-image', file_get_contents(__DIR__ . '/../../../../stubs/DockerfileWithAdd'));
        $root = $this->setUpFolderStructure();

        $template = $this->parse($dockerTemplate);
        $template->shouldNotBe($this);
        $template->content()->shouldReturn(file_get_contents(__DIR__ . '/../../../../stubs/DockerfileWithAddReplaced'));

        $nginxConf = $root->getChild('home/prepared/super-image/nginx.conf');
        \PHPUnit_Framework_Assert::assertEquals('Some nginx configuration', $nginxConf->getContent());
    }

    function it_should_parse_mulitple_include_tags()
    {
        $dockerTemplate = new DockerTemplate('super-image', file_get_contents(__DIR__ . '/../../../../stubs/DockerfileWithMultipleAdd'));
        $root = $this->setUpFolderStructure();

        $template = $this->parse($dockerTemplate);
        $template->shouldNotBe($this);
        $template->content()->shouldReturn(file_get_contents(__DIR__ . '/../../../../stubs/DockerfileWithMultipleAddReplaced'));

        $nginxConf = $root->getChild('home/prepared/super-image/nginx.conf');
        \PHPUnit_Framework_Assert::assertEquals('Some nginx configuration', $nginxConf->getContent());

        $dbConf = $root->getChild('home/prepared/super-image/postgresql.prod.conf');
        \PHPUnit_Framework_Assert::assertEquals('some postgres configuration', $dbConf->getContent());

        $rabbitIni = $root->getChild('home/prepared/super-image/rabbit.ini');
        \PHPUnit_Framework_Assert::assertEquals('some rabbit configuration', $rabbitIni->getContent());
    }

    function it_should_throw_exception_if_file_not_found()
    {
        vfsStream::setup('system', null, [
            "home" => [
                "dockerfiles" => [],
                "partials" => []
            ]
        ]);

        $dockerTemplate = new DockerTemplate('super-image', '{add:nginx.conf:/etc/nginx/conf/nginx.conf}');
        $this->shouldThrow(BuildException::class)->during('parse', [$dockerTemplate]);
    }

    function it_should_throw_exception_if_not_read_permissions_for_file()
    {
        $root = vfsStream::setup('system', null, [
            "home" => [
                "dockerfiles" => [],
                "resources" => [
                    "nginx.conf" => 'some contents'
                ]
            ]
        ]);

        $partials = $root->getChild('home/resources/nginx.conf');
        $partials->chmod(0000);

        $dockerTemplate = new DockerTemplate('super-image', '{add:nginx.conf:/etc/nginx/conf/nginx.conf}');
        $this->shouldThrow(BuildException::class)->during('parse', [$dockerTemplate]);
    }

    function it_should_throw_exception_if_partials_folder_is_not_writable()
    {
        $root = $this->setUpFolderStructure();
        $targetFolder = $root->getChild('system/home/prepared');

        $targetFolder->chmod(0000);
        $dockerTemplate = new DockerTemplate('super-image', '{add:nginx/nginx.conf:/etc/nginx/conf/nginx.conf}');
        $this->shouldThrow(BuildException::class)->during('parse', [$dockerTemplate]);
    }

    function it_should_create_target_folders_if_not_existing()
    {
        $structure = [
            "home" => [
                "dockerfiles" => [],
                "resources" => [
                    "nginx" => [
                        "nginx.conf" => 'Some nginx configuration'
                    ],
                    "db" => [
                        "postgres" => [
                            "postgresql.prod.conf" => "some postgres configuration"
                        ]
                    ],
                    "rabbit.ini" => "some rabbit configuration"
                ],
                "prepared" => []
            ]
        ];
        $root = vfsStream::setup('system', null, $structure);

        $dockerTemplate = new DockerTemplate('super-image', file_get_contents(__DIR__ . '/../../../../stubs/DockerfileWithAdd'));

        $template = $this->parse($dockerTemplate);
        $template->shouldNotBe($this);
        $template->content()->shouldReturn(file_get_contents(__DIR__ . '/../../../../stubs/DockerfileWithAddReplaced'));

        $nginxConf = $root->getChild('home/prepared/super-image/nginx.conf');
        \PHPUnit_Framework_Assert::assertEquals('Some nginx configuration', $nginxConf->getContent());
    }

    function it_should_throw_exception_if_folder_does_not_and_cant_be_created()
    {
        $structure = [
            "home" => [
                "dockerfiles" => [],
                "resources" => [
                    "nginx" => [
                        "nginx.conf" => 'Some nginx configuration'
                    ],
                    "db" => [
                        "postgres" => [
                            "postgresql.prod.conf" => "some postgres configuration"
                        ]
                    ],
                    "rabbit.ini" => "some rabbit configuration"
                ],
                "prepared" => []
            ]
        ];
        $root = vfsStream::setup('system', null, $structure);
        $targetFolder = $root->getChild('home/prepared');
        $targetFolder->chmod(0000);

        $dockerTemplate = new DockerTemplate('super-image', file_get_contents(__DIR__ . '/../../../../stubs/DockerfileWithAdd'));

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
                "resources" => [
                    "nginx" => [
                        "nginx.conf" => 'Some nginx configuration'
                    ],
                    "db" => [
                        "postgres" => [
                            "postgresql.prod.conf" => "some postgres configuration"
                        ]
                    ],
                    "rabbit.ini" => "some rabbit configuration"
                ],
                "prepared" => []
            ]
        ];

        $root = vfsStream::setup('system', null, $structure);
        return $root;
    }

}
