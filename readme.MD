# Бандл с консольной командой, генерирующей заготовки бандлов Symfony

Заточен не под оригинальные приложения Symfony, а под связки с другими конструкциями - типа Битрикса или Wordpress.

### Установка

1) `composer require proklung/symfony-maker-bundle`

2) Инициализировать бандл в приложении.

### Конфигурация

В файле `maker_bundle.yaml` ключ `bundle_dir` - директория, куда будут помещены сгенерированные бандлы.
По умолчанию /local/classes/Bundles/, пространство имен - `Local\Bundles`.

   - `config_file_dir` - путь к папке, где лежит конфигурационный файл бандлов (bundles.php в терминах Symfony).
     По умолчанию - `/local/configs/` 
   - `config_file` - Имя конфигурационного файла бандлов (по умолчанию - `standalone_bundles.php` )
   - `namespace` - Пространство имен бандла. По умолчанию -  `Local\Bundles`.

### Использование

`php bin/console maker:make-bundle TestBundle`

Где `TestBundle` - название бандла.

По завершению команда пропишет созданный бандл в `/local/configs/standalone_bundles.php`.
Если это не требуется, то нужно запускать команду с ключом `--init=false`. 

