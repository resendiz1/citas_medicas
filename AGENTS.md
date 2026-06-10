# Contexto del Proyecto: Citas Médicas

## Estado Actual
- Framework: Laravel 13 con Vite + MDBootstrap + neumorphism design
- Base de datos: MySQL, 22 migraciones ejecutadas
- Roles: admin, medico, paciente, recepcionista

## Funcionalidades Implementadas

### Autenticación
- Login/register/logout personalizado con selección de rol (medico/paciente)
- Middleware `CheckRole` para control de acceso por rol

### Dashboard por Rol
- **Admin**: estadísticas (pacientes, médicos, citas, pendientes)
- **Médico**: lista de sus citas con acciones (perfil paciente, receta)
- **Paciente**: lista de sus citas con botón nueva cita y cancelar + "Ver detalles"
- **Recepcionista**: estadísticas + tabla de todas las citas con gestión de estado

### Citas
- Paciente puede solicitar cita seleccionando médico, fecha y motivo
- Al venir desde `/medicos/{id}`, el médico se preselecciona automáticamente (`?medico_id=X`)
- Estados: pendiente → confirmada → en_espera → en_consulta → finalizada
- Transiciones: cancelada, no_asistio, reprogramada desde estados permitidos
- Historial de cambios registrado en `cita_historiales`
- Médico, admin y recepcionista pueden cambiar estado
- Paciente puede cancelar solo citas pendientes

### Detalle de Cita (`/citas/{id}`)
- Ruta `GET /citas/{cita}` → `CitaController@show` → `citas.show`
- Muestra: fecha, estado, motivo, paciente, médico, especialidad
- Recetas médicas inline: info, diagnóstico, indicaciones, medicamentos, notas, documentos
- Consulta médica inline (visible para todos los roles): motivo, síntomas, dolores, signos vitales (presión, temperatura, frecuencia cardíaca, peso, estatura, IMC), exploración, diagnóstico
- Historial de cambios de estado
- Tablas con `neu-table` (fondo transparente)

### Recetas Médicas
- Solo el día de la cita, médico puede crear receta
- Diagnóstico, indicaciones, medicamentos dinámicos, notas, documentos adjuntos
- Vista detallada con tabla de medicamentos y descarga de documentos
- Admin también puede crear/ver recetas

### Consulta Médica
- Formulario completo con motivo, síntomas, signos vitales, exploración, diagnóstico
- Sección dinámica de **dolores** (tabla hija `dolores`) — agregar/eliminar múltiples dolores con ubicación, intensidad, duración
- Sección dinámica de **recetas** — agregar/eliminar múltiples recetas, cada una con sus medicamentos dinámicos
- Transición automática de `en_espera` → `en_consulta` al abrir el formulario
- Vista detalle con tabla de dolores (fondo transparente) y todos los datos clínicos

### Perfil de Paciente
- `/paciente/perfil` con toggle vista/edición, foto, contactos, alergias, enfermedades
- Tabla "Mis Citas" con todas las citas del paciente ordenadas por fecha descendente
- Cada cita con enlace a "Ver detalles"

### Perfil de Médico
- `/medico/perfil` con toggle vista/edición, foto, documentos

### Admin CRUD
- Médicos: crear, editar, eliminar + búsqueda + paginación
- Pacientes: crear, editar, eliminar + búsqueda + paginación
- Citas: listar todas con filtro por estado + búsqueda + paginación

### Horarios de Médicos
- Médico gestiona sus propios horarios (día, hora inicio, hora fin, activo)
- Admin gestiona horarios de cualquier médico
- Médico define `intervalo_minutos` (15–120) en la vista de horarios, almacenado en `medico_perfiles`
- Intervalo se usa en `citas.create` para generar slots en FullCalendar y en validación server-side (`CitaController@store`)

### Bloqueos de Disponibilidad
- Médico registra bloqueos (fecha inicio, fecha fin, motivo)
- Admin gestiona bloqueos de cualquier médico

### Notificaciones en Tiempo Real
- `CitaEstadoNotificacion`: canales `mail` + `database`
- Email al crear cita (médico) y al cambiar estado (paciente/médico)
- Notificaciones en base de datos (`notifications` table)
- Polling JS cada 10s vía `GET /notificaciones/poll` → `NotificacionController@poll`
- Toasts Notyf emergentes con el mensaje de la notificación
- Al cambiar estado, notifica al paciente y también al médico si quien cambia no es él mismo

### UI/UX
- Notyf para notificaciones toast (success/error)
- Diseño neumórfico con theme claro/oscuro
- Placeholder amarillo en inputs en tema oscuro (con `!important` para vencer MDB)
- Flatpickr para selects de fecha/hora
- Navbar siempre expandida (sin collapse), links visibles en todos los tamaños
- Fotos de perfil: click → `window.open(url, '_blank')` (sin modal)
- Assets cargados desde `public/build/manifest.json` dinámicamente con rutas `/build/...`

## Estructura de Archivos Clave

```
app/
  Http/Controllers/
    AdminController.php         - CRUD médicos, pacientes, citas
    AuthController.php          - Login/register/logout
    CitaController.php          - Crear citas, cambiar estado, show detalle
    DashboardController.php     - Dashboard por rol
    MedicoController.php        - Perfil paciente para médico
    MedicoHorarioController.php - CRUD horarios
    MedicoBloqueoController.php - CRUD bloqueos
    ConsultaMedicaController.php - CRUD consulta médica + dolores
    RecetaController.php        - CRUD recetas + documentos
    PacienteController.php      - Perfil paciente + citas
    NotificacionController.php  - Polling de notificaciones
  Http/Middleware/CheckRole.php - Middleware roles
  Models/
    User.php                    - esAdmin, esMedico, esPaciente, esRecepcionista, relaciones
    CitaMedica.php              - citas_medicas
    ConsultaMedica.php          - consulta_medicas (relación dolores)
    Dolor.php                   - dolores (hija de consulta_medicas)
    Receta.php, RecetaMedicamento.php, RecetaDocumento.php
    MedicoPerfil.php, MedicoHorario.php, MedicoBloqueo.php
    TipoMedico.php, Alergia.php, EnfermedadImportante.php
    ContactoEmergencia.php, CitaHistorial.php
  Notifications/
    CitaEstadoNotificacion.php  - canales mail + database
database/migrations/           - 22 migraciones (incl. notifications)
resources/
  views/
    layouts/app.blade.php       - Layout con meta tag para polling de notificaciones
    dashboard/index.blade.php   - Dashboard con "Ver detalles" para paciente
    citas/create.blade.php      - Crear cita con preselección de médico por query param
    citas/show.blade.php        - Detalle cita con recetas + consulta inline
    admin/citas/index.blade.php
    admin/medicos/index.blade.php - Placeholder amarillo en búsqueda
    admin/pacientes/index.blade.php
    medico/perfil.blade.php
    medico/horarios.blade.php
    medico/bloqueos.blade.php
    paciente/perfil.blade.php   - Perfil + tabla de citas del paciente
    paciente/medico-show.blade.php - Perfil médico público con "Solicitar cita"
    recetas/create.blade.php, show.blade.php
    consulta-medica/form.blade.php, show.blade.php
    recepcionista/paciente-show.blade.php
routes/web.php                  - + ruta notificaciones.poll
resources/css/app.css           - Placeholder amarillo en tema oscuro con !important
resources/js/app.js             - Polling cada 10s + Notyf toast de notificaciones
```

## Próximos Pasos / No Implementado
- Configurar driver de mail real (SMTP) en `.env`
- Tests
