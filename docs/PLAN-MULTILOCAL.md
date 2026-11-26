# Plan: de un hotel a una plataforma multi-local

> Estado: **fases 1, 2 y 3 completadas (2026-11-26)**. Siguiente: fase 4 (pantallas de gestión: personal, hoteles y alta de empresas).

## 1. La idea central

Un **hotel individual es simplemente una empresa con un solo hotel**. Así no hay dos sistemas ni casos especiales: las mismas reglas sirven para una cadena de 10 hoteles y para un hostal independiente.

```
Plataforma (tú, super-admin)
└── Empresa  ← quien te compra el sistema (cadena o hotel individual)
    ├── Programa de fidelidad (nombre del club, logo, recompensas)
    ├── Membresías: personas inscritas en su club, con sus puntos
    └── Hoteles / locales
        ├── Tipos de habitación y sus puntos
        ├── Métodos de pago con QR
        ├── Estadías (visitas) y canjes que ocurren ahí
        └── Personal asignado

Personas (globales, una sola vez en toda la plataforma)
└── se inscriben en el club de una o varias empresas (membresías)
```

## 2. Entidades

| Entidad | Pertenece a | Notas |
|---|---|---|
| **Empresa** (nueva) | Plataforma | Nombre comercial, razón social, RUC, logo, plan, estado (activa/suspendida), nombre del club. |
| **Hotel** (hoy `establecimiento`) | Empresa | Nombre, dirección, ciudad, check-in/out, zona horaria. Usa el logo de la empresa. |
| **Usuario** | Empresa | Rol + hoteles a los que tiene acceso. El super-admin no pertenece a ninguna empresa. |
| **Persona** (nueva) | Plataforma | Tipo + número de documento (únicos en toda la plataforma) y nombre. Se registra una sola vez; ningún hotel la duplica. |
| **Membresía** (`clientes`) | Empresa + Persona | La persona inscrita en el club de una empresa. Una persona por empresa. Los puntos viven aquí. |
| **Tipo de habitación** | Hotel | Cada local define sus tipos y puntos. |
| **Recompensa** | Empresa | Opcionalmente limitada a un hotel (`hotel_id` nulo = vale en todos). |
| **Visita / Canje** | Huésped + Hotel | Registran **dónde** se ganó o canjeó. |
| **Método de pago QR** | Hotel | Cada local sube su QR y número. |

## 3. Roles

| Rol | Puede |
|---|---|
| **Super-admin** (tú) | Crear empresas, asignar plan, suspender/reactivar, ver métricas globales. No ve datos personales de huéspedes salvo soporte. |
| **Administrador de empresa** | Todo dentro de su empresa: hoteles, personal, recompensas, branding, reportes consolidados de la cadena. |
| **Gerente de hotel** | Su(s) hotel(es): datos del local, QR, tipos de habitación, recepcionistas de ese local. |
| **Recepcionista** | Operación diaria en sus hoteles: registrar huéspedes, asignar habitación, canjear, cobrar. |

Quien tiene acceso a varios hoteles elige el **hotel activo** desde la barra lateral; todo lo que registra queda en ese hotel.

**Cómo quedó (fase 3):** columna `users.rol` + tabla `hotel_user` (hoteles de gerentes y recepcionistas). Middleware `rol:` en `routes/web.php` (3 grupos: operación, gestión del hotel, plataforma) y `empresa.activa` (cierra la sesión si la empresa está suspendida). El menú se filtra con los mismos roles. El gerente edita los datos de su hotel pero no la marca (logo, club, RUC). Al registrar personal solo se pueden dar los roles y hoteles que uno mismo tiene. El super-admin va a `/plataforma` (solo lectura hasta la fase 4). Pruebas en `tests/Feature/RolesTest.php`.

## 4. Aislamiento de datos (lo más delicado)

- Todas las tablas operativas llevan `empresa_id` (y `hotel_id` donde aplica).
- Un *global scope* filtra automáticamente por la empresa del usuario: ninguna consulta depende de "acordarse" del filtro.
- Políticas (Gates/Policies) controlan qué hotel puede tocar cada rol.
- Las reglas `unique` pasan a ser compuestas (`empresa_id + dni`).
- Archivos (logos, QR) en carpetas por empresa.
- **Tests de fuga obligatorios**: un usuario de la empresa A nunca puede ver, editar ni canjear datos de la empresa B, aunque adivine el ID en la URL.

**Cómo quedó (fase 2):** traits `PerteneceAEmpresa` (clientes, recompensas, hoteles) y `PerteneceAEmpresaPorHotel` (habitaciones, visitas, canjes, métodos de pago) en `app/Models/Concerns`. Filtran por la empresa del usuario y rellenan empresa/hotel al crear. El hotel activo vive en la sesión (`Hotel::SESION`) y se cambia desde la barra lateral; una sesión con un hotel ajeno vuelve al primero propio. **Atención:** las reglas de validación `exists`/`unique` y `DB::table()` no pasan por los scopes: siempre filtrar a mano ahí (ver `HabitacionController`). Pruebas en `tests/Feature/AislamientoTest.php`.

## 5. Reglas de negocio

0. **Identidad global, puntos aislados.** La persona existe una sola vez en la plataforma; si otro hotel ya la registró, al inscribirla se vincula la misma persona en vez de duplicarla. Los puntos nunca cruzan de empresa: un hotel individual tiene sus puntos propios y una cadena los comparte entre sus locales. Una empresa no puede listar ni buscar personas de otras empresas: solo vincula a alguien escribiendo su documento.
1. **Puntos compartidos en la cadena.** La libreta es de la empresa: el huésped gana puntos en cualquier hotel y los canjea en cualquier otro. Cada movimiento guarda en qué hotel ocurrió.
2. **Recompensas de la empresa, con excepciones por hotel.** La empresa define el catálogo; una recompensa puede marcarse "solo en el Hotel X" y solo aparece al canjear en ese local.
3. **Documento: DNI, CE o pasaporte.** La persona se identifica por tipo + número, único en toda la plataforma. Validación por tipo: DNI 8 dígitos, CE 9–12 alfanumérico, pasaporte 6–12 alfanumérico.
4. **Un solo login para todos.** Cada usuario pertenece a una empresa y entra directo a ella. El email es único en toda la plataforma. Los subdominios quedan para la fase 6.
5. **Una estadía por huésped por día y por hotel**, con la hora local del hotel.
6. **Un hotel individual = una empresa con un hotel.** Sin pantallas ni reglas especiales.
7. **Plan comercial** (manual al inicio): cada empresa tiene un máximo de hoteles según su plan; el super-admin lo cambia. El cobro de la suscripción queda fuera hasta la fase 6.
8. **Empresa suspendida**: su personal no puede iniciar sesión; los datos se conservan.

## 6. Fases

| Fase | Entrega | Resultado visible |
|---|---|---|
| **0. Decisiones** | Este documento cerrado | Reglas claras antes de tocar código |
| **1. Modelo de datos** ✅ | Tablas `empresas`, columnas `empresa_id`/`hotel_id`, uniques compuestos, **migración de los datos actuales** a una empresa "Hotel Buena Vida" | La app funciona igual que hoy |
| **2. Aislamiento** ✅ | Global scopes, hotel activo en sesión, tests de fuga | Dos empresas de prueba conviven sin verse |
| **3. Roles** ✅ | Columna `rol`, políticas, menú según rol | Recepcionista ya no puede editar QR ni establecimiento |
| **4. Pantallas de gestión** | Hoteles de la empresa, personal, selector de hotel, panel super-admin (empresas, plan, estado) | Puedes dar de alta un cliente nuevo sin tocar la base de datos |
| **5. Reportes de cadena** | Panel consolidado vs. por hotel | El dueño ve toda su cadena |
| **6. Opcional** | Subdominios (`sol.tuapp.com`), cobro de suscripción, vencimiento de puntos, auditoría | Producto listo para escalar |

Cada fase deja la app funcionando y con tests verdes; se puede vender después de la fase 4.

## 7. Pendientes del QA que se resuelven aquí

- **Zona horaria**: pasa a ser un dato de cada hotel (una cadena puede tener locales en distintas zonas).
- **"Una estadía por día"**: se evalúa por hotel y con la hora local del hotel.
- **Roles**: hoy cualquier trabajador edita todo; la fase 3 lo corrige.

## 8. Decisiones tomadas

| Tema | Decisión | Alternativa descartada |
|---|---|---|
| Puntos en cadena | Compartidos por empresa | Separados por hotel |
| Recompensas | Catálogo de empresa + "solo en el Hotel X" | Solo empresa / cada hotel la suya |
| Documento del huésped | DNI, CE o pasaporte | Solo DNI |
| Identidad del huésped | Persona global + membresía por empresa | Huésped duplicado en cada empresa |
| Acceso | Un login para todos | Subdominio por empresa (fase 6) |

## 9. Impacto en lo que ya existe

- `establecimiento` (una fila) → `hoteles` (muchas filas) + `empresas`. Logo y nombre del club pasan a la empresa; dirección, horarios, zona horaria y QR quedan en el hotel.
- `clientes.nombre` y `clientes.dni` pasan a la tabla `personas` (tipo + número de documento); `clientes` queda como membresía (`empresa_id` + `persona_id`). Los DNI actuales se migran como tipo DNI. La columna obsoleta `total_puntos` se elimina.
- `habitaciones`, `metodos_pago` → `hotel_id`. `recompensas` → `empresa_id` + `hotel_id` opcional. `visitas`, `canjes` → `hotel_id`.
- El registro de trabajadores (`/9ax1g9`) se reemplaza por la gestión de personal dentro de la empresa.
- Los datos actuales se migran a la empresa "Hotel Buena Vida" con su único hotel; el usuario existente queda como administrador de empresa.

## 10. Privacidad

Compartir la identidad entre empresas implica que el nombre registrado por un hotel lo verá otro hotel cuando inscriba a la misma persona. Por la Ley 29733 (Protección de Datos Personales, Perú) conviene pedir el **consentimiento** del huésped al inscribirlo y guardar solo lo mínimo en la persona global (documento y nombre). Teléfono, correo o notas, si se agregan, van en la membresía de cada empresa.
