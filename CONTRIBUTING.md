# Contributing

Any open source product is only as good as the community behind it. You can participate by sharing code, ideas, or
simply helping others. No matter what your skill level is, every contribution counts.

## Coding Conventions

To keep the codebase consistent, we have established the following conventions, which should be used when contributing
to the project.

### EditorConfig

This project uses [EditorConfig](http://editorconfig.org/) to help maintain consistency across project files. There are
[plugins available for most IDEs](http://editorconfig.org/#download) which will apply these conventions for you
automatically.

Your IDE might not support the `max_line_length` option, so set this to `120` in your IDE, just in case.

### PHP

PHP for this project is [PSR-2](http://www.php-fig.org/psr/psr-2/) compliant. You can use
[PHP Coding Standards Fixer](http://cs.sensiolabs.org) to automatically fix up non-compliant code.

The following rules have been excluded in `phpcs.xml` to support SilverStripe 3.x flavor PHP.

- PSR1.Classes.ClassDeclaration.MissingNamespace
- PSR1.Classes.ClassDeclaration.MultipleClasses
- PSR1.Methods.CamelCapsMethodName.NotCamelCaps
- PSR1.Methods.CamelCapsMethodName.NotCamelCaps

## Copyright

**IMPORTANT: By supplying code to the Somar Design Studios core team in patches, tickets and pull requests, you agree to
assign copyright of that code to Somar Design Studios, on the condition that Somar Design Studios releases that code
under the BSD license.**

We ask for this so that the ownership in the license is clear and unambiguous, and so that community involvement doesn't
stop us from being able to continue supporting these projects. By releasing this code under a permissive license, this
copyright assignment won't prevent you from using the code in any way you see fit.
