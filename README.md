# FlexiGES Next — ERP
## Laravel 12 + React 19 + PostgreSQL

---

## Stack
- **Backend**: Laravel 12, PHP 8.4, PostgreSQL 16, Sanctum, Spatie Permission, DomPDF
- **Frontend**: React 19, Vite, TanStack Query v5, Zustand v5, Tailwind CSS, shadcn/ui

---

## Setup rápido

### 1. PostgreSQL
```bash
createdb flexiges
```

### 2. Backend
```bash
cd backend
cp .env.example .env

# Ajustar en .env:
# DB_DATABASE=flexiges
# DB_USERNAME=tu_usuario
# DB_PASSWORD=tu_contraseña

composer install
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan storage:link
php artisan serve   # http://localhost:8000
```

### 3. Frontend
```bash
cd frontend
npm install
npm run dev   # http://localhost:5173
```

---

## Credenciales por defecto

| Usuario | Email | Password | Rol |
|---------|-------|----------|-----|
| Super Admin | admin@efgnext.es | password | superadmin |
| María García | mgarcia@efgnext.es | password | comercial |
| Juan Pérez | jperez@efgnext.es | password | comercial |
| Carlos Almacén | calmacen@efgnext.es | password | almacenero |
| Ana Contabilidad | acontabilidad@efgnext.es | password | contabilidad |

---

## Módulos implementados

| Módulo | Backend | Frontend |
|--------|---------|----------|
| Auth (Sanctum) | ✅ | ✅ |
| Dashboard KPIs | ✅ | ✅ |
| Clientes | ✅ CRUD completo | ✅ |
| Proveedores | ✅ CRUD completo | ✅ |
| Artículos | ✅ CRUD + 4 tarifas + stock | ✅ |
| Almacén | ✅ Movimientos + Inventario | ✅ |
| **Fabricación BOM** | ✅ Fórmulas + Órdenes + Ejecución | ✅ |
| Ventas | ✅ Factura/Albarán/Pedido/Presupuesto | ✅ |
| Cobros | ✅ Cartera + Cobro parcial + Remesas | ✅ |
| PDF Facturas | ✅ DomPDF A4 | ✅ (via API) |
| Roles/Permisos | ✅ Spatie 6 permisos | ✅ |
| Auditoría | ✅ audit_logs | ✅ |
| Compras | ✅ Pedidos + Albaranes + Facturas | — |
| Estadísticas | ✅ Vistas SQL | Parcial |

---

## Fabricación BOM — ejemplo canónico

El módulo implementa exactamente el requisito "CAJA-BOBINAS":

**Fórmula FOR003 — Caja expositor 10 bobinas multicolor:**
- 1 × Caja cartón 40x30x20cm (CA001)
- 10 × Bobina hilo 500m multicolor (BO001) ← que es a su vez un fabricado
- 0.05 KG × Hilo poliéster blanco (HI001) con 2% merma
- 2 × Etiqueta tejida marca (ET001)

Al ejecutar la orden:
1. Se descuenta stock de cada componente (FABRICACION_OUT)
2. Se suman unidades al producto terminado (FABRICACION_IN)
3. Se actualizan movimientos con trazabilidad completa
4. Se calcula coste real por unidad producida

---

## API endpoints principales

```
POST   /api/auth/login
GET    /api/dashboard
GET    /api/clientes?search=&page=&per_page=
GET    /api/articulos?search=&familia_id=&bajo_minimo=
GET    /api/almacen/inventario?almacen_id=
GET    /api/fabricacion/formulas
POST   /api/fabricacion/formulas/{id}/verificar-stock
POST   /api/fabricacion/ordenes
POST   /api/fabricacion/ordenes/{id}/ejecutar
POST   /api/ventas
GET    /api/ventas/{id}/pdf
GET    /api/cobros?estado=pendiente
POST   /api/cobros/{id}/cobrar
```

---

## Estructura de carpetas

```
flexiges/
├── backend/
│   ├── app/
│   │   ├── Http/Controllers/Api/   ← Controllers CRUD + lógica HTTP
│   │   ├── Models/                 ← Eloquent models
│   │   └── Services/               ← FabricacionService, VentasService, etc.
│   ├── database/
│   │   ├── migrations/             ← 10 migraciones ordenadas
│   │   └── seeders/                ← Datos reales de prueba
│   ├── resources/views/pdf/        ← Blade template factura A4
│   └── routes/api.php              ← Todas las rutas API
└── frontend/
    └── src/
        ├── api/                    ← Cliente Axios + módulos API
        ├── features/               ← Una carpeta por módulo
        ├── hooks/queries.ts        ← TanStack Query hooks
        ├── store/authStore.ts      ← Zustand auth
        ├── types/                  ← TypeScript interfaces
        └── utils/                  ← Formatters, helpers
```
