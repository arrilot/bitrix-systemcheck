[![Latest Stable Version](https://poser.pugx.org/arrilot/bitrix-systemcheck/v/stable.svg)](https://packagist.org/packages/arrilot/bitrix-systemcheck/)

# Bitrix System Checks

*Пакет представляет из себя модуль для Битрикса, который является более гибкой и функциональной альтернативой штатной битриксовой функциональности "Проверка системы"*

### Установка

1. Добавляем в `composer.json` следующие строчки если их там еще нет

```
  "extra": {
    "installer-paths": {
      "app/local/modules/{$name}/": [
        "type:bitrix-d7-module",
        "type:bitrix-module"
      ]
    }
  },
```

2.

`composer require arrilot/bitrix-systemcheck`

3. Устанавливаем модуль на странице /bitrix/admin/partner_modules.php?lang=ru

4. Добавляем local/modules/arrilot.systemcheck в .gitignore

### Теория

#### Возможности bitrix-systemcheck

- Предоставляет обширную коллекцию готовых проверок, которые включают в себя как как и проверки из встроенного в Битрикс решения, так и дополнительные
- Позволяет создавать новые, специфичные для приложения проверки
- Позволяет создавать неограниченное количество мониторингов с любым набором проверок в каждом
- Позволяет запускать эти мониторинги как через админку (web thread), так и через консоль (cli)
- Умеет логировать результат через PSR-логгер и помогает автоматизировать таким образом контроль за приложением.
- При необходимости дружит c basic auth в отличие от встроенной проверки

#### Основные сущности

Основные сушности пакета - проверка и мониторинг.

Проверка - класс, наследующий `Arrilot\BitrixSystemCheck\Checks\Check`. Цель проверки - вернуть `true/false` в методе `run()`.
Если проверка возвращает false, то где-то перед `return false;` следует вызвать метод `$this->logError("текст ошибки тут");` один или несколько раз чтобы было понятна причина провала проверки.
Пример:

```php
class BitrixDebugIsTurnedOn extends Check
{
    /**
     * @return string
     */
    public function name()
    {
        return "Проверка на exception_handling.debug = true ...";
    }

    /**
     * @return boolean
     */
    public function run()
    {
        $config = Configuration::getInstance()->get('exception_handling');

        if (empty($config['debug'])) {
            $this->logError('Значение конфигурации exception_handling.debug должно быть true в данном окружении');
            return false;
        }

        return true;
    }
}
```

Мониторинг - класс, наследующий `Arrilot\BitrixSystemCheck\Checks\Monitoring`
Логически он представляет из себя именованный набор проверок, причем этот набор может зависить от окружения и других параметров.

Пример:

```php
class FullMonitoring extends Monitoring
{
    /**
     * Russian monitoring name
     *
     * @return string
     */
    public function name()
    {
        return 'Полный мониторинг';
    }

    /**
     * Monitoring code (id)
     *
     * @return string
     */
    public function code()
    {
        return 'full';
    }

    /**
     * @return array
     */
    public function checks()
    {
        if (in_production()) {
            return [
                new BitrixChecks\RequiredPhpModules(),
                new BitrixChecks\PhpSettings(),
                ...
        }

        return [
            new BitrixChecks\RequiredPhpModules(),
            new BitrixChecks\PhpSettings(),
            ...
        ];
    }

    /**
     * @return Logger|null|\Psr\Log\LoggerInterface
     */
    public function logger()
    {
        $logger = new Logger('monitoring-full');
        $logger->pushHandler(new StreamHandler(logs_path('systemcheck/monitoring-full.log')));

        return $logger;
    }
}
```

### Использование

1. Реализовуем мониторинг/мониторинги (пример выше)
2. Добавляем их в ``.setting_extra.php`

```php
'bitrix-systemcheck' => [
    'value' => [
        'monitorings' => [
            \System\SystemCheck\Monitorings\FullMonitoring::class,
            \System\SystemCheck\Monitorings\BriefMonitoring::class,
        ]
    ],
    'readonly' => true,
],
```

3. Теперь можно запустить выбранный мониторинг в админке `/bitrix/admin/arrilot_systemcheck_monitoring.php?code=full`
4. Регистрируем команду `Arrilot\BitrixSystemCheck\Console\SystemCheckCommand` в консольном приложении на базе symfony/console если у нас есть такое
5. После этого мы сможем запускать мониторинги через консольное приложение следующим образом :
`php our_console_app.php system:check full`, где `full` - код мониторинга, возвращаемый методом `Monitoring::code()`
6. При желании ставим консольную команду запускающую мониторинг в крон и получаем логи/алерты согласно нашему логгеру из `Monitoring::logger()`

Имеющиеся в пакете проверки можно посмотреть в `src/Checks`

### Добавление проверок в пакет aka Contributing

Любой желающий может через Pull Request (PR) предложить свою проверку для рассмотрения на добавление в ядро модуля.
При этом необходимо следовать следующим несложным правилам.

1. Перед началом работы на проверкой нужно удостовериться что такой проверки еще нет в пакете и в открытых PR
2. Один PR = одна проверка
3. Проверка должна быть достаточно общей и подходить таким образом большому числу приложений. Если ваша проверка завязана на ваше приложение, то лучше ей в нём и оставаться
4. Нужно предусмотреть чтобы проверка не закончилась Fatal Exception/Error при запуске на каком-то другом приложении. Например если в ней используется какой-то дополнительный модуль php, то нужно сделать проверку `extension_loaded()` и `$this->skip()` или `return false` в случае false в `extension_loaded()`
5. Из предыдущего пункта следует, что проверка должна по минимуму использовать внешние зависимости (модули php, композер-пакеты и т д)
6. Если проверка является еще почему-то нереализованным аналогом проверки из битриксовой "Проверки системы", то она должна идти в неймспейс `Arrilot\BitrixSystemCheck\Checks\Bitrix`, в противном случае - в `Arrilot\BitrixSystemCheck\Checks\Custom`
7. Код должен следовать PSR-2
