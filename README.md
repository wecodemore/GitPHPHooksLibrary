# Git PHP Hooks - official Library

This is a set of Git PHP Hooks examples, ready to use in conjunction
with [GitPHPHooks](https://github.com/wecodemore/GitPHPHooks).

The Hooks are available in the [`src` directory](https://github.com/wecodemore/GitPHPHooksLibrary/tree/master/src)
of this repo. You can read more about the naming convention [here](https://github.com/wecodemore/GitPHPHooks#naming-convention).

## Install

This repo is registered to Packagist. You can include it by adding

    "wcm/git-php-hooks-library": "dev-master"

to your projects `composer.json` file.

On **Windows** you might want to use the `--prefer-source` flag. It's a
[_known issue_](https://github.com/composer/composer/issues/604) that Windows will
use `zip` and instantly extract the `pdepend/pdepend` package and fail else. The _fix_ is:

    composer install --prefer-source

(The same works with `composer update`).

## Examples included

 * PHP Lint as `pre-commit` task, priority: 10
 * PHP Mess Detector as `pre-push` task, priority: 10

## Pull Requests

I am happy to accept pull requests. As it really doesn't matter (to me) what coding style you use,
there's no convention forced. Just add your file, test it and send the PR. :)