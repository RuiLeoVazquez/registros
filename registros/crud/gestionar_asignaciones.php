<?php
require_once __DIR__ . '/../conexion.php';
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
$tipos = [
 'grupos' => [
  'titulo'=>'Asignaciones de grupo', 'tabla'=>'asignar_grupo', 'pk'=>'idasignar_grupo',
  'campos'=>['idgrupo'=>'Grupo','idalumn'=>'Alumno','idprof'=>'Profesor'],
  'consultar'=>'SELECT ag.idasignar_grupo AS id, ag.idgrupo, ag.idalumn, ag.idprof, g.descripcion_grupo AS grupo, CONCAT(a.matricula_al, " - ", a.nombre_al, " ", a.apaterno_al, " ", a.amaterno_al) AS alumno, CONCAT(p.nocontrol_prof, " - ", p.nombre_prof, " ", p.apaterno_prof, " ", p.amaterno_prof) AS profesor FROM asignar_grupo ag JOIN grupos g ON g.idgrupo=ag.idgrupo JOIN alumnos a ON a.idalumn=ag.idalumn JOIN profesores p ON p.idprof=ag.idprof ORDER BY ag.idasignar_grupo DESC'
 ],
 'materias' => [
  'titulo'=>'Asignaciones de materia', 'tabla'=>'asignar_materia', 'pk'=>'idasignar_materia',
  'campos'=>['idmateria'=>'Materia','idalumn'=>'Alumno','idprof'=>'Profesor','calificacion'=>'Calificación'],
  'consultar'=>'SELECT am.idasignar_materia AS id, am.idmateria, am.idalumn, am.idprof, am.calificacion, m.descripcion_mat AS materia, CONCAT(a.matricula_al, " - ", a.nombre_al, " ", a.apaterno_al, " ", a.amaterno_al) AS alumno, CONCAT(p.nocontrol_prof, " - ", p.nombre_prof, " ", p.apaterno_prof, " ", p.amaterno_prof) AS profesor FROM asignar_materia am JOIN materias m ON m.idmateria=am.idmateria JOIN alumnos a ON a.idalumn=am.idalumn JOIN profesores p ON p.idprof=am.idprof ORDER BY am.idasignar_materia DESC'
 ]
];
$tipo=$_REQUEST['tipo']??'grupos'; if(!isset($tipos[$tipo])) $tipo='grupos'; $c=$tipos[$tipo];
$opciones=[];
$opciones['idgrupo']=$conexion->query("SELECT idgrupo AS id, descripcion_grupo AS nombre FROM grupos WHERE estatus_grupo='ALTA' ORDER BY descripcion_grupo")->fetch_all(MYSQLI_ASSOC);
$opciones['idalumn']=$conexion->query("SELECT idalumn AS id, CONCAT(matricula_al, ' - ', nombre_al, ' ', apaterno_al, ' ', amaterno_al) AS nombre FROM alumnos WHERE estatus_al='ALTA' ORDER BY apaterno_al, nombre_al")->fetch_all(MYSQLI_ASSOC);
$opciones['idprof']=$conexion->query("SELECT idprof AS id, CONCAT(nocontrol_prof, ' - ', nombre_prof, ' ', apaterno_prof, ' ', amaterno_prof) AS nombre FROM profesores WHERE estatus_prof='ALTA' ORDER BY apaterno_prof, nombre_prof")->fetch_all(MYSQLI_ASSOC);
$opciones['idmateria']=$conexion->query("SELECT idmateria AS id, descripcion_mat AS nombre FROM materias WHERE estatus_mat='ACTIVA' ORDER BY descripcion_mat")->fetch_all(MYSQLI_ASSOC);
$mensaje=''; $clase='success'; $editar=null;
if($_SERVER['REQUEST_METHOD']==='POST'){
 try {
  $accion=$_POST['accion']??''; $id=(int)($_POST['id']??0);
  if($accion==='eliminar'){
   $s=$conexion->prepare("DELETE FROM {$c['tabla']} WHERE {$c['pk']}=?"); $s->bind_param('i',$id); $s->execute(); $mensaje='Asignación eliminada correctamente.';
  } elseif($accion==='guardar'){
   $val=[];
   foreach($c['campos'] as $campo=>$label){
    if($campo==='calificacion') { $raw=trim($_POST[$campo]??''); if($raw!=='' && (!is_numeric($raw) || (float)$raw<0 || (float)$raw>100)) throw new Exception('La calificación debe estar entre 0 y 100.'); $val[$campo]=$raw===''?null:$raw; }
    else { $val[$campo]=(int)($_POST[$campo]??0); if($val[$campo]<1) throw new Exception('Selecciona todas las opciones requeridas.'); }
   }
   if($id>0){$sets=implode(',',array_map(fn($x)=>"$x=?",array_keys($val)));$sql="UPDATE {$c['tabla']} SET $sets WHERE {$c['pk']}=?";$s=$conexion->prepare($sql);$types='';$params=[];foreach($val as $k=>$v){$types.=$k==='calificacion'?'d':'i';$params[]=$v;} $types.='i';$params[]=$id;$s->bind_param($types,...$params);$s->execute();$mensaje='Asignación actualizada correctamente.';}
   else{$cols=implode(',',array_keys($val));$marks=implode(',',array_fill(0,count($val),'?'));$s=$conexion->prepare("INSERT INTO {$c['tabla']} ($cols) VALUES ($marks)");$types='';$params=[];foreach($val as $k=>$v){$types.=$k==='calificacion'?'d':'i';$params[]=$v;} $s->bind_param($types,...$params);$s->execute();$mensaje='Asignación registrada correctamente.';}
  }
 } catch(Throwable $e){$mensaje=$e instanceof mysqli_sql_exception ? 'No se pudo completar la operación. Verifica que la relación no esté duplicada y que los datos seleccionados existan.' : $e->getMessage();$clase='danger';}
}
if(isset($_GET['editar'])){$id=(int)$_GET['editar'];$s=$conexion->prepare("SELECT * FROM {$c['tabla']} WHERE {$c['pk']}=?");$s->bind_param('i',$id);$s->execute();$editar=$s->get_result()->fetch_assoc();}
$r=$conexion->query($c['consultar']);
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=h($c['titulo'])?></title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="../assets/estilos.css" rel="stylesheet"></head><body><main class="container py-4"><a href="../index.php" class="btn btn-secondary mb-3">← Menú principal</a><h1><?=h($c['titulo'])?></h1><p class="text-muted">Crear, consultar, actualizar y eliminar asignaciones.</p>
<?php if($mensaje):?><div class="alert alert-<?=h($clase)?>"><?=h($mensaje)?></div><?php endif;?>
<div class="card mb-4"><div class="card-body"><h4><?=$editar?'Editar asignación':'Registrar asignación'?></h4><form method="post" class="row g-3"><input type="hidden" name="tipo" value="<?=h($tipo)?>"><input type="hidden" name="accion" value="guardar"><input type="hidden" name="id" value="<?=h($editar[$c['pk']]??0)?>">
<?php foreach($c['campos'] as $campo=>$label):?><div class="col-md-6"><label class="form-label" for="<?=h($campo)?>"><?=h($label)?></label><?php if($campo==='calificacion'):?><input id="<?=h($campo)?>" class="form-control" type="number" name="calificacion" min="0" max="100" step="0.01" value="<?=h($editar['calificacion']??'')?>" placeholder="0 a 100 (opcional)"><?php else:?><select id="<?=h($campo)?>" class="form-select" name="<?=h($campo)?>" required><option value="">Selecciona <?=h(strtolower($label))?></option><?php foreach($opciones[$campo] as $op):?><option value="<?=h($op['id'])?>" <?=((string)($editar[$campo]??'')===(string)$op['id'])?'selected':''?>><?=h($op['nombre'])?></option><?php endforeach;?></select><?php endif;?></div><?php endforeach;?><div class="col-12"><button class="btn btn-principal"><?=$editar?'Guardar cambios':'Registrar asignación'?></button><?php if($editar):?> <a class="btn btn-outline-secondary" href="gestionar_asignaciones.php?tipo=<?=h($tipo)?>">Cancelar</a><?php endif;?></div></form></div></div>
<div class="card"><div class="card-body"><h4>Asignaciones existentes</h4><div class="table-responsive"><table class="table table-striped table-hover align-middle"><thead class="table-dark"><tr><th>ID</th><?php if($tipo==='grupos'):?><th>Grupo</th><th>Alumno</th><th>Profesor</th><?php else:?><th>Materia</th><th>Alumno</th><th>Profesor</th><th>Calificación</th><?php endif;?><th>Acciones</th></tr></thead><tbody><?php while($f=$r->fetch_assoc()):?><tr><td><?=h($f['id'])?></td><?php if($tipo==='grupos'):?><td><?=h($f['grupo'])?></td><td><?=h($f['alumno'])?></td><td><?=h($f['profesor'])?></td><?php else:?><td><?=h($f['materia'])?></td><td><?=h($f['alumno'])?></td><td><?=h($f['profesor'])?></td><td><?=h($f['calificacion']??'Pendiente')?></td><?php endif;?><td class="text-nowrap"><a class="btn btn-sm btn-warning" href="gestionar_asignaciones.php?tipo=<?=h($tipo)?>&editar=<?=h($f['id'])?>">Editar</a> <form method="post" class="d-inline" onsubmit="return confirm('¿Seguro que deseas eliminar esta asignación?')"><input type="hidden" name="tipo" value="<?=h($tipo)?>"><input type="hidden" name="accion" value="eliminar"><input type="hidden" name="id" value="<?=h($f['id'])?>"><button class="btn btn-sm btn-danger">Eliminar</button></form></td></tr><?php endwhile;?></tbody></table></div></div></div></main></body></html>
