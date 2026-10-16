<?php
require_once __DIR__ . '/../conexion.php'; $mensaje=""; $clase="success";
if($_SERVER["REQUEST_METHOD"]==="POST"){
 try{$s=$conexion->prepare("INSERT INTO asignar_grupo(idgrupo,idalumn,idprof) VALUES(?,?,?)");$s->bind_param("iii",$_POST["grupo"],$_POST["alumno"],$_POST["profesor"]);$s->execute();$mensaje="Grupo asignado correctamente.";}catch(Throwable $e){$mensaje="Error: ".$e->getMessage();$clase="danger";}
}
$alumnos=$conexion->query("SELECT idalumn,matricula_al,nombre_al,apaterno_al FROM alumnos WHERE estatus_al='ALTA' ORDER BY nombre_al");
$grupos=$conexion->query("SELECT idgrupo,descripcion_grupo FROM grupos WHERE estatus_grupo='ALTA' ORDER BY descripcion_grupo");
$profesores=$conexion->query("SELECT idprof,nombre_prof,apaterno_prof FROM profesores WHERE estatus_prof='ALTA' ORDER BY nombre_prof");
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Asignar grupo</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="../assets/estilos.css" rel="stylesheet"></head><body class="bg-light"><main class="container py-4"><a href="../index.php" class="btn btn-secondary mb-3">Volver al menú</a><div class="card shadow-sm"><div class="card-body"><h2>Asignar grupo</h2><?php if($mensaje):?><div class="alert alert-<?php echo $clase;?>"><?php echo htmlspecialchars($mensaje);?></div><?php endif;?><form method="post">
<label class="form-label mt-2">Alumno</label><select name="alumno" class="form-select" required><option value="">Selecciona</option><?php while($f=$alumnos->fetch_assoc()):?><option value="<?php echo $f['idalumn'];?>"><?php echo htmlspecialchars($f['matricula_al'].' - '.$f['nombre_al'].' '.$f['apaterno_al']);?></option><?php endwhile;?></select>
<label class="form-label mt-2">Grupo</label><select name="grupo" class="form-select" required><option value="">Selecciona</option><?php while($f=$grupos->fetch_assoc()):?><option value="<?php echo $f['idgrupo'];?>"><?php echo htmlspecialchars($f['descripcion_grupo']);?></option><?php endwhile;?></select>
<label class="form-label mt-2">Profesor</label><select name="profesor" class="form-select" required><option value="">Selecciona</option><?php while($f=$profesores->fetch_assoc()):?><option value="<?php echo $f['idprof'];?>"><?php echo htmlspecialchars($f['nombre_prof'].' '.$f['apaterno_prof']);?></option><?php endwhile;?></select><button class="btn btn-success mt-3">Guardar asignación</button></form></div></div></main></body></html>
