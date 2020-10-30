# burdock-project-manager

## インストール - Install

```
composer require tomk79/burdock-project-manager
```


## 使い方 - Usage

```php
<?php
require_once('vendor/autoload.php');
$burdockProjectManager = new \tomk79\picklesFramework2\burdock\projectManager\main( '/path/to/bd_data/' );

$project_status = $burdockProjectManager->project('project_id')->branch('master', 'preview')->status();
```


## 更新履歴 - Change log

### tomk79/burdock-project-manager v0.0.1 (2020年10月30日)

- Initial Release.


## ライセンス - License

Copyright (c)2020 Tomoya Koyanagi, and Pickles Project<br />
MIT License https://opensource.org/licenses/mit-license.php


## 作者 - Author

- Tomoya Koyanagi <tomk79@gmail.com>
- website: <https://www.pxt.jp/>
- Twitter: @tomk79 <https://twitter.com/tomk79/>
