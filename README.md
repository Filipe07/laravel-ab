
![Build](https://github.com/filipe07/laravel-ab/actions/workflows/build.yml/badge.svg)
![Release](https://img.shields.io/badge/Release-1.0.0-green)


<hr>
<h6 align="center">
    <img src="https://i.ibb.co/hy7fjMG/Laravel-AB.png" width="300"/>
</h6>

This package helps you to find out which content works on your site and which doesn't.

It allows you to create experiments and goals. The visitor will receive randomly the next experiment and you can customize your site to that experiment. The view and the goal conversion will be tracked and you can view the results in a report.

This package is very inspired and a mix of two other packages:
- [ben182/laravel-ab](https://github.com/ben182/laravel-ab)
- [mfjordvald/laravel-ab](https://github.com/mfjordvald/laravel-ab)

## Installation

This package can be used in Laravel 8 or higher.

You can install the package via composer:

```bash
composer require filipe07/laravel-ab
```

## Config

After installation publish the config file:

```bash
php artisan vendor:publish --provider="AbTesting\AbTestingServiceProvider"
```

You can define your experiments and goals in there.

Finally, run the newly added migration

```bash
php artisan migrate
```

Two new migrations should be added.

## Usage

### Variants

```html
@if (AbTesting::isVariant('logo-big'))

    <div class="logo-big"></div>

@elseif (AbTesting::isVariant('logo-grayscale'))

    <div class="logo-greyscale"></div>

@elseif (AbTesting::isVariant('brand-name'))

    <h1>Brand name</h1>

@endif
```

That's the most basic usage of the package. You don't have to initialize anything. The package handles everything for you if you call `isVariant`

Alternatively you can use a custom blade if statement:

```html
@ab('logo-big')

    <div class="logo-big"></div>

@elseab('logo-grayscale')

    <div class="logo-greyscale"></div>

@elseab('brand-name')

    <h1>Brand name</h1>

@endab
```

This will work exactly the same way.

If you don't want to make any continual rendering you can call

```php
AbTesting::pageView()
```

directly and trigger a new page view with a random variant. This function will also be called from `isVarian`.

Under the hood a new session item will keep track of the current variant. A session will only get one variant and only trigger one page view.

You can grab the current variant with:

```php
// get the underlying model
AbTesting::getVariant()

// get the variant name
AbTesting::getVariant()->name

// get the visitor count
AbTesting::getVariant()->visitors
```

Alternatively there is a request helper for you:

```php
public function index(Request $request) {
    // the same as 'AbTesting::getVariant()'
    $request->abVariant()
}
```

### Goals

To complete a goal simply call:

```php
AbTesting::completeGoal('signup')
```

The function will increment the conversion of the goal assigned to the active variant. If there isn't an active variant running for the session one will be created. You can only trigger a goal conversion once per session. This will be prevented with another session item. The function returns the underlying goal model.

To get all completed goals for the current session:

```php
AbTesting::getCompletedGoals()
```

### Persisting visitor data
When used in the above way the visitor data is stored in session data attached to the user. In some cases you might want to trigger a goal completion in an asynchronous way and the users session won't be available. To avoid getting bad data by registering the goal completion under a new variant you can persist the visitor data in the database.

To do this you pass a user identifier to the pageview function.

```php
AbTesting::pageview($identifier)
```

In your asynchronous request you can then provide the same idenfier to the completeGoals function.

```php
AbTesting::completeGoal('payment-completed', $identifier)
```

### Report

To get a report of the page views, completed goals and conversion call the report command:

```bash
php artisan ab:report
```

This prints something like this:

```
+---------------+----------+-------------+
| Variant       | Visitors | Goal signup |
+---------------+----------+-------------+
| big-logo      | 2        | 1 (50%)     |
| small-buttons | 1        | 0 (0%)      |
+---------------+----------+-------------+
```

### Reset

To reset all your visitors and goal completions call the reset command:

```bash
php artisan ab:reset
```

### Events

In addition you can hook into two events:

- `VariantNewVisitor` gets triggered once an variant gets assigned to a new visitor. You can grab the variant and visitor instance as properties of the event.
- `GoalCompleted` gets triggered once a goal is completed. You can grab the goal as a property of the event.

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email moin@benjaminbortels.de instead of using the issue tracker.

## Credits

- [Benjamin Bortels](https://github.com/ben182)
- [Martin Fjordvald](https://github.com/mfjordvald)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
