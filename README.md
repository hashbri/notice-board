# notice-board

公告资讯发布系统 · PHP REST API + 前后端分离

管理端写公告、存草稿、改删；前台按分页浏览、点开看详情。后端是单入口 PHP REST API，前端原生 JS，两边只通过 JSON 通信——**前端换任何技术栈，后端一行不用改**。

## 截图

| 前台列表页 | 资讯详情页 | 管理端面板 |
|---|---|---|
| _待补_ | _待补_ | _待补_ |

## 功能

- [x] 资讯列表（分页，每页 5 条）
- [x] 资讯详情（正文保留换行）
- [x] 资讯发布（前后端双重校验）
- [x] 资讯编辑（列表内点「编辑」回填，同一按钮切换 POST / PUT）
- [x] 资讯删除（软删除，可恢复）
- [x] 草稿机制（前台不可见，管理端置顶并打「草稿」标签）
- [x] 前台接口与管理端接口分离（`/api/articles` vs `/api/admin/articles`）

## 技术栈

| 层 | 用了什么 | 为什么 |
|---|---|---|
| 后端 | PHP 7.4 原生 + PDO | **不引框架**。5 个接口上框架是负担；路由分发、参数校验、分页这些底层动作，正是要练的东西 |
| 数据库 | MySQL 5.7 / InnoDB / utf8mb4 | 要事务和行锁。用 `utf8mb4` 而非 `utf8`——后者存不了 emoji |
| 前端 | 原生 HTML / CSS / JS | 无构建步骤，改完刷新就见效。3 个页面引入工程链是负收益 |
| 通信 | JSON over HTTP，`fetch` | 前后端只交换数据，不交换 HTML |

**零第三方依赖**：没有 `composer.json`，没有 `package.json`。clone 下来直接跑。

## 快速开始

```bash
mysql -u root -p < backend/sql/schema.sql          # 建库 + 建表 + 14 条数据
cd backend  && php -S localhost:8000 -t public public/router.php
cd frontend && php -S localhost:5500 -t .
```

打开 `http://localhost:5500/index.html`。完整步骤（含改数据库密码、6 项验证清单）见 [使用说明](docs/使用说明.md)。

## 文档

- [接口文档](docs/api.md) — 5 个接口的地址、参数、返回、错误码
- [设计决策](docs/design.md) — 每个「为什么不用另一种方案」
- [使用说明](docs/使用说明.md) — 从零到跑通，含 7 条常见问题

## 我踩的坑

每条三行：现象 / 原因 / 怎么修的。详见 [设计决策 · 第 6 节](docs/design.md)。

**1. 分页排序不稳定，同一秒发布的数据会重复或遗漏**

`created_at` 只精确到秒，`ORDER BY created_at DESC` 在多条同秒记录上顺序不确定。两次查询之间顺序一变，翻页就会重复一条、丢掉一条。→ 补唯一的第二排序键 `ORDER BY created_at DESC, id DESC`。

**2. `$_POST` 在 JSON 请求下是空的**

PHP 只自动解析 `application/x-www-form-urlencoded` 和 `multipart/form-data`。前端 `fetch` 发 `application/json` 时，数据在 body 里但没进 `$_POST`。→ `file_get_contents('php://input')` + `json_decode($raw, true)`。

**3. `rowCount()` 返回 0 不代表记录不存在**

MySQL 的 `UPDATE` 分 matched 和 changed 两步，`rowCount()` 给的是 **changed**。值没变时返回 0，和"id 不存在"返回的是同一个值。用它判断存在性，会把"内容没改动"误报成 404。→ 拆成先 `SELECT id` 判存在，再执行 `UPDATE`。

**4. 分页 SQL 报 1064 语法错误**

PDO 默认开模拟预处理，`LIMIT ?, ?` 被当字符串拼成 `LIMIT '0','5'`。→ 连接时设 `PDO::ATTR_EMULATE_PREPARES => false`，走 MySQL 真预处理。

**5. 存了草稿，结果哪里都找不到**

三层原因叠在一起：列表接口写死 `WHERE status = 1`（前台看不到，这是对的）、详情接口也写死 `status = 1`（草稿直接 404）、而管理端复用了同一个列表接口（于是管理端也看不到）。→ 拆出 `/api/admin/articles` 前缀，管理端走这里不过滤 `status`；管理端列表改用 `ORDER BY status ASC`，草稿置顶。

## 已知不足

完整 8 条（含触发条件）见 [设计决策 · 第 7 节](docs/design.md)。

| 不足 | 影响 |
|---|---|
| 管理端无鉴权 | 任何人可增删改，也可读草稿。**上生产前必须补登录** |
| 数据库密码硬编码在 `index.php` | 已进 Git 历史，改代码擦不掉 |
| 无全局异常捕获 | 数据库挂了会输出 HTML，前端 `res.json()` 崩溃 |
| 管理端查询未走索引 | `EXPLAIN` 为 `type=ALL`。20 行无感，万级会慢 |
| 软删除无清理机制 | 表会无限增长 |

## License

[MIT](LICENSE)
