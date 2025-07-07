# 🎯 Funcionalidad de Asignación Automática de Tickets

## 📖 Descripción

Esta nueva funcionalidad permite la asignación automática y balanceada de tickets a los agentes disponibles de cada departamento, manteniendo la capacidad de asignación manual.

## ✨ Características Principales

### 🔧 Métodos de Asignación

1. **Balanceado (Round Robin)** *(Recomendado)*
   - Distribuye tickets equitativamente entre todos los agentes
   - Mantiene contadores de asignación por departamento
   - Garantiza carga de trabajo balanceada

2. **Aleatorio**
   - Asigna tickets de forma completamente aleatoria
   - Útil para distribución impredecible
   - Sin consideración de carga actual

3. **Ponderado por Prioridad**
   - Usa pesos configurables para cada agente
   - Agentes con mayor peso reciben más tickets
   - Ideal para equipos con diferentes capacidades

### 🎛️ Configuración Flexible

- **Habilitación/Deshabilitación** global del sistema
- **Configuración por departamento** de agentes participantes
- **Pesos de prioridad** personalizables (1-10)
- **Estadísticas y contadores** en tiempo real
- **Reseteo de contadores** para rebalancear cargas

### 🔒 Compatibilidad y Seguridad

- **No reemplaza la asignación manual** - convive perfectamente
- **Solo administradores** pueden configurar el sistema
- **Logs detallados** de todas las asignaciones
- **Respaldo automático** a administradores cuando no hay agentes disponibles

## 🗄️ Cambios en Base de Datos

### Nuevas Tablas

1. **`hdzfv_department_assignments`**
   - Tracking de asignaciones por departamento/agente
   - Contadores y timestamps de última asignación

2. **`hdzfv_staff_departments`**
   - Configuración de agentes por departamento
   - Pesos de prioridad y estado activo/inactivo

### Modificaciones

1. **`hdzfv_tickets`**
   - Nuevo campo `staff_id` para asignación
   - Índice para optimizar consultas

2. **`hdzfv_config`**
   - `auto_assignment`: Habilitar/deshabilitar (0/1)
   - `auto_assignment_method`: Método de asignación (balanced/random/weighted)

## 🚀 Instalación

### 1. Ejecutar Migración de Base de Datos

```sql
-- Ejecutar el archivo: mysql/auto_assignment_migration.sql
mysql -u root -p helpdesk < mysql/auto_assignment_migration.sql
```

### 2. Configuración Inicial

1. Acceder al panel de administración
2. Ir a **Configuración > Asignación Automática**
3. Habilitar la funcionalidad
4. Seleccionar método de asignación
5. Configurar agentes por departamento (opcional)

## 📱 Uso de la Funcionalidad

### Para Administradores

1. **Configuración Global**
   - Activar/desactivar el sistema
   - Seleccionar método de asignación
   - Ver estadísticas globales

2. **Gestión de Staff**
   - Asignar agentes a departamentos específicos
   - Configurar pesos de prioridad
   - Activar/desactivar participación por agente

3. **Monitoreo**
   - Ver estadísticas de asignación por departamento
   - Resetear contadores cuando sea necesario
   - Revisar logs de asignaciones

### Para Agentes

- **Sin cambios en el flujo de trabajo**
- Los tickets llegan automáticamente asignados
- Posibilidad de reasignar manualmente
- Visibilidad del método de asignación en la vista del ticket

## 🔧 API y Integración

### Nuevas Clases

1. **`App\Libraries\AutoAssignment`**
   - Lógica principal de asignación
   - Métodos públicos para integración
   - Estadísticas y configuración

2. **`App\Controllers\Staff\AutoAssignmentController`**
   - Interfaz de administración
   - Configuración y estadísticas
   - Gestión de agentes por departamento

### Métodos Principales

```php
// Asignar ticket automáticamente
$autoAssignment = new AutoAssignment();
$staff_id = $autoAssignment->assignTicket($ticket_id, $department_id);

// Verificar si está habilitado
$enabled = $autoAssignment->isAutoAssignmentEnabled();

// Obtener estadísticas
$stats = $autoAssignment->getAssignmentStats($department_id);

// Reasignar manualmente
$autoAssignment->reassignTicket($ticket_id, $new_staff_id);
```

## 📊 Estadísticas y Monitoreo

### Métricas Disponibles

- **Tickets asignados por agente** en cada departamento
- **Última asignación** por agente
- **Distribución de carga** visual
- **Agentes disponibles** por departamento

### Logs del Sistema

Todas las asignaciones se registran en los logs del sistema con nivel `INFO`:

```
[INFO] Ticket #123 asignado automáticamente al staff #5 en departamento #2
[INFO] Ticket #124 reasignado manualmente al staff #3
```

## 🔄 Flujo de Trabajo

1. **Cliente crea ticket** en departamento específico
2. **Sistema verifica** si auto-asignación está habilitada
3. **Obtiene agentes disponibles** para el departamento
4. **Aplica método de asignación** configurado
5. **Asigna ticket** y actualiza contadores
6. **Registra la acción** en logs
7. **Envía notificaciones** normales del sistema

## ⚠️ Consideraciones Importantes

### Comportamiento de Fallback

- Si no hay agentes específicos para un departamento, asigna a administradores
- Si la asignación automática falla, el ticket queda sin asignar
- Los errores se registran en logs para debugging

### Compatibilidad

- **100% compatible** con asignación manual existente
- **No afecta** tickets existentes sin reasignación
- **Mantiene** todas las funcionalidades actuales

### Rendimiento

- **Consultas optimizadas** con índices apropiados
- **Caché interno** para agentes por departamento
- **Logging mínimo** para no afectar rendimiento

## 🎯 Casos de Uso Recomendados

1. **Equipos Balanceados**: Usar método "Balanceado"
2. **Equipos con Especialistas**: Usar método "Ponderado"
3. **Distribución Impredecible**: Usar método "Aleatorio"
4. **Departamentos Grandes**: Configurar agentes específicos
5. **Departamentos Pequeños**: Dejar que administradores reciban todo

## 🔮 Funcionalidades Futuras

- **Asignación por prioridad de ticket**
- **Horarios de disponibilidad de agentes**
- **Asignación basada en carga de trabajo actual**
- **Integración con calendario de agentes**
- **Notificaciones push para asignaciones**

---

**Desarrollado para HelpDeskZ v2.0.2+**
**Fecha: Julio 2025**
**Rama: feature/auto-assignment-tickets**
