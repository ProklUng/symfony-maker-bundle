# Бандл с консольной командой, генерирующей заготовки бандлов Symfony

### Установка

1) `composer require proklung/symfony-maker-bundle`

2) Инициализировать бандл в приложении.

### Конфигурация

В файле maker_bundle.yaml ключ `bundle_dir` - директория, куда будут помещены сгенерированные бандлы.
По умолчанию /local/classes/Bundles/

### Использование

`php bin/console maker:make-bundle TestBundle`

Где TestBundle - название бандла.

По завершению команда пропишет созданный бандл в `/local/configs/standalone_bundles.php`.
Если это не требуется, то нужно запускать команду с ключом `--init=false`. 

