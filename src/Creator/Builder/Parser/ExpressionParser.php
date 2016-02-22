<?php

namespace Madkom\Docker\Creator\Builder\Parser;

use Madkom\Docker\Creator\Builder\BuildException;
use Madkom\Docker\Creator\Builder\DockerTemplate;
use Madkom\Docker\Creator\Builder\Parser;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * Class DockerExpressionParser
 * @package Madkom\Docker\Creator\Builder\Parser
 * @author  Dariusz Gafka <d.gafka@madkom.pl>
 */
class ExpressionParser implements Parser
{
    /**
     * @var ExpressionLanguage
     */
    private $expressionLanguage;
    /**
     * @var array
     */
    private $data;

    /**
     * DockerExpressionParser constructor.
     *
     * @param ExpressionLanguage $expressionLanguage
     * @param array              $data
     */
    public function __construct(ExpressionLanguage $expressionLanguage, array $data)
    {
        $this->expressionLanguage = $expressionLanguage;
        $this->data               = $data;
    }

    /**
     * @inheritDoc
     */
    public function priority()
    {
        return 2;
    }

    /**
     * @inheritDoc
     */
    public function parse(DockerTemplate $dockerTemplate)
    {
        $dockerTemplate = $this->parseIfStatements($dockerTemplate);
        $dockerTemplate = $this->parseExpressions($dockerTemplate);

        return $dockerTemplate;
    }

    /**
     * Parses if statements
     *
     * @param DockerTemplate $dockerTemplate
     *
     * @return DockerTemplate|static
     */
    private function parseIfStatements(DockerTemplate $dockerTemplate)
    {
        preg_match_all("#\[\*(if([^\*]*))\*\]((.|\s)*)\[\*\/if\*\]#i", $dockerTemplate->content(), $matches);

        for ($i = 0; $i < count($matches[0]); $i++) {
            $evaluatedExpression = $this->evaluate($matches[2][$i]);

            $dockerTemplate = $dockerTemplate->replaceContent($matches[0][$i], $evaluatedExpression ? $matches[3][$i] : '');
        }

        return $dockerTemplate;
    }

    /**
     * Parses expressions
     *
     * @param DockerTemplate $dockerTemplate
     *
     * @return DockerTemplate|static
     */
    private function parseExpressions(DockerTemplate $dockerTemplate)
    {
        preg_match_all("#\[\*([^if|^\/if][^\*]*)\*\]#i", $dockerTemplate->content(), $matches);

        for ($i = 0; $i < count($matches[0]); $i++) {
            $evaluatedExpression = $this->evaluate($matches[1][$i]);
            $dockerTemplate = $dockerTemplate->replaceContent($matches[0][$i], $evaluatedExpression);
        }

        return $dockerTemplate;
    }

    /**
     * Evaluates expression
     *
     * @param $expression
     *
     * @return string
     * @throws BuildException
     */
    private function evaluate($expression)
    {
        try {
            $evaluatedExpression = $this->expressionLanguage->evaluate($expression, $this->data);
        }catch(\Exception $e) {
            throw new BuildException($e->getMessage());
        }

        return $evaluatedExpression;
    }

}
