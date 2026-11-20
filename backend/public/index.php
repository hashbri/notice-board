<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}



$pdo = new PDO('mysql:host=127.0.0.1;dbname=notice_board;charset=utf8mb4', 'root', 'root', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,

]);
$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path   = substr($uri, 4);

$isAdmin = false;
if (strpos($path, '/admin/') === 0) {
    $isAdmin = true;
    $path    = substr($path, 6);
}

$parts  = explode('/', trim($path, '/'));
$id     = $parts[1] ?? null;

if ($parts[0] !== 'articles') {
    http_response_code(404);
    echo json_encode(['code' => 1003, 'message' => '接口不存在'], JSON_UNESCAPED_UNICODE);
    exit;
}



if ($method === 'GET' && $id === null) {

  $page = max(1, (int)($_GET['page'] ?? 1));

  $size = (int)($_GET['size'] ?? 5);
  if ($size < 1 || $size > 50) {
      $size = 5;
  }

  $offset = ($page - 1) * $size;

  $where = $isAdmin ? "deleted_at IS NULL" : "status = 1 AND deleted_at IS NULL";

  $order = $isAdmin ? "status ASC, created_at DESC, id DESC" : "created_at DESC, id DESC";

  $total = (int)$pdo->query("SELECT COUNT(*) FROM articles WHERE $where")->    fetchColumn();

  $stmt = $pdo->prepare("SELECT id, title, status, created_at FROM articles WHERE $where ORDER BY $order LIMIT ?, ?");
  $stmt->execute([$offset, $size]);
  $list = $stmt->fetchAll();

  echo json_encode(['code' => 0, 'message' => 'ok', 'data' => ['total' => $total, 'page' => $page, 'size' => $size, 'list' => $list]], JSON_UNESCAPED_UNICODE);
} 



elseif ($method === 'GET' && $id !== null) {

   $sql = "SELECT id, title, content, status, created_at, updated_at FROM articles WHERE id = ? AND deleted_at IS NULL";
   if (!$isAdmin) {
       $sql .= " AND status = 1";
   }

   $stmt = $pdo->prepare($sql);
   $stmt->execute([$id]);
   $row = $stmt->fetch();

    if ($row === false) {
        http_response_code(404);
        echo json_encode(['code' => 1002, 'message' => '文章不存在'], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(['code' => 0, 'message' => 'ok', 'data' => $row], JSON_UNESCAPED_UNICODE);
    }
}



elseif ($method === 'POST' && $id === null) {

    $raw  = file_get_contents('php://input');
    $body = json_decode($raw, true);

        if (!is_array($body)) {
        http_response_code(400);
        echo json_encode(['code' => 1001, 'message' => '请求体不是合法 JSON'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $title   = trim($body['title'] ?? '');
    $content = trim($body['content'] ?? '');
    $status  = (int)($body['status'] ?? 0);

    if ($title === '') {
        http_response_code(400);
        echo json_encode(['code' => 1001, 'message' => '标题不能为空'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    if (mb_strlen($title) > 100) {
        http_response_code(400);
        echo json_encode(['code' => 1001, 'message' => '标题不能超过 100 字'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    if ($content === '') {
        http_response_code(400);
        echo json_encode(['code' => 1001, 'message' => '正文不能为空'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    if ($status !== 0 && $status !== 1) {
        http_response_code(400);
        echo json_encode(['code' => 1001, 'message' => '状态只能是 0 或 1'], JSON_UNESCAPED_UNICODE);
        exit;
    }

     $stmt = $pdo->prepare("INSERT INTO articles (title, content, status) VALUES (?, ?, ?)");
    $stmt->execute([$title, $content, $status]);

    http_response_code(201);
    echo json_encode(['code' => 0, 'message' => '创建成功', 'data' => ['id' => (int)$pdo->lastInsertId()]], JSON_UNESCAPED_UNICODE);

} 



elseif ($method === 'PUT' && $id !== null) {

    $raw  = file_get_contents('php://input');
    $body = json_decode($raw, true);

    if (!is_array($body)) {
        http_response_code(400);
        echo json_encode(['code' => 1001, 'message' => '请求体不是合法 JSON'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $title   = trim($body['title'] ?? '');
    $content = trim($body['content'] ?? '');
    $status  = (int)($body['status'] ?? 1);

    if ($title === '') {
        http_response_code(400);
        echo json_encode(['code' => 1001, 'message' => '标题不能为空'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    if (mb_strlen($title) > 100) {
        http_response_code(400);
        echo json_encode(['code' => 1001, 'message' => '标题不能超过 100 字'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    if ($content === '') {
        http_response_code(400);
        echo json_encode(['code' => 1001, 'message' => '正文不能为空'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    if ($status !== 0 && $status !== 1) {
        http_response_code(400);
        echo json_encode(['code' => 1001, 'message' => '状态只能是 0 或 1'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $check = $pdo->prepare("SELECT id FROM articles WHERE id = ? AND deleted_at IS NULL");
    $check->execute([$id]);
    if ($check->fetch() === false) {
        http_response_code(404);
        echo json_encode(['code' => 1002, 'message' => '文章不存在'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE articles SET title = ?, content = ?, status = ? WHERE id = ? AND deleted_at IS NULL");
    $stmt->execute([$title, $content, $status, $id]);

    echo json_encode(['code' => 0, 'message' => '修改成功', 'data' => ['id' => (int)$id]], JSON_UNESCAPED_UNICODE);
} 



elseif ($method === 'DELETE' && $id !== null) {

    $check = $pdo->prepare("SELECT id FROM articles WHERE id = ? AND deleted_at IS NULL");
    $check->execute([$id]);
    if ($check->fetch() === false) {
        http_response_code(404);
        echo json_encode(['code' => 1002, 'message' => '文章不存在'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE articles SET deleted_at = NOW() WHERE id = ?");
    $stmt->execute([$id]);

    echo json_encode(['code' => 0, 'message' => '已删除', 'data' => ['id' => (int)$id]], JSON_UNESCAPED_UNICODE);

}


else {

    echo json_encode(['code' => 1003, 'message' => '接口不存在'], JSON_UNESCAPED_UNICODE);
}



