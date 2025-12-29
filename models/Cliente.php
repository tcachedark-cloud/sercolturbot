<?php
class Cliente{
 function crear($pdo,$n,$t,$tour,$f,$p){
  $s=$pdo->prepare("INSERT INTO clientes VALUES(NULL,?,?,?,?,?)");
  $s->execute([$n,$t,$tour,$f,$p]);
  return $pdo->lastInsertId();
 }
 function listar($pdo){
  return $pdo->query("SELECT * FROM clientes")->fetchAll(PDO::FETCH_ASSOC);
 }
}
?>