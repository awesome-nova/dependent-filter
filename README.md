## Nova Dependent Filter
[![Latest Version on Github](https://img.shields.io/packagist/v/awesome-nova/dependent-filter.svg?style=flat)](https://packagist.org/packages/awesome-nova/dependent-filter)
[![Total Downloads](https://img.shields.io/packagist/dt/awesome-nova/dependent-filter.svg?style=flat)](https://packagist.org/packages/awesome-nova/dependent-filter)
[![Become a Patron!](https://img.shields.io/badge/become-a_patron!-red.svg?logo=patreon&style=flat)](https://www.patreon.com/bePatron?u=16285116)

1. [Installation](#user-content-installation)
2. [Usage](#user-content-usage)
    1. [Declaration](#user-content-declaration)
    2. [Class declaration](#user-content-class-declaration)
    3. [Static dependencies](#user-content-static-dependencies)
    4. [Dynamic dependencies](#user-content-dynamic-dependencies)
    5. [Hiding empty filters](#user-content-hiding-empty-filters)
    6. [Default filter value](#user-content-default-filter-value)
    7. [Other stuffs](#user-content-other-stuffs)
3. [Thanks](#user-content-thanks)

## Installation

You can install the package in to a Laravel app that uses [Nova](https://nova.laravel.com) via composer:

```sh
composer require awesome-nova/dependent-filter
```

## Usage

### Declaration

You can declare filters in you filters method directly:

```php
function filters(Request $request)
{
    return [
        (new DependentFilter('State'))
            ->withOptions([
                'all' => 'All orders',
                'dragt' => 'Draft',
                'outstanding' => 'Outstanding',
                'past_due' => 'Past due',
                'paid' => 'Paid',
            ]),
    ];
}
```

Also you can use `DependentFilter::make()` instead `new DependentFilter()`.

For queries you need to use callback declaration:

```php
function filters(Request $request)
{
    return [
        DependentFilter::make('Category', 'category_id'))
            ->withOptions(function (Request $request) {
                return Category::pluck('title', 'id');
            }),
    ];
}
```

> Note: In difference with Nova filters filter's `value` need pass as array key and `label` as array value.
 
### Class declaration

As is Nova filters you can create filter's class:

```php
class CategoryFilter extends DependentFilter
{
    /**
     * Name of filter.
     *
     * @var string
     */
    public $name = 'Category';
    
    /**
     * Attribute name of filter. Also it is key of filter.
     *
     * @var string
     */
    public $attribute = 'ctaegory_uid';
    
    public function options(Request $request, array $filters = [])
    {
        return Category::pluck('title', 'id');
    } 
}
```

> Note: The `fresh` method is identical with the callback for declaring options.

```php
function filters(Request $request)
{
    return [
        CategoryFilter::make(),
    ];
}
```

### Static dependencies

For creating dependent filter you need to specify dependent filters values at which the option will be shown:

```php
function filters(Request $request)
{
    return [
        CategoryFilter::make(),
        
        SubCategory::make('Subcategory', 'subcategory_id')
            ->withOptions(function (Request $request) {
                return SubCategory::all()->map(function ($subcategory) {
                    return [
                        'value' => $subcategory->id,
                        'label' => $subcategory->title.
                        'depends' => [
                            'category_id' => $subcategory->category_id, //Also you can set array of values
                        ],
                    ];
                });
            }),
    ];
}
```

> Note. Instead of an attribute or class name, you must specify the key of the filter.

### Dynamic dependencies

For big collection of data you can use dynamic updating of the filter.

```php
function filters(Request $request) 
{
    return [
        StateFilter::make('State', 'state_id'),
        
        DependentFilter::make('City', 'city_id')
            ->dpendentOf('state_id')
            ->withOptions(function (Request $request, $filters) {
                return City::where('state_id', $filters['state_id'])
                    ->pluck('title', 'id');
            }),
    ];
}
```

In class declaration you also need to set `$dpendentOf` property: 

```php
class CityFilter extends DependentFilter
{
    public $dpendentOf = ['state_id'];
    
    function options(Request $request, $filters = [])
    {
        return City::where('state_id', $filters['state_id'])
            ->pluck('title', 'id');
    }
}
```

If you want to show options only when main filter is selected you can use `when` for check it:

```php
function options(Request $request, $filters = []) {
    return City::when($filters['state_id'], function ($query, $value) {
        $query->where('state_id', $value)
    })->pluck('title', 'id');
}
```

### Hiding empty filters

You can hide filters until they have options. 

For it you need set `$hideWhenEmpty` or call `hideWhenEmpty()` method:

```php
class CityFilter extends DependentFilter
{
    public $hideWhenEmpty = true;
    
}
```

```php
function filters(Request $request) {
    return [
        StateFilter::make('State', 'state_id'),
        
        CityFilter::make('City', 'city_id')->hideWhenEmpty(),
    ];
}

```

### Default filter value

If you want to set default value you need to call `withDefault` method with value or overload `default` method in class declaration. 

```php
function filters(Request $request) {
    return [
        StateFilter::make('State', 'code')->withDefault('WA'),
    ];
}

```

```php
class StateFilter extends DependentFilter
{
    public function default()
    {
        return 'WA';
    }
}

```

### Other stuffs

By default filter checking by equal filed specified in `$attribute` and filter value. You can overload it like as in Nova filters:

```php
class MyFilter extends DependentFilter
{
    public function apply(Request $request, $query, $value)
    {
        return $query->where('column', '>=', $value);
    }
}
```

When you use declare style you can set pass apply callback to `withApply` method:
```php
function filters(Request $request) {
    return [
        StateFilter::make('State', 'code')->withApply(function ($request, $query, $value) {
            return $query->where('code', '=', $value);
        }),
    ];
}

```
 

Also you can specify another filter key over method `key`.


## Thanks

Thanks to [Brian](https://github.com/dillingham) for his support and advices. 