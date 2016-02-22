<?php

namespace Madkom\Docker\Creator\Builder\Parser;

/**
 * Class PreExpressionParser
 * @package Madkom\Docker\Creator\Builder\Parser
 * @author  Dariusz Gafka <d.gafka@madkom.pl>
 */
class PreExpressionParser extends ExpressionParser
{
    /**
     * @inheritDoc
     */
    public function priority()
    {
        return 0;
    }

}
