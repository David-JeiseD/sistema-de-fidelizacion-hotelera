@include('errors.base', [
    'codigo' => 403,
    'icono' => 'fa-lock',
    'titulo' => 'Sin acceso',
    'mensaje' => $exception->getMessage() ?: 'Tu rol no tiene acceso a esta sección.',
])
