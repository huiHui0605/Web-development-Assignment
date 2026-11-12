<?php
 declare(strict_types=1);
 session_set_cookie_params(['httponly'=>true,'samesite'=>'Lax','secure'=>!empty($_SERVER['HTTPS'])]);
 session_start();
 header('X-Content-Type-Options: nosniff');
 header('Cache-Control: no-store');
 function e($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
 function db(): PDO {
 static $pdo;
 if (!$pdo) { try {
 $pdo=new PDO('mysql:host='.(getenv('DB_HOST') ?: '127.0.0.1').';dbname=volunteer_db;charset=utf8mb4',getenv('DB_USER') ?: 'root',getenv('DB_PASSWORD') ?: '',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_EMULATE_PREPARES=>false,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
 } catch (PDOException $ex) { error_log($ex->getMessage()); http_response_code(503); exit('Database unavailable. Check database configuration and import database.sql.'); } }
 return $pdo;
 }
 const CATEGORIES=['Community Outreach','Environmental Care','Food Distribution','Education Support','Elderly Care','Animal Welfare'];
 const FIELDS=['volunteer_name','ic_number','contact_number','email','service_category','available_date'];
 $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
 function csrf(): string { return '<input type="hidden" name="csrf" value="'.e($_SESSION['csrf']).'">'; }
 function require_post(): void {
 if ($_SERVER['REQUEST_METHOD']!=='POST') { header('Allow: POST'); http_response_code(405); exit('POST required.'); }
 if (!is_string($_POST['csrf'] ?? null) || !hash_equals($_SESSION['csrf'],$_POST['csrf'])) { http_response_code(403); exit('Invalid session token. Reload the form.'); }
 }
 function input(): array {
 $v=[]; $errors=[];
 foreach (FIELDS as $k) { $v[$k]=is_string($_POST[$k] ?? null) ? trim($_POST[$k]) : ''; if ($v[$k]==='') $errors[$k]='This field is required.'; }
 foreach (['volunteer_name'=>100,'ic_number'=>20,'contact_number'=>20,'email'=>100,'service_category'=>100] as $k=>$max) if (strlen($v[$k])>$max) $errors[$k]='Maximum '.$max.' bytes.';
 if ($v['ic_number']!=='' && !preg_match('/^(?:[0-9]{12}|[0-9]{6}-[0-9]{2}-[0-9]{4})$/D',$v['ic_number'])) $errors['ic_number']='Use 12 digits or YYMMDD-SS-NNNN.';
 if ($v['contact_number']!=='' && !preg_match('/^\+?[0-9][0-9 ()-]{6,19}$/D',$v['contact_number'])) $errors['contact_number']='Enter a valid contact number (7–20 characters).';
 if (!filter_var($v['email'],FILTER_VALIDATE_EMAIL)) $errors['email']='Enter a valid email address.';
 if (!in_array($v['service_category'],CATEGORIES,true)) $errors['service_category']='Choose a listed category.';
 $date=DateTimeImmutable::createFromFormat('!Y-m-d',$v['available_date']);
 if (!$date || $date->format('Y-m-d')!==$v['available_date'] || $v['available_date']<'1000-01-01') $errors['available_date']='Choose a valid date.';
 return [$v,$errors];
 }
 function id(): int { $id=filter_var($_GET['id'] ?? $_POST['id'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]); if (!$id) { http_response_code(400); exit('Invalid volunteer ID.'); } return $id; }
 function record(int $id): array { $s=db()->prepare('SELECT * FROM volunteers WHERE volunteer_id=?'); $s->execute([$id]); $row=$s->fetch(); if (!$row) { http_response_code(404); exit('Volunteer not found.'); } return $row; }
 function notice(string $message): never { $_SESSION['notice']=$message; header('Location: view.php',true,303); exit; }
