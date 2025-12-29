<?php
class Reserva{
 function crear($pdo,$cid,$tour,$f,$p){
  $s=$pdo->prepare("INSERT INTO reservas(cliente_id,tour,fecha,personas) VALUES(?,?,?,?)");
  $s->execute([$cid,$tour,$f,$p]);
  return $pdo->lastInsertId();
 }
 function confirmar($pdo,$id){
  $pdo->prepare("UPDATE reservas SET estado='confirmada' WHERE id=?")->execute([$id]);
 }
 function listar($pdo){
  return $pdo->query("SELECT * FROM reservas")->fetchAll(PDO::FETCH_ASSOC);
 }
}
?>