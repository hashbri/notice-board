<?php
// ============================================================
// 路由转发
//
// 作用：让所有请求都进 index.php，由 index.php 根据路径分派
// 不加这个文件，访问 /api/articles 会 404（因为不存在这个文件）
// ============================================================

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// 如果请求的是一张真实存在的文件（比如图片、css），直接交回给内置服务器
if ($path !== '/' && is_file(__DIR__ . $path)) {
    return false;
}

// 其他所有请求，都交给 index.php
require __DIR__ . '/index.php';
