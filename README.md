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

### tomk79/burdock-project-manager v0.1.1 (リリース日未定)

- `$branch->get_entry_script()` を追加。

### tomk79/burdock-project-manager v0.1.0 (2021年5月28日)

- プレビュー環境ディレクトリの区切り文字を、ハイフン3つからハイフン4つに変更した。

### tomk79/burdock-project-manager v0.0.1 (2020年10月30日)

- Initial Release.


## ライセンス - License

Copyright (c)2021 Tomoya Koyanagi, and Pickles Project<br />
MIT License https://opensource.org/licenses/mit-license.php


## 作者 - Author

- Tomoya Koyanagi <tomk79@gmail.com>
- website: <https://www.pxt.jp/>
- Twitter: @tomk79 <https://twitter.com/tomk79/>
