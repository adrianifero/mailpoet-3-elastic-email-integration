<?php

namespace MailPoetVendor\Sabberworm\CSS\RuleSet;

use MailPoetVendor\Sabberworm\CSS\Property\AtRule;
/**
 * A RuleSet constructed by an unknown @-rule. @font-face rules are rendered into AtRuleSet objects.
 */
class AtRuleSet extends \MailPoetVendor\Sabberworm\CSS\RuleSet\RuleSet implements \MailPoetVendor\Sabberworm\CSS\Property\AtRule
{
    private $sType;
    private $sArgs;
    public function __construct($sType, $sArgs = '', $iLineNo = 0)
    {
        parent::__construct($iLineNo);
        $this->sType = $sType;
        $this->sArgs = $sArgs;
    }
    public function atRuleName()
    {
        return $this->sType;
    }
    public function atRuleArgs()
    {
        return $this->sArgs;
    }
    public function __toString()
    {
        return $this->render(new \MailPoetVendor\Sabberworm\CSS\OutputFormat());
    }
    public function render(\MailPoetVendor\Sabberworm\CSS\OutputFormat $oOutputFormat)
    {
        $sArgs = $this->sArgs;
        if ($sArgs) {
            $sArgs = ' ' . $sArgs;
        }
        $sResult = "@{$this->sType}{$sArgs}{$oOutputFormat->spaceBeforeOpeningBrace()}{";
        $sResult .= parent::render($oOutputFormat);
        $sResult .= '}';
        return $sResult;
    }
}
