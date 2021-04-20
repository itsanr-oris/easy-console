## Foris/Easy/Console

基于Laravel artisan框架提取的简易终端交互扩展包

[![Build Status](https://travis-ci.com/itsanr-oris/easy-console.svg?branch=master)](https://travis-ci.com/itsanr-oris/easy-console)
[![codecov](https://codecov.io/gh/itsanr-oris/easy-console/branch/master/graph/badge.svg)](https://codecov.io/gh/itsanr-oris/easy-console)
[![Latest Stable Version](https://poser.pugx.org/f-oris/easy-console/v/stable)](https://packagist.org/packages/f-oris/easy-console)
[![Latest Unstable Version](https://poser.pugx.org/f-oris/easy-console/v/unstable)](https://packagist.org/packages/f-oris/easy-console)
[![Total Downloads](https://poser.pugx.org/f-oris/easy-console/downloads)](https://packagist.org/packages/f-oris/easy-console)
[![License](https://poser.pugx.org/f-oris/easy-console/license)](LICENSE)

## 版本说明

|  版本 | php | 备注  |
|  ---  | ---- | ---- |
| < 1.0 | >= 5.5 | 非正式版本，后续移除 |
| >= 1.0, < 2.0 | >= 7.1 | 正式版本 |
| >= 2.0 | >= 5.5 | 正式版本，兼容php-5.5运行环境，同时增加了测试辅助相关组件，移除限制Command代码文件只能存放在`src`目录限制等 |

## 安装使用

#### 环境要求

- >= php-5.5

#### 通过`composer`安装

```bash
composer require f-oris/easy-console:^2.0
```

## 基本用法

#### 1. 准备工作

通过composer引入扩展包后，在项目目录下创建一个文件夹，如命名为`src`，里面创建一个子文件夹`Commands`，一个`Application.php`类文件，同时在项目根目录创建一个入口文件，如命名为`artisan`，各文件相关内容如下

```php
<?php

# Application.php

namespace Foris\Easy\Console\Demo;

/**
 * Class Application
 */
class Application extends \Foris\Easy\Console\Application
{
    /**
     * Register the commands for the application.
     *
     * @throws \ReflectionException
     */
    protected function commands()
    {
        parent::commands();
        $this->load(__DIR__ . '/Commands');
    }
}
```

```php
#!/usr/bin/env php
<?php

# artisan

require __DIR__ . '/vendor/autoload.php';

$app = new \Foris\Easy\Console\Demo\Application(__DIR__);

$app->run();
```

创建完毕后，项目的主要相关目录结构如下

```
.
├── src
│   ├── Application.php
│   └── Commands
├── artisan
├── composer.json
├── composer.lock
└── vendor
```

#### 2. 创建自定义命令

在项目根目录下，通过扩展包自带指令`make:command`，创建一个自定义命令`HelloCommand`

```bash
php artisan make:command HelloCommand
```

命令创建完毕后，在`src/Commands`目录找到`HelloCommand.php`文件，以在控制台输出`Hello world`为例，编写业务代码代码，代码如下

```php
<?php

namespace Foris\Easy\Console\Demo\Commands;

use Foris\Easy\Console\Commands\Command;

/**
 * Class HelloCommand
 */
class HelloCommand extends Command
{
    /**
     * Command name
     * 
     * @var string 
     */
    protected $name = 'hello';
    
    /**
     * Command description 
     * 
     * @var string 
     */
    protected $description = 'This is a demo command.';
    
    /**
     * Execute the console command.
     */
    protected function handle()
    {
        $this->line('Hello world');
    }
}
```

返回到项目根目录下，执行命令`php artisan hello`即可在终端输出文字内容`Hello world`

> 更多使用方法，参考Wiki文档说明

## License

MIT License

Copyright (c) 2019-present F.oris <us@f-oris.me>
