import axios from 'axios'
import { useAuthStore } from '@/store/authStore'

// ── Axios instance ─────────────────────────────────────────────────────────
export const api = axios.create({
  baseURL: '/api',
  headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
})

api.interceptors.request.use((config) => {
  const token = useAuthStore.getState().token
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

api.interceptors.response.use(
  (r) => r,
  (err) => {
    if (err.response?.status === 401) {
      useAuthStore.getState().logout()
      window.location.href = '/login'
    }
    return Promise.reject(err)
  }
)

// ── Helpers ────────────────────────────────────────────────────────────────
const get  = (url: string, params?: object) => api.get(url, { params }).then(r => r.data)
const post = (url: string, data?: object)   => api.post(url, data).then(r => r.data)
const put  = (url: string, data?: object)   => api.put(url, data).then(r => r.data)
const del  = (url: string)                  => api.delete(url).then(r => r.data)

// ── AUTH ───────────────────────────────────────────────────────────────────
export const authApi = {
  login:  (email: string, password: string) => post('/auth/login', { email, password }),
  logout: ()                                => post('/auth/logout'),
  me:     ()                                => get('/auth/me'),
}

// ── DASHBOARD ──────────────────────────────────────────────────────────────
export const dashboardApi = {
  get: () => get('/dashboard'),
}

// ── MAESTROS ───────────────────────────────────────────────────────────────
export const maestrosApi = {
  formasPago:   () => get('/maestros/formas-pago'),
  tiposIva:     () => get('/maestros/tipos-iva'),
  almacenes:    () => get('/maestros/almacenes'),
  vendedores:   () => get('/maestros/vendedores'),
  zonas:        () => get('/maestros/zonas'),
  sectores:     () => get('/maestros/sectores'),
  rutas:        () => get('/maestros/rutas'),
  delegaciones: () => get('/maestros/delegaciones'),
  familias:     () => get('/maestros/familias'),
  empresa:      () => get('/empresa'),
  proveedores:      () => get('/proveedores'),
  updateEmpresa:(data: object) => put('/empresa', data),
}

// ── CLIENTES ───────────────────────────────────────────────────────────────
export const clientesApi = {
  list:         (params?: object)            => get('/clientes', params),
  get:          (id: number)                 => get(`/clientes/${id}`),
  create:       (data: object)               => post('/clientes', data),
  update:       (id: number, data: object)   => put(`/clientes/${id}`, data),
  delete:       (id: number)                 => del(`/clientes/${id}`),
  facturas:     (id: number, params?: object)=> get(`/clientes/${id}/facturas`, params),
  cobros:       (id: number)                 => get(`/clientes/${id}/cobros`),
  estadisticas: (id: number)                 => get(`/clientes/${id}/estadisticas`),
}

// ── PROVEEDORES ────────────────────────────────────────────────────────────
export const proveedoresApi = {
  list:   (params?: object)          => get('/proveedores', params),
  get:    (id: number)               => get(`/proveedores/${id}`),
  create: (data: object)             => post('/proveedores', data),
  update: (id: number, data: object) => put(`/proveedores/${id}`, data),
  delete: (id: number)               => del(`/proveedores/${id}`),
}

// ── ARTÍCULOS ──────────────────────────────────────────────────────────────
export const articulosApi = {
  list:         (params?: object)            => get('/articulos', params),
  get:          (id: number)                 => get(`/articulos/${id}`),
  create:       (data: object)               => post('/articulos', data),
  update:       (id: number, data: object)   => put(`/articulos/${id}`, data),
  delete:       (id: number)                 => del(`/articulos/${id}`),
  stock:        (id: number)                 => get(`/articulos/${id}/stock`),
  movimientos:  (id: number, params?: object)=> get(`/articulos/${id}/movimientos`, params),
}

// ── ALMACÉN ────────────────────────────────────────────────────────────────
export const almacenApi = {
  stock:        (params?: object) => get('/almacen/stock', params),
  movimientos:  (params?: object) => get('/almacen/movimientos', params),
  registrar:    (data: object)    => post('/almacen/movimientos', data),
  inventario:   (params?: object) => get('/almacen/inventario', params),
}

// ── FABRICACIÓN ────────────────────────────────────────────────────────────
export const fabricacionApi = {
  // Fórmulas
  formulas:        (params?: object)           => get('/fabricacion/formulas', params),
  getFormula:      (id: number)                => get(`/fabricacion/formulas/${id}`),
  createFormula:   (data: object)              => post('/fabricacion/formulas', data),
  updateFormula:   (id: number, data: object)  => put(`/fabricacion/formulas/${id}`, data),
  deleteFormula:   (id: number)                => del(`/fabricacion/formulas/${id}`),
  verificarStock:  (id: number, lotes: number) => post(`/fabricacion/formulas/${id}/verificar-stock`, { lotes }),
  // Órdenes
  ordenes:         (params?: object)           => get('/fabricacion/ordenes', params),
  getOrden:        (id: number)                => get(`/fabricacion/ordenes/${id}`),
  crearOrden:      (data: object)              => post('/fabricacion/ordenes', data),
  ejecutar:        (id: number)                => post(`/fabricacion/ordenes/${id}/ejecutar`),
  anularOrden:     (id: number, motivo?: string) => post(`/fabricacion/ordenes/${id}/anular`, { motivo }),
}

// ── VENTAS ─────────────────────────────────────────────────────────────────
export const ventasApi = {
  list:              (params?: object)          => get('/ventas', params),
  get:               (id: number)               => get(`/ventas/${id}`),
  create:            (data: object)             => post('/ventas', data),
  facturarAlbaranes: (data: object)             => post('/ventas/facturar-albaranes', data),
  anular:            (id: number)               => post(`/ventas/${id}/anular`),
  pdfUrl:            (id: number)               => `/api/ventas/${id}/pdf`,
}

// ── COBROS ─────────────────────────────────────────────────────────────────
export const cobrosApi = {
  list:         (params?: object)             => get('/cobros', params),
  resumen:      ()                            => get('/cobros/resumen'),
  get:          (id: number)                  => get(`/cobros/${id}`),
  cobrar:       (id: number, data: object)    => post(`/cobros/${id}/cobrar`, data),
  crearRemesa:  (data: object)                => post('/cobros/remesas', data),
}

// ── ADMIN ──────────────────────────────────────────────────────────────────
export const adminApi = {
  users:      ()                           => get('/admin/users'),
  createUser: (data: object)               => post('/admin/users', data),
  updateUser: (id: number, data: object)   => put(`/admin/users/${id}`, data),
  roles:      ()                           => get('/admin/roles'),
  auditLogs:  (params?: object)            => get('/admin/audit-logs', params),
}
