<?php

class __Mustache_b9b15631a7c848a8d5fde845cd7650b5 extends Mustache_Template
{
    private $lambdaHelper;

    public function renderInternal(Mustache_Context $context, $indent = '')
    {
        $this->lambdaHelper = new Mustache_LambdaHelper($this->mustache, $context);
        $buffer = '';

        $buffer .= $indent . '
';
        $buffer .= $indent . '<div class="local-adminer-nav-action">
';
        $buffer .= $indent . '    <a id="local-adminer-action-';
        $value = $this->resolveValue($context->find('uniqid'), $context);
        $buffer .= ($value === null ? '' : call_user_func($this->mustache->getEscape(), $value));
        $buffer .= '" href="';
        $value = $this->resolveValue($context->find('url'), $context);
        $buffer .= ($value === null ? '' : $value);
        $buffer .= '" class="nav-link icon-no-margin" target="_blank">
';
        $buffer .= $indent . '        <i class="icon fa fa-database fa-fw navicon" title="';
        $value = $context->find('str');
        $buffer .= $this->section39ca222f95db4b461b946930948df93c($context, $indent, $value);
        $buffer .= '"></i>
';
        $buffer .= $indent . '    </a>
';
        $buffer .= $indent . '</div>
';

        return $buffer;
    }

    private function section39ca222f95db4b461b946930948df93c(Mustache_Context $context, $indent, $value)
    {
        $buffer = '';
    
        if (!is_string($value) && is_callable($value)) {
            $source = ' pluginname, local_adminer ';
            $result = (string) call_user_func($value, $source, $this->lambdaHelper);
            $buffer .= $result;
        } elseif (!empty($value)) {
            $values = $this->isIterable($value) ? $value : array($value);
            foreach ($values as $value) {
                $context->push($value);
                
                $buffer .= ' pluginname, local_adminer ';
                $context->pop();
            }
        }
    
        return $buffer;
    }

}
