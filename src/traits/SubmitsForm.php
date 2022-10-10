<?php

namespace markhuot\craftpest\traits;

trait SubmitsForm
{
    /** @var array */
    protected $formData = [];

    /**
     * Fills any form data with a matching key with the given value.
     */
    function fill(string $key, string|array $value): self
    {
        $this->formData[$key] = $value;
        
        return $this;
    }
    
    /**
     * Submits a form matching the given selector
     */
    function submit(string $selector='form')
    {
        $form = $this->querySelector($selector)->form();
        
        $url = $form->getUri();
        $method = $form->getMethod();
        $values = array_replace_recursive($form->getPhpValues(), $this->formData);
        $files = $form->getFiles();
        
        $request = test()->http($method, $url)
            ->addHeader('X-Http-Method-Override', $method)
            ->setBody($values);

        return $request->send();
    }

}