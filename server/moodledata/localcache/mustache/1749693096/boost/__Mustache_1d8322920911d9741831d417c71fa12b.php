<?php

class __Mustache_1d8322920911d9741831d417c71fa12b extends Mustache_Template
{
    private $lambdaHelper;

    public function renderInternal(Mustache_Context $context, $indent = '')
    {
        $this->lambdaHelper = new Mustache_LambdaHelper($this->mustache, $context);
        $buffer = '';

        $buffer .= $indent . '
';
        $buffer .= $indent . '<div class="alert alert-warning alert-block d-flex flex-column" role="alert">
';
        $buffer .= $indent . '    <div>
';
        $buffer .= $indent . '        <strong>';
        $value = $context->find('str');
        $buffer .= $this->sectionC07061508cfa72d06749f23c7f5be4b5($context, $indent, $value);
        $buffer .= ':</strong>
';
        $buffer .= $indent . '        ';
        $value = $context->find('str');
        $buffer .= $this->sectionEbff675e3e797f88d1e9779bf68a5733($context, $indent, $value);
        $buffer .= '
';
        $buffer .= $indent . '    </div>
';
        $buffer .= $indent . '    <div>
';
        $buffer .= $indent . '        <strong>';
        $value = $context->find('str');
        $buffer .= $this->section159c551db70aa873d3c2d07b6d9338fb($context, $indent, $value);
        $buffer .= ':</strong>
';
        $buffer .= $indent . '    </div>
';
        $buffer .= $indent . '    <div>
';
        $buffer .= $indent . '        <code>$CFG->local_adminer_secret = \'';
        $value = $context->find('str');
        $buffer .= $this->section4b271daad5aa6f3c157da5ee77c5f72b($context, $indent, $value);
        $buffer .= '\';</code>
';
        $buffer .= $indent . '    </div>
';
        $buffer .= $indent . '    <div>
';
        $buffer .= $indent . '        ';
        $value = $context->find('str');
        $buffer .= $this->sectionFcfde98197a5f953609fd16a6387e89f($context, $indent, $value);
        $buffer .= '
';
        $buffer .= $indent . '    </div>
';
        $buffer .= $indent . '    <div>
';
        $buffer .= $indent . '        <strong>';
        $value = $context->find('str');
        $buffer .= $this->section159c551db70aa873d3c2d07b6d9338fb($context, $indent, $value);
        $buffer .= ':</strong>
';
        $buffer .= $indent . '    </div>
';
        $buffer .= $indent . '    <div>
';
        $buffer .= $indent . '        <code>$CFG->local_adminer_secret = \'';
        $value = $this->resolveValue($context->find('disabledsecret'), $context);
        $buffer .= ($value === null ? '' : call_user_func($this->mustache->getEscape(), $value));
        $buffer .= '\';</code>
';
        $buffer .= $indent . '    </div>
';
        $buffer .= $indent . '</div>
';

        return $buffer;
    }

    private function sectionC07061508cfa72d06749f23c7f5be4b5(Mustache_Context $context, $indent, $value)
    {
        $buffer = '';
    
        if (!is_string($value) && is_callable($value)) {
            $source = ' securitynote, local_adminer ';
            $result = (string) call_user_func($value, $source, $this->lambdaHelper);
            $buffer .= $result;
        } elseif (!empty($value)) {
            $values = $this->isIterable($value) ? $value : array($value);
            foreach ($values as $value) {
                $context->push($value);
                
                $buffer .= ' securitynote, local_adminer ';
                $context->pop();
            }
        }
    
        return $buffer;
    }

    private function sectionEbff675e3e797f88d1e9779bf68a5733(Mustache_Context $context, $indent, $value)
    {
        $buffer = '';
    
        if (!is_string($value) && is_callable($value)) {
            $source = ' securitynote_text, local_adminer ';
            $result = (string) call_user_func($value, $source, $this->lambdaHelper);
            $buffer .= $result;
        } elseif (!empty($value)) {
            $values = $this->isIterable($value) ? $value : array($value);
            foreach ($values as $value) {
                $context->push($value);
                
                $buffer .= ' securitynote_text, local_adminer ';
                $context->pop();
            }
        }
    
        return $buffer;
    }

    private function section159c551db70aa873d3c2d07b6d9338fb(Mustache_Context $context, $indent, $value)
    {
        $buffer = '';
    
        if (!is_string($value) && is_callable($value)) {
            $source = ' example, local_adminer ';
            $result = (string) call_user_func($value, $source, $this->lambdaHelper);
            $buffer .= $result;
        } elseif (!empty($value)) {
            $values = $this->isIterable($value) ? $value : array($value);
            foreach ($values as $value) {
                $context->push($value);
                
                $buffer .= ' example, local_adminer ';
                $context->pop();
            }
        }
    
        return $buffer;
    }

    private function section4b271daad5aa6f3c157da5ee77c5f72b(Mustache_Context $context, $indent, $value)
    {
        $buffer = '';
    
        if (!is_string($value) && is_callable($value)) {
            $source = ' example_key, local_adminer ';
            $result = (string) call_user_func($value, $source, $this->lambdaHelper);
            $buffer .= $result;
        } elseif (!empty($value)) {
            $values = $this->isIterable($value) ? $value : array($value);
            foreach ($values as $value) {
                $context->push($value);
                
                $buffer .= ' example_key, local_adminer ';
                $context->pop();
            }
        }
    
        return $buffer;
    }

    private function sectionFcfde98197a5f953609fd16a6387e89f(Mustache_Context $context, $indent, $value)
    {
        $buffer = '';
    
        if (!is_string($value) && is_callable($value)) {
            $source = ' blockinginfo, local_adminer , {{disabledsecret}}';
            $result = (string) call_user_func($value, $source, $this->lambdaHelper);
            $buffer .= $result;
        } elseif (!empty($value)) {
            $values = $this->isIterable($value) ? $value : array($value);
            foreach ($values as $value) {
                $context->push($value);
                
                $buffer .= ' blockinginfo, local_adminer , ';
                $value = $this->resolveValue($context->find('disabledsecret'), $context);
                $buffer .= ($value === null ? '' : call_user_func($this->mustache->getEscape(), $value));
                $context->pop();
            }
        }
    
        return $buffer;
    }

}
