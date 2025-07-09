# Selector de Agentes Dinámico por Departamento

## Resumen de Cambios

Se implementó la funcionalidad para que el selector de agentes se actualice dinámicamente según el departamento seleccionado cuando la auto-asignación está desactivada.

### Problema Resuelto
- **Antes**: Al seleccionar cualquier departamento, el combo de agentes mostraba todos los agentes del sistema
- **Ahora**: El selector de agentes muestra solo los agentes asignados al departamento seleccionado

### Archivos Modificados

#### 1. **Backend - Controladores**
- `hdz/app/Controllers/Ticket.php`
  - Agregado método `getAgentsByDepartment()` para endpoint AJAX del cliente
  - Valida departamento y auto-asignación desactivada
  - Retorna JSON con agentes filtrados

- `hdz/app/Controllers/Staff/Misc.php`
  - Agregado método `getAgentsByDepartment()` para endpoint AJAX del staff
  - Filtrado de agentes por departamento
  - Respuesta JSON estructurada

- `hdz/app/Controllers/Staff/Tickets.php`
  - Removida pre-carga de todos los agentes
  - Ahora envía lista vacía para carga dinámica via AJAX

#### 2. **Backend - Rutas**
- `hdz/app/Config/Routes.php`
  - Agregada ruta: `/ajax/agents/(:num)` para cliente
  - Agregada ruta: `/staff/ajax/agents/(:num)` para staff

#### 3. **Frontend - Vistas**
- `hdz/app/Views/staff/ticket_new.php`
  - Agregado JavaScript para actualización dinámica
  - Detección de cambio de departamento
  - Petición AJAX automática
  - Carga inicial de agentes del departamento seleccionado por defecto

- `hdz/app/Views/client/ticket_form.php`
  - Preparado para funcionalidad dinámica (departamento readonly actualmente)
  - Comentarios agregados para futuras mejoras

#### 4. **Backend - Librerías**
- `hdz/app/Libraries/Staff.php`
  - Método `getAgentsByDepartment()` ya existía y funciona correctamente
  - Filtrado por departamento usando deserialización

- `hdz/app/Libraries/Tickets.php`
  - Soporte para asignación manual mediante `$assigned_staff_id`
  - Integración con creación de tickets

### Funcionalidad Implementada

#### Para el Staff:
1. **Carga inicial**: Al abrir la página, se cargan automáticamente los agentes del departamento seleccionado por defecto
2. **Cambio dinámico**: Al cambiar de departamento, se actualizan los agentes via AJAX
3. **Validación**: Solo se muestran agentes activos del departamento seleccionado
4. **Manejo de errores**: Estados de carga y mensajes de error apropiados

#### Para el Cliente:
1. **Funcionalidad preparada**: Endpoints AJAX funcionando
2. **Integración**: Formulario preparado para actualización dinámica
3. **Compatibilidad**: Funciona con la selección de departamento actual (readonly)

### Endpoints AJAX

#### Cliente
- **URL**: `/ajax/agents/{department_id}`
- **Método**: GET
- **Respuesta**: `{"agents": [{"id": "2", "fullname": "Juan Pérez", "username": "jperez", "display_name": "Juan Pérez (jperez)"}]}`

#### Staff
- **URL**: `/staff/ajax/agents/{department_id}`
- **Método**: GET
- **Respuesta**: Igual formato que cliente
- **Seguridad**: Requiere autenticación de staff

### Validaciones
- Verificación de departamento existente
- Validación de auto-asignación desactivada
- Manejo de errores HTTP (400, 404)
- Filtrado de agentes activos y no admin

### Resultado
✅ **Problema resuelto**: El selector de agentes ahora se actualiza dinámicamente según el departamento seleccionado, evitando errores al crear tickets con agentes no asignados al departamento correspondiente.

### Pruebas Realizadas
- Verificación de endpoints AJAX funcionando
- Filtrado correcto por departamento
- Comportamiento dinámico en interfaz de staff
- Validación de respuestas JSON
- Limpieza de archivos temporales
