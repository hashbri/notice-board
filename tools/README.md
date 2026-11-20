# tools · 启动脚本

三个批处理文件，双击即可。**顺序不能乱**：数据库 → 后端 → 前端。

| 文件 | 作用 | 端口 | 起来后访问 |
|---|---|---|---|
| `1-start-db.bat` | 启动 MySQL | 3306 | — |
| `2-start-api.bat` | 启动 PHP 后端 | 8000 | http://localhost:8000/api/articles |
| `3-start-web.bat` | 启动前端静态服务 | 5500 | http://localhost:5500/index.html |

**每个窗口都要保持打开**，关掉就等于停止服务。

## 脚本做了什么

- 用 `%~dp0` 定位脚本自身所在目录，再进 `..\backend` / `..\frontend`——**项目整体搬到别的盘也不用改路径**
- 启动前先查端口，已被占用就提示并退出，不会出现两个服务抢同一端口
- PHP 优先用 PATH 里的 `php`，找不到再回落到 phpStudy 默认路径 `D:\phpstudy_pro\Extensions\php\php7.4.33nts\php.exe`
- `2-start-api.bat` 还会检查 3306：没监听时给出警告——后端能起来，但查库会失败，早点提示比让用户看 500 强

## 换环境要改什么

| 情况 | 改哪里 |
|---|---|
| 不用 phpStudy | 改 `1-start-db.bat` 的 `MYSQL_HOME`；或改用 XAMPP / WAMP 启动 MySQL，跳过这个脚本 |
| PHP 既不在 PATH 也不在默认路径 | 改 `2-start-api.bat`、`3-start-web.bat` 里的 `PHP_EXE` |
| 端口冲突要换端口 | 改脚本里的端口，**并且**同步改三个 HTML 里的 `localhost:8000`（见 `docs/使用说明.md` Q2） |

## 为什么文件名和内容都是英文

Windows 批处理在**纯 ASCII** 下最稳。中文文件名 + 中文内容的 bat，在 GBK / UTF-8 混用时容易乱码或一打开就闪退，所以这里全部用英文，换行也统一成 CRLF。

## 不想用脚本

手动敲两条命令等价：

```bash
cd backend  && php -S localhost:8000 -t public public/router.php
cd frontend && php -S localhost:5500 -t .
```
