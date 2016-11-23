# Dravencms FAQ module

This is a simple FAQ module for dravencms

## Instalation

The best way to install dravencms/faq is using  [Composer](http://getcomposer.org/):


```sh
$ composer require dravencms/faq:@dev
```

Then you have to register extension in `config.neon`.

```yaml
extensions:
	faq: Dravencms\Faq\DI\FaqExtension
```
