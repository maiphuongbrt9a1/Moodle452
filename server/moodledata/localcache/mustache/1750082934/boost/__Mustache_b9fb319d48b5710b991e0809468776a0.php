<?php

class __Mustache_b9fb319d48b5710b991e0809468776a0 extends Mustache_Template
{
    public function renderInternal(Mustache_Context $context, $indent = '')
    {
        $buffer = '';

        $buffer .= $indent . '
';
        $buffer .= $indent . '<div>
';
        $buffer .= $indent . '    <form method="get" action="';
        $value = $this->resolveValue($context->find('url'), $context);
        $buffer .= ($value === null ? '' : $value);
        $buffer .= '">
';
        $buffer .= $indent . '        <button type="submit" class="btn btn-warning form-control my-1 border-danger">
';
        $buffer .= $indent . '            <i class="fa fa-graduation-cap fa-fw mx-1"></i><span>';
        $value = $this->resolveValue($context->find('title'), $context);
        $buffer .= ($value === null ? '' : call_user_func($this->mustache->getEscape(), $value));
        $buffer .= '</span>
';
        $buffer .= $indent . '        </button>
';
        $buffer .= $indent . '    </form>
';
        $buffer .= $indent . '</div>
';

        return $buffer;
    }
}
