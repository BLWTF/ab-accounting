<?php

namespace Lorisleiva\LaravelSearchString\Options;

use Illuminate\Support\Arr;
use Lorisleiva\LaravelSearchString\SearchStringManager;

abstract class Rule
{
    /** @var string */
    public $column;

    /** @var string */
    public $key;

    public function __construct(string $column, $rule = null)
    {
        if (is_null($rule)) {
            $rule = [];
        }

        if (is_string($rule)) {
            $rule = [ 'key' => $rule ];
        }

        $this->column = $column;
        $this->key = $this->getPattern($rule, 'key', $column);
    }

    public function match($key)
    {
        return preg_match($this->key, $key);
    }

    public function qualifyColumn($builder)
    {
        return SearchStringManager::qualifyColumn($builder, $this->column);
    }

    protected function getPattern($rawRule, $key, $default = null)
    {
        $default = $default ?? $this->$key;
        $pattern = Arr::get($rawRule, $key, $default);
        $pattern = is_null($pattern) ? $default : $pattern;

        return $this->regexify($pattern);
    }

    protected function regexify($pattern)
    {
        try {
            preg_match($pattern, null);
            return $pattern;
        } catch (\Throwable $exception) {
            return '/^' . preg_quote($pattern, '/') . '$/';
        }
    }

    public function __toString()
    {
        return "[$this->key]";
    }
}
