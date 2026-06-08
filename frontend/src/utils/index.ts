import { clsx, type ClassValue } from 'clsx'
import { twMerge } from 'tailwind-merge'
import { format, parseISO } from 'date-fns'
import { es } from 'date-fns/locale'

export const cn = (...inputs: ClassValue[]) => twMerge(clsx(inputs))

// ── Formatters ─────────────────────────────────────────────────────────────
export const fmtEur = (n: number | string | null | undefined, decimals = 2): string => {
  const v = Number(n ?? 0)
  return v.toLocaleString('es-ES', { minimumFractionDigits: decimals, maximumFractionDigits: decimals }) + ' €'
}

export const fmtNum = (n: number | string | null | undefined, decimals = 2): string => {
  const v = Number(n ?? 0)
  return v.toLocaleString('es-ES', { minimumFractionDigits: decimals, maximumFractionDigits: decimals })
}

export const fmtDate = (d: string | null | undefined): string => {
  if (!d) return '—'
  try { return format(parseISO(d), 'dd/MM/yyyy', { locale: es }) } catch { return d }
}

export const fmtDatetime = (d: string | null | undefined): string => {
  if (!d) return '—'
  try { return format(parseISO(d), 'dd/MM/yyyy HH:mm', { locale: es }) } catch { return d }
}

export const today = (): string => format(new Date(), 'yyyy-MM-dd')

// ── Estado badges ──────────────────────────────────────────────────────────
export const estadoColors: Record<string, string> = {
  pendiente:   'bg-yellow-100 text-yellow-800 border-yellow-300',
  cobrado:     'bg-green-100  text-green-800  border-green-300',
  cobrada:     'bg-green-100  text-green-800  border-green-300',
  enviada:     'bg-blue-100   text-blue-800   border-blue-300',
  enviado:     'bg-blue-100   text-blue-800   border-blue-300',
  confirmado:  'bg-sky-100    text-sky-800    border-sky-300',
  anulado:     'bg-red-100    text-red-800    border-red-300',
  anulada:     'bg-red-100    text-red-800    border-red-300',
  borrador:    'bg-gray-100   text-gray-600   border-gray-300',
  parcial:     'bg-orange-100 text-orange-800 border-orange-300',
  completada:  'bg-green-100  text-green-800  border-green-300',
  en_proceso:  'bg-blue-100   text-blue-800   border-blue-300',
  'bajo mínimo':'bg-red-100   text-red-800    border-red-300',
  activo:      'bg-green-100  text-green-800  border-green-300',
  recibido:    'bg-green-100  text-green-800  border-green-300',
  facturado:   'bg-purple-100 text-purple-800 border-purple-300',
}

export const tipoDocLabel: Record<string, string> = {
  presupuesto: 'Presupuesto',
  pedido:      'Pedido',
  albaran:     'Albarán',
  factura:     'Factura',
  rectificativa: 'Rectificativa',
}
