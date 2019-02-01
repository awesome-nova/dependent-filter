<?php
declare(strict_types=1);

namespace DKulyk\Nova;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Nova\Filters\Filter;

class DependentFilter extends Filter
{
    /**
     * @var callable
     */
    public $optionsCallback;

    /**
     * @var string[]
     */
    public $dependentOf = [];

    /**
     * Default value.
     *
     * @var mixed
     */
    public $default = '';

    /**
     * @var string
     */
    public $attribute;

    /**
     * @var bool
     */
    public $hideWhenEmpty = false;

    /**
     * @var string
     */
    public $component = 'dkulyk-dependent-filter';

    /**
     * RelatedFilter constructor.
     * @param null $name
     * @param null $attribute
     */
    public function __construct($name = null, $attribute = null)
    {
        $this->name = $name ?? $this->name;
        $this->attribute = $attribute ?? $this->attribute ?? str_replace(' ', '_', Str::lower($this->name()));
    }

    /**
     * Apply the filter to the given query.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  mixed $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Request $request, $query, $value)
    {
        return $query->whereIn($this->attribute, (array)$value);
    }

    /**
     * Get the key for the filter.
     *
     * @return string
     */
    public function key()
    {
        return $this->attribute;
    }

    /**
     * Get the filter's available options.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  array $filters
     * @return array|\Illuminate\Support\Collection
     */
    public function options(Request $request, array $filters = [])
    {
        return call_user_func($this->optionsCallback, $request, $filters);
    }

    /**
     * @param  string|string[] $filter
     * @return $this
     */
    final public function dependentOf($filter)
    {
        if (! is_array($filter)) {
            $filter = func_get_args();
        }

        $this->dependentOf = $filter;

        return $this;
    }

    /**
     * @param  \Illuminate\Http\Request $request
     * @param  array $filters
     * @return array
     */
    final public function getOptions(Request $request, array $filters = [])
    {
        return collect(
            $this->options($request, $filters + array_fill_keys($this->dependentOf, ''))
        )->map(function ($value, $key) {
            return is_array($value) ? ($value + ['value' => $key]) : ['label' => $value, 'value' => $key];
        })->values()->all();
    }

    /**
     * @param  callable|array $callback
     *
     * @return $this
     */
    final public function withOptions($callback, $dependentOf = null)
    {
        if (! is_callable($callback)) {
            $callback = function () use ($callback) {
                return $callback;
            };
        }

        $this->optionsCallback = $callback;

        if (! is_null($dependentOf)) {
            $this->dependentOf($dependentOf);
        }

        return $this;
    }

    /**
     * @param  bool $value
     * @return $this
     */
    public function hideWhenEmpty($value = true)
    {
        $this->hideWhenEmpty = $value;

        return $this;
    }

    /**
     * Prepare the filter for JSON serialization.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return array_merge([
            'class' => $this->key(),
            'name' => $this->name(),
            'component' => $this->component(),
            'options' => count($this->dependentOf) === 0 ? $this->getOptions(app(Request::class)) : [],
            'currentValue' => $this->default() ?? '',
            'dependentOf' => $this->dependentOf,
            'hideWhenEmpty' => $this->hideWhenEmpty,
        ], $this->meta());
    }

    /**
     * @param  mixed[] ...$args
     * @return static
     */
    public static function make(...$args)
    {
        return new static(...$args);
    }
}