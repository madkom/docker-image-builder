<?php

namespace spec\Madkom\Docker\Creator\Builder;

use Madkom\Docker\Creator\Builder\BuildException;
use Madkom\Docker\Creator\Builder\DockerTemplate;
use org\bovigo\vfs\vfsStream;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class DockerTemplateSpec
 * @package spec\Madkom\Docker\Creator\Builder
 * @author  Dariusz Gafka <d.gafka@madkom.pl>
 * @mixin DockerTemplate
 */
class DockerTemplateSpec extends ObjectBehavior
{

    function let()
    {
        $this->beConstructedWith('nginx', 'FROM registry.madkom.pl/php:5.6-cli');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Madkom\Docker\Creator\Builder\DockerTemplate');
    }

    function it_should_return_values_it_was_created_with()
    {
        $this->content()->shouldReturn('FROM registry.madkom.pl/php:5.6-cli');
    }

    function it_should_return_name()
    {
        $this->name()->shouldReturn('nginx');
    }

    function it_should_change_content()
    {
        $template = $this->addNewLine("RUN curl -sSL -m 30 https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && chmod +x /usr/local/bin/composer");
        $this->shouldNotBe($template);
        $template->content()->shouldReturn(
          "FROM registry.madkom.pl/php:5.6-cli\nRUN curl -sSL -m 30 https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && chmod +x /usr/local/bin/composer"
        );
    }

    function it_should_replace_line()
    {
        $this->beConstructedWith('nginx', "FROM registry.madkom.pl/php:5.6-cli\nRUN curl -sSL -m 30 https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && chmod +x /usr/local/bin/composer\nRUN apt-get update -qq \\
    && apt-get install -yqq curl --no-install-recommends \\
    && apt-get autoclean \\
    && rm -rf /var/lib/apt/lists/*");

        $template1 = $this->replaceContent('FROM registry.madkom.pl/php:5.6-cli', 'FROM registry.madkom.pl/php:7-cli');
        $template1->shouldNotBe($this);
        $template1->content()->shouldReturn("FROM registry.madkom.pl/php:7-cli\nRUN curl -sSL -m 30 https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && chmod +x /usr/local/bin/composer\nRUN apt-get update -qq \\
    && apt-get install -yqq curl --no-install-recommends \\
    && apt-get autoclean \\
    && rm -rf /var/lib/apt/lists/*");

        $template2 = $this->replaceContent("RUN apt-get update -qq \\
    && apt-get install -yqq curl --no-install-recommends \\
    && apt-get autoclean \\
    && rm -rf /var/lib/apt/lists/*", 'RUN echo test');
        $template2->shouldNotBe($this);
        $template2->content()->shouldReturn("FROM registry.madkom.pl/php:5.6-cli\nRUN curl -sSL -m 30 https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && chmod +x /usr/local/bin/composer\nRUN echo test");

        $template3 = $this->replaceContent('php:5.6-cli', 'php:7-cli');
        $template3->shouldNotBe($this);
        $template3->content()->shouldReturn("FROM registry.madkom.pl/php:7-cli\nRUN curl -sSL -m 30 https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && chmod +x /usr/local/bin/composer\nRUN apt-get update -qq \\
    && apt-get install -yqq curl --no-install-recommends \\
    && apt-get autoclean \\
    && rm -rf /var/lib/apt/lists/*");
    }

    function it_should_be_copied_to_file()
    {
        $root = vfsStream::setup('home');

        $this->toFile(vfsStream::url('home'));

        $dockerfile = $root->getChild('Dockerfile');
        \PHPUnit_Framework_Assert::assertEquals('FROM registry.madkom.pl/php:5.6-cli', $dockerfile->getContent());
    }

    function it_should_throw_exception_if_target_folder_is_not_writable()
    {
        $root = vfsStream::setup('home', 0775, [
            'files' => []
        ]);
        $child = $root->getChild('files');
        $child->chmod(0000);

        $this->shouldThrow(BuildException::class)->during('toFile', [vfsStream::url('home/files')]);
    }

    function it_should_create_catalog_if_not_exists()
    {
        $root = vfsStream::setup('home', 0775, []);

        $this->toFile(vfsStream::url('home/files'));
        $dockerfile = $root->getChild('files/Dockerfile');
        \PHPUnit_Framework_Assert::assertEquals('FROM registry.madkom.pl/php:5.6-cli', $dockerfile->getContent());
    }
}
