<?php require 'config.php'; require_post(); [$v,$errors]=input();
if ($errors) { http_response_code(422); require 'layout.php'; top('Check registration'); echo '<div class="panel mx-auto" style="max-width:850px"><h1>Check your registration</h1>'; form($v,$errors); echo '</div>'; bottom(); exit; }
try { $s=db()->prepare('INSERT INTO volunteers (volunteer_name,ic_number,contact_number,email,service_category,available_date) VALUES (?,?,?,?,?,?)'); $s->execute(array_values($v)); } catch(PDOException $e) { error_log($e->getMessage()); http_response_code(503); exit('Unable to save registration. Please try again later.'); }
$_SESSION['registered']=$v; header('Location: success.php',true,303); exit;
