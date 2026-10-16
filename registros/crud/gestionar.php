<?php
require_once __DIR__ . '/../conexion.php';
$config = [
 'alumnos'=>['tabla'=>'alumnos','pk'=>'idalumn','titulo'=>'Alumnos','campos'=>['matricula_al'=>'Matrícula','nombre_al'=>'Nombre','apaterno_al'=>'Apellido paterno','amaterno_al'=>'Apellido materno','dom_al'=>'Domicilio','mail_al'=>'Correo electrónico','tel_al'=>'Teléfono','estatus_al'=>'Estatus'],'opciones'=>['estatus_al'=>['ALTA','BAJA']]],
 'profesores'=>['tabla'=>'profesores','pk'=>'idprof','titulo'=>'Profesores','campos'=>['nocontrol_prof'=>'Número de control','nombre_prof'=>'Nombre','apaterno_prof'=>'Apellido paterno','amaterno_prof'=>'Apellido materno','dom_prof'=>'Domicilio','mail_prof'=>'Correo electrónico','tel_prof'=>'Teléfono','estatus_prof'=>'Estatus'],'opciones'=>['estatus_prof'=>['ALTA','BAJA']]],
 'materias'=>['tabla'=>'materias','pk'=>'idmateria','titulo'=>'Materias','campos'=>['descripcion_mat'=>'Descripción','estatus_mat'=>'Estatus'],'opciones'=>['estatus_mat'=>['ACTIVA','INACTIVA']]],
 'grupos'=>['tabla'=>'grupos','pk'=>'idgrupo','titulo'=>'Grupos','campos'=>['descripcion_grupo'=>'Descripción del grupo','estatus_grupo'=>'Estatus'],'opciones'=>['estatus_grupo'=>['ALTA','BAJA']]]
];
$tipo=$_REQUEST['tipo']??'alumnos'; if(!isset($config[$tipo])){$tipo='alumnos';}
$c=$config[$tipo]; $mensaje=''; $clase='success'; $editar=null;
function h($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}
function bind_dynamic($stmt,$types,&$values){$args=[];$args[]=$types;foreach($values as $k=>&$value){$args[]=&$value;}call_user_func_array([$stmt,'bind_param'],$args);}
if($_SERVER['REQUEST_METHOD']==='POST'){
 try {
  $accion=$_POST['accion']??''; $id=(int)($_POST['id']??0);
  if($accion==='eliminar'){
   $s=$conexion->prepare("DELETE FROM {$c['tabla']} WHERE {$c['pk']}=?");$s->bind_param('i',$id);$s->execute();$mensaje='Registro eliminado correctamente.';
  } elseif($accion==='guardar'){
   $valores=[]; foreach($c['campos'] as $campo=>$etiqueta){$valores[$campo]=trim($_POST[$campo]??'');}
   if($id>0){$set=implode(',',array_map(fn($x)=>"$x=?",array_keys($valores)));$sql="UPDATE {$c['tabla']} SET $set WHERE {$c['pk']}=?";$s=$conexion->prepare($sql);$types=str_repeat('s',count($valores)).'i';$vals=array_values($valores);$vals[]=$id;bind_dynamic($s,$types,$vals);$s->execute();$mensaje='Registro actualizado correctamente.';}
   else{$cols=implode(',',array_keys($valores));$marks=implode(',',array_fill(0,count($valores),'?'));$s=$conexion->prepare("INSERT INTO {$c['tabla']} ($cols) VALUES ($marks)");$vals=array_values($valores);bind_dynamic($s,str_repeat('s',count($vals)),$vals);$s->execute();$mensaje='Registro agregado correctamente.';}
  }
 } catch(Throwable $e){$mensaje='No se pudo completar la operación. Verifica que el dato no esté repetido y que el registro no esté relacionado con otros datos.';$clase='danger';}
}
if(isset($_GET['editar'])){$id=(int)$_GET['editar'];$s=$conexion->prepare("SELECT * FROM {$c['tabla']} WHERE {$c['pk']}=?");$s->bind_param('i',$id);$s->execute();$editar=$s->get_result()->fetch_assoc();}
$r=$conexion->query("SELECT * FROM {$c['tabla']} ORDER BY {$c['pk']} DESC");
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Administrar <?=h($c['titulo'])?></title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="../assets/estilos.css" rel="stylesheet"></head><body><main class="container py-4"><a href="../index.php" class="btn btn-secondary mb-3">← Menú principal</a><h1>Administrar <?=h($c['titulo'])?></h1><p class="text-muted">Crear, consultar, actualizar y eliminar registros (CRUD).</p>
<?php if($mensaje):?><div class="alert alert-<?=h($clase)?>"><?=h($mensaje)?></div><?php endif;?>
<div class="card mb-4"><div class="card-body"><h4><?= $editar?'Editar registro':'Agregar registro' ?></h4><form method="post" class="row g-3"><input type="hidden" name="tipo" value="<?=h($tipo)?>"><input type="hidden" name="accion" value="guardar"><input type="hidden" name="id" value="<?=h($editar[$c['pk']]??0)?>">
<?php foreach($c['campos'] as $campo=>$etiqueta):?><div class="col-md-6"><label class="form-label"><?=h($etiqueta)?></label><?php if(isset($c['opciones'][$campo])):?><select class="form-select" name="<?=h($campo)?>" required><?php foreach($c['opciones'][$campo] as $op):?><option value="<?=h($op)?>" <?=($editar[$campo]??($op===$c['opciones'][$campo][0]?$op:''))===$op?'selected':''?>><?=h($op)?></option><?php endforeach;?></select><?php else:?><input class="form-control" name="<?=h($campo)?>" value="<?=h($editar[$campo]??'')?>" <?=str_contains($campo,'mail_')?'type="email"':'type="text"'?> required><?php endif;?></div><?php endforeach;?>
<div class="col-12"><button class="btn btn-principal"><?= $editar?'Guardar cambios':'Agregar registro' ?></button><?php if($editar):?> <a class="btn btn-outline-secondary" href="../crud/gestionar.php?tipo=<?=h($tipo)?>">Cancelar</a><?php endif;?></div></form></div></div>
<div class="card"><div class="card-body"><h4>Registros existentes</h4><div class="table-responsive"><table class="table table-striped table-hover align-middle"><thead class="table-dark"><tr><th>ID</th><?php foreach($c['campos'] as $etiqueta):?><th><?=h($etiqueta)?></th><?php endforeach;?><th>Acciones</th></tr></thead><tbody><?php while($f=$r->fetch_assoc()):?><tr><td><?=h($f[$c['pk']])?></td><?php foreach($c['campos'] as $campo=>$etiqueta):?><td><?=h($f[$campo])?></td><?php endforeach;?><td class="text-nowrap"><a class="btn btn-sm btn-warning" href="../crud/gestionar.php?tipo=<?=h($tipo)?>&editar=<?=h($f[$c['pk']])?>">Editar</a> <form method="post" class="d-inline" onsubmit="return confirm('¿Seguro que deseas eliminar este registro?')"><input type="hidden" name="tipo" value="<?=h($tipo)?>"><input type="hidden" name="accion" value="eliminar"><input type="hidden" name="id" value="<?=h($f[$c['pk']])?>"><button class="btn btn-sm btn-danger">Eliminar</button></form></td></tr><?php endwhile;?></tbody></table></div></div></div></main></body></html>
